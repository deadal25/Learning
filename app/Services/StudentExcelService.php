<?php

namespace App\Services;

use App\Models\EnglishClass;
use App\Models\EnglishGrade;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ZipArchive;

class StudentExcelService
{
    /**
     * Parse an uploaded file (.xlsx or .csv) into an array of associative student rows.
     */
    public function parseFile(string $filePath, ?string $originalExtension = null): array
    {
        $ext = $originalExtension
            ? strtolower($originalExtension)
            : strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            return $this->parseCsv($filePath);
        }

        // Check if file starts with PK (Zip archive magic bytes for .xlsx)
        $handle = @fopen($filePath, 'r');
        $magic = $handle ? fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }

        if (str_starts_with($magic, "PK\x03\x04") || $ext === 'xlsx') {
            return $this->parseXlsx($filePath);
        }

        // Otherwise fallback to CSV parser
        return $this->parseCsv($filePath);
    }

    /**
     * Native XLSX parser using ZipArchive and XML (fast, standalone, zero extra dependencies).
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Gagal membuka file Excel (.xlsx). Pastikan format file valid.');
        }

        // 1. Read shared strings
        $sharedStrings = [];
        if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($idx));
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Locate first worksheet
        $sheetXmlContent = null;
        if (($idx = $zip->locateName('xl/worksheets/sheet1.xml')) !== false) {
            $sheetXmlContent = $zip->getFromIndex($idx);
        } else {
            // Find any sheet in worksheets/
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $stat['name'])) {
                    $sheetXmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if (!$sheetXmlContent) {
            throw new \Exception('Lembar kerja Excel (Sheet) tidak ditemukan dalam file.');
        }

        $sheetXml = simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData)) {
            throw new \Exception('Struktur lembar kerja Excel tidak valid.');
        }

        $rawRows = [];
        foreach ($sheetXml->sheetData->row as $r) {
            $rowNum = (int)$r['r'];
            $cols = [];
            foreach ($r->c as $c) {
                $cellType = (string)$c['t'];
                if ($cellType === 'inlineStr' && isset($c->is->t)) {
                    $v = (string)$c->is->t;
                } elseif (isset($c->is->t)) {
                    $v = (string)$c->is->t;
                } else {
                    $v = (string)$c->v;
                    if ($cellType === 's' && isset($sharedStrings[(int)$v])) {
                        $v = $sharedStrings[(int)$v];
                    }
                }
                $cellRef = (string)$c['r'];
                $colLetter = preg_replace('/[0-9]/', '', $cellRef);
                $cols[$colLetter] = trim((string)$v);
            }
            if (!empty(array_filter($cols, fn($val) => $val !== ''))) {
                $rawRows[$rowNum] = $cols;
            }
        }

        return $this->normalizeRows($rawRows);
    }

    /**
     * Parse CSV file
     */
    public function parseCsv(string $filePath): array
    {
        $rawRows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $rowNum = 1;
            while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                // If single element with semicolon, try semicolon separator
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = str_getcsv($data[0], ';');
                }

                $cols = [];
                foreach ($data as $i => $val) {
                    $letter = $this->indexToColumnLetter($i);
                    $cols[$letter] = trim((string)$val);
                }
                if (!empty(array_filter($cols, fn($v) => $v !== ''))) {
                    $rawRows[$rowNum] = $cols;
                }
                $rowNum++;
            }
            fclose($handle);
        }

        return $this->normalizeRows($rawRows);
    }

    /**
     * Map raw rows with letters to standardized field names based on header row.
     */
    protected function normalizeRows(array $rawRows): array
    {
        if (empty($rawRows)) {
            return [];
        }

        // Find header row (usually row 1 or 2)
        $headerRow = null;
        $headerMap = []; // letter => standard key

        foreach ($rawRows as $rNum => $cols) {
            $normalizedCols = array_map(fn($v) => strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string)$v)), $cols);
            
            // Check if this row contains 'NAMA' or 'PESERTA' or 'NRP' or 'KELAS'
            $hasName = false;
            foreach ($normalizedCols as $val) {
                if (str_contains($val, 'NAMA') || str_contains($val, 'PESERTA') || str_contains($val, 'STUDENT')) {
                    $hasName = true;
                    break;
                }
            }

            if ($hasName) {
                $headerRow = $rNum;
                foreach ($cols as $colLetter => $rawHeader) {
                    $cleanHeader = strtoupper(trim((string)$rawHeader));
                    if (str_contains($cleanHeader, 'KELAS') || str_contains($cleanHeader, 'CLASS') || str_contains($cleanHeader, 'GRUP') || str_contains($cleanHeader, 'GROUP')) {
                        $headerMap[$colLetter] = 'class_name';
                    } elseif (str_contains($cleanHeader, 'NRP') || str_contains($cleanHeader, 'NIK') || str_contains($cleanHeader, 'NIS') || str_contains($cleanHeader, 'ID')) {
                        $headerMap[$colLetter] = 'nrp';
                    } elseif (str_contains($cleanHeader, 'NAMA') || str_contains($cleanHeader, 'PESERTA') || str_contains($cleanHeader, 'NAME')) {
                        $headerMap[$colLetter] = 'name';
                    } elseif (str_contains($cleanHeader, 'BAGIAN') || str_contains($cleanHeader, 'DIVISI') || str_contains($cleanHeader, 'DEPT') || str_contains($cleanHeader, 'SECTION')) {
                        $headerMap[$colLetter] = 'division';
                    } elseif (str_contains($cleanHeader, 'EMAIL')) {
                        $headerMap[$colLetter] = 'email';
                    } elseif (str_contains($cleanHeader, 'PASSWORD') || str_contains($cleanHeader, 'SANDI')) {
                        $headerMap[$colLetter] = 'password';
                    } elseif (str_contains($cleanHeader, 'TELP') || str_contains($cleanHeader, 'PHONE') || str_contains($cleanHeader, 'HP') || str_contains($cleanHeader, 'WA')) {
                        $headerMap[$colLetter] = 'phone';
                    }
                }
                break;
            }
        }

        // If no recognizable header found, assume standard format: A=class_name, B=nrp, C=name, D=division
        if (!$headerRow) {
            $headerMap = [
                'A' => 'class_name',
                'B' => 'nrp',
                'C' => 'name',
                'D' => 'division',
                'E' => 'email',
                'F' => 'password',
            ];
            $headerRow = 0;
        }

        $result = [];
        foreach ($rawRows as $rNum => $cols) {
            if ($rNum <= $headerRow) {
                continue;
            }

            $item = [
                'class_name' => null,
                'nrp' => null,
                'name' => null,
                'division' => null,
                'email' => null,
                'password' => null,
                'phone' => null,
            ];

            foreach ($cols as $letter => $val) {
                if (isset($headerMap[$letter])) {
                    $item[$headerMap[$letter]] = trim((string)$val);
                }
            }

            // A valid student row must have a name
            if (!empty($item['name']) && !str_contains(strtoupper($item['name']), 'NAMA PESERTA')) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Import an array of parsed student rows into the database for a specific subject.
     */
    public function importStudents(array $rows, int $subjectId, ?int $creatorId = null, ?int $teacherId = null): array
    {
        $subject = Subject::findOrFail($subjectId);
        $teacher = $teacherId ? User::find($teacherId) : null;
        $importedCount = 0;
        $updatedCount = 0;
        $errors = [];
        $createdClasses = [];

        $allSubjects = Subject::with('levels')->get();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $name = trim($row['name'] ?? '');
            if (empty($name)) {
                continue;
            }

            $nrp = !empty($row['nrp']) ? trim($row['nrp']) : null;
            $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';
            $className = !empty($row['class_name']) ? trim($row['class_name']) : null;
            $division = !empty($row['division']) ? trim($row['division']) : null;
            $phone = !empty($row['phone']) ? trim($row['phone']) : null;
            $defaultPassword = $cleanNrp ? "{$cleanNrp}@musashi" : 'password';
            $plainPassword = !empty($row['password']) && trim($row['password']) !== 'password' ? trim($row['password']) : $defaultPassword;

            // Ensure class exists in english_classes for this subject if provided
            if (!empty($className)) {
                $classObj = EnglishClass::firstOrCreate(
                    [
                        'name' => $className,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'is_active' => true,
                        'sort_order' => 99,
                    ]
                );
                if ($classObj->wasRecentlyCreated) {
                    $createdClasses[$className] = true;
                }
            }

            $explicitEmail = !empty($row['email']) ? strtolower(trim($row['email'])) : null;

            try {
                DB::transaction(function () use (
                    $name,
                    $nrp,
                    $className,
                    $division,
                    $phone,
                    $plainPassword,
                    $explicitEmail,
                    $subjectId,
                    $subject,
                    $creatorId,
                    $teacherId,
                    $teacher,
                    $allSubjects,
                    &$importedCount,
                    &$updatedCount
                ) {
                    // Find matching existing student accurately within the target subject
                    $existingUser = $this->findExistingStudent($explicitEmail, $nrp, $name, $subjectId);

                    // Generate a guaranteed collision-free unique email per subject
                    $email = $this->generateUniqueEmail($name, $nrp, $explicitEmail, $existingUser, $subjectId);

                    if ($existingUser) {
                        // Update existing student
                        $updateData = [
                            'name' => $name,
                            'division' => $division ?? $existingUser->division,
                            'class_name' => $className ?? $existingUser->class_name,
                            'subject_id' => $subjectId,
                            'phone' => $phone ?? $existingUser->phone,
                            'status' => 'active',
                            'email' => $email,
                        ];
                        if ($nrp) {
                            $updateData['nrp'] = $nrp;
                        }
                        if ($teacherId) {
                            $updateData['created_by'] = $teacherId;
                        }
                        $existingUser->update($updateData);

                        // Ensure enrollment with teacher (preserve existing if teacher not specified)
                        $enrollTeacher = $teacher ?? $existingUser->enrollments->firstWhere('subject_id', $subjectId)?->teacher;
                        $existingUser->enrollInSubject($subject, $enrollTeacher, $className ?? 'REGULAR');
                        $this->ensureProgressAndLevels($existingUser, $allSubjects);

                        // Create/Update EnglishGrade if English
                        if ((int)$subjectId === 1 && $className) {
                            $gradeTeacherId = $teacherId ?? $enrollTeacher?->id ?? $existingUser->created_by;
                            $this->ensureEnglishGrade($existingUser, $className, $gradeTeacherId);
                        }

                        $updatedCount++;
                    } else {
                        $student = User::create([
                            'name' => $name,
                            'nrp' => $nrp,
                            'email' => $email,
                            'password' => Hash::make($plainPassword),
                            'role' => User::ROLE_STUDENT,
                            'division' => $division,
                            'class_name' => $className,
                            'subject_id' => $subjectId,
                            'phone' => $phone,
                            'status' => 'active',
                            'created_by' => $teacherId ?? $creatorId,
                        ]);

                        // Enroll student with teacher
                        $student->enrollInSubject($subject, $teacher, $className ?? 'REGULAR');
                        $this->ensureProgressAndLevels($student, $allSubjects);

                        // Create EnglishGrade if English
                        if ((int)$subjectId === 1 && $className) {
                            $this->ensureEnglishGrade($student, $className, $teacherId);
                        }

                        $importedCount++;
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Baris {$rowNumber} ({$name}): " . $e->getMessage();
            }
        }

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'errors' => $errors,
            'created_classes' => array_keys($createdClasses),
        ];
    }

    /**
     * Ensure student progress and level status are initialized.
     */
    protected function ensureProgressAndLevels(User $student, $allSubjects): void
    {
        foreach ($allSubjects as $subj) {
            $firstLevel = $subj->levels->firstWhere('order', 1);
            if ($firstLevel) {
                UserLevelStatus::firstOrCreate([
                    'user_id' => $student->id,
                    'level_id' => $firstLevel->id,
                ], [
                    'points' => 0,
                    'is_unlocked' => true,
                    'is_completed' => false,
                ]);

                foreach ($subj->levels->where('order', '>', 1) as $higherLevel) {
                    UserLevelStatus::firstOrCreate([
                        'user_id' => $student->id,
                        'level_id' => $higherLevel->id,
                    ], [
                        'points' => 0,
                        'is_unlocked' => false,
                        'is_completed' => false,
                    ]);
                }

                UserProgress::firstOrCreate([
                    'user_id' => $student->id,
                    'subject_id' => $subj->id,
                ], [
                    'current_level_id' => $firstLevel->id,
                    'current_points' => 0,
                    'is_completed' => false,
                ]);
            }
        }
    }

    /**
     * Ensure EnglishGrade record exists for an English student.
     */
    protected function ensureEnglishGrade(User $student, string $className, ?int $teacherId = null): void
    {
        $grade = EnglishGrade::firstOrCreate([
            'student_id' => $student->id,
            'class_name' => $className,
            'week' => 1,
        ], [
            'teacher_id' => $teacherId,
            'meeting_1' => 0,
            'meeting_2' => 0,
            'meeting_3' => 0,
            'meeting_4' => 0,
            'attendance_score' => 0,
            'fluency' => 0,
            'grammar' => 0,
            'pronunciation' => 0,
            'vocabulary' => 0,
            'total_exam' => 0,
            'final_score' => 0,
            'feedback' => null,
        ]);

        if ($teacherId && !$grade->wasRecentlyCreated && $grade->teacher_id !== $teacherId) {
            $grade->update(['teacher_id' => $teacherId]);
        }
    }

    /**
     * Helper to convert 0-based column index to letter (A, B, C...)
     */
    protected function indexToColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }

    /**
     * Find an existing student record that corresponds to the given row data.
     * Patokan utama adalah NRP sebagai ID unik. Jika baris memiliki NRP dan tidak ditemukan di DB,
     * maka ini adalah siswa yang BERBEDA (bukan orang yang sama meskipun namanya kebetulan sama).
     */
    protected function findExistingStudent(?string $email, ?string $nrp, string $name, int $subjectId = 1): ?User
    {
        // 1. Primary identifier: NRP (strictly scoped to the target subject)
        if (!empty($nrp)) {
            $userByNrp = User::where('role', User::ROLE_STUDENT)
                ->where('subject_id', $subjectId)
                ->where('nrp', trim((string)$nrp))
                ->first();
            if ($userByNrp) {
                return $userByNrp;
            }

            // If explicit email is provided, check if that email exists for this subject
            if (!empty($email)) {
                $userByEmail = User::where('role', User::ROLE_STUDENT)
                    ->where('subject_id', $subjectId)
                    ->where('email', strtolower(trim($email)))
                    ->first();
                if ($userByEmail) {
                    return $userByEmail;
                }
            }

            // JANGAN pernah mencocokkan berdasarkan Nama jika baris Excel memiliki NRP yang berbeda!
            return null;
        }

        // 2. If no NRP provided, check by explicit email within this subject
        if (!empty($email)) {
            $userByEmail = User::where('role', User::ROLE_STUDENT)
                ->where('subject_id', $subjectId)
                ->where('email', strtolower(trim($email)))
                ->first();
            if ($userByEmail) {
                return $userByEmail;
            }
        }

        // 3. Fallback: match by exact Name ONLY if neither NRP nor email was specified in the row, within this subject
        return User::where('role', User::ROLE_STUDENT)
            ->where('subject_id', $subjectId)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->whereNull('nrp')
            ->first();
    }

    /**
     * Generate a unique email address for a student, supporting multiple subjects per NRP.
     */
    protected function generateUniqueEmail(string $name, ?string $nrp, ?string $explicitEmail, ?User $existingUser, int $subjectId = 1): string
    {
        // 1. Explicit email if provided and available
        if (!empty($explicitEmail)) {
            $candidate = strtolower(trim($explicitEmail));
            if (!User::where('email', $candidate)->where('id', '!=', $existingUser?->id)->exists()) {
                return $candidate;
            }
        }

        $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';
        $subjectSuffix = match ($subjectId) {
            2 => '.jp',
            3 => '.mat',
            default => '',
        };

        // 2. Candidate from NRP:
        if (!empty($cleanNrp)) {
            if ($subjectId !== 1 && !empty($subjectSuffix)) {
                $candidate = strtolower("{$cleanNrp}{$subjectSuffix}@musashi.co.id");
                if (!User::where('email', $candidate)->where('id', '!=', $existingUser?->id)->exists()) {
                    return $candidate;
                }
                $candidateId = strtolower("{$cleanNrp}{$subjectSuffix}@musashi.id");
                if (!User::where('email', $candidateId)->where('id', '!=', $existingUser?->id)->exists()) {
                    return $candidateId;
                }
            } else {
                $baseCandidate = strtolower("{$cleanNrp}@musashi.co.id");
                if (!User::where('email', $baseCandidate)->where('id', '!=', $existingUser?->id)->exists()) {
                    return $baseCandidate;
                }
                $baseIdCandidate = strtolower("{$cleanNrp}@musashi.id");
                if (!User::where('email', $baseIdCandidate)->where('id', '!=', $existingUser?->id)->exists()) {
                    return $baseIdCandidate;
                }
            }
        }

        // 3. Fallback from name slug:
        $slug = Str::slug($name, '.');
        if (!empty($slug)) {
            $candidate = strtolower("{$slug}{$subjectSuffix}@musashi.co.id");
            if (!User::where('email', $candidate)->where('id', '!=', $existingUser?->id)->exists()) {
                return $candidate;
            }
        }

        // 4. Guaranteed collision-free loop
        $prefix = !empty($cleanNrp) ? "{$cleanNrp}{$subjectSuffix}" : ($slug ?: 'student');
        $counter = 1;
        while (true) {
            $candidate = strtolower("{$prefix}.{$counter}@musashi.co.id");
            if (!User::where('email', $candidate)->where('id', '!=', $existingUser?->id)->exists()) {
                return $candidate;
            }
            $counter++;
        }
    }
}

