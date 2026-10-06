<?php

namespace App\Services;

use App\Models\EnglishClass;
use App\Models\EnglishGrade;
use App\Models\StudentEnrollment;
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
     * Native XLSX parser using ZipArchive and XML (fast, standalone, supports all sheets).
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Gagal membuka file Excel (.xlsx). Pastikan format file valid.');
        }

        // 1. Read shared strings table
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

        // 2. Discover all worksheets from xl/workbook.xml & relations
        $sheetFiles = []; // [sheetName => internalXmlPath]
        if (($idx = $zip->locateName('xl/workbook.xml')) !== false) {
            $wbXml = simplexml_load_string($zip->getFromIndex($idx));
            $sheetMap = [];
            if ($wbXml && isset($wbXml->sheets->sheet)) {
                foreach ($wbXml->sheets->sheet as $s) {
                    $rId = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                    $sheetMap[$rId] = trim((string)$s['name']);
                }
            }

            if (($relIdx = $zip->locateName('xl/_rels/workbook.xml.rels')) !== false) {
                $relsXml = simplexml_load_string($zip->getFromIndex($relIdx));
                if ($relsXml && isset($relsXml->Relationship)) {
                    foreach ($relsXml->Relationship as $rel) {
                        $id = (string)$rel['Id'];
                        $target = (string)$rel['Target'];
                        if (isset($sheetMap[$id])) {
                            $path = str_starts_with($target, 'worksheets/') ? 'xl/' . $target : 'xl/' . ltrim($target, '/');
                            $sheetFiles[$sheetMap[$id]] = $path;
                        }
                    }
                }
            }
        }

        // Fallback to sheet1 if no relations found
        if (empty($sheetFiles)) {
            if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
                $sheetFiles['Sheet1'] = 'xl/worksheets/sheet1.xml';
            } else {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $stat['name'])) {
                        $sheetFiles["Sheet" . ($i + 1)] = $stat['name'];
                        break;
                    }
                }
            }
        }

        $allResults = [];

        foreach ($sheetFiles as $sheetName => $xmlPath) {
            $content = $zip->getFromName($xmlPath);
            if (!$content) {
                continue;
            }

            $sheetXml = simplexml_load_string($content);
            if (!$sheetXml || !isset($sheetXml->sheetData)) {
                continue;
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

            $sheetRows = $this->normalizeRows($rawRows, $sheetName);
            if (!empty($sheetRows)) {
                $allResults = array_merge($allResults, $sheetRows);
            }
        }

        $zip->close();

        return $allResults;
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
    protected function normalizeRows(array $rawRows, ?string $sheetName = null): array
    {
        if (empty($rawRows)) {
            return [];
        }

        // 1. Try to detect class name from sheet name or banner rows before header
        $inferredClass = null;
        if ($sheetName) {
            $cleanSheet = trim($sheetName);
            if (preg_match('/^(Class|Grup|Group|Kelas)\s+([A-Za-z0-9\-_]+)$/i', $cleanSheet) ||
                preg_match('/^[A-Za-z]\d*$/i', $cleanSheet)) {
                $inferredClass = $cleanSheet;
            }
        }

        // Scan for class name in top rows (e.g. "CLASS : Class I" or cell A="CLASS :", cell B="Class I")
        foreach ($rawRows as $rNum => $cols) {
            if ($rNum > 10) {
                break;
            }
            foreach ($cols as $colLetter => $val) {
                $valUpper = strtoupper(trim((string)$val));
                if (preg_match('/^(?:CLASS|KELAS|GRUP|GROUP)\s*:\s*(.+)$/i', $valUpper, $m)) {
                    $cand = trim($m[1]);
                    if (!empty($cand)) {
                        $inferredClass = $cand;
                        break 2;
                    }
                }
                if (in_array($valUpper, ['CLASS :', 'CLASS:', 'KELAS :', 'KELAS:'])) {
                    $nextLetter = chr(ord($colLetter) + 1);
                    if (!empty($cols[$nextLetter])) {
                        $cand = trim((string)$cols[$nextLetter]);
                        if (!empty($cand)) {
                            $inferredClass = $cand;
                            break 2;
                        }
                    }
                }
            }
        }

        // 2. Find header row (usually row 1 to 10)
        $headerRow = null;
        $headerMap = []; // letter => standard key

        foreach ($rawRows as $rNum => $cols) {
            $foundNameCol = null;
            foreach ($cols as $colLetter => $val) {
                $cleanVal = strtoupper(trim((string)$val));
                // Skip title banners
                if (str_contains($cleanVal, 'LIST') || str_contains($cleanVal, 'ATTENDANCE') ||
                    str_contains($cleanVal, 'DAFTAR') || str_contains($cleanVal, 'ABSENSI') ||
                    str_contains($cleanVal, 'REKAP') || str_contains($cleanVal, 'LAPORAN')) {
                    continue;
                }
                if (in_array($cleanVal, ['NAME', 'NAMA', 'NAMA SISWA', 'NAMA PESERTA', 'STUDENT NAME', 'FULL NAME', 'STUDENT', 'PESERTA']) ||
                    preg_match('/^(NAMA|NAME|PESERTA)(\s+(SISWA|PESERTA|LENGKAP|STUDENT|MURID))?$/i', $cleanVal)) {
                    $foundNameCol = $colLetter;
                    break;
                }
            }

            if ($foundNameCol !== null) {
                $headerRow = $rNum;
                foreach ($cols as $colLetter => $rawHeader) {
                    $cleanHeader = strtoupper(trim((string)$rawHeader));
                    if (preg_match('/^(KELAS|CLASS|GRUP|GROUP)(\s+.*)?$/i', $cleanHeader)) {
                        $headerMap[$colLetter] = 'class_name';
                    } elseif (preg_match('/^(NRP|NIK|NIS|ID|NO REG|NO INDUK|NOMOR INDUK)$/i', $cleanHeader)) {
                        $headerMap[$colLetter] = 'nrp';
                    } elseif ($colLetter === $foundNameCol || preg_match('/^(NAMA|NAME|PESERTA)(\s+(SISWA|PESERTA|LENGKAP|STUDENT|MURID))?$/i', $cleanHeader)) {
                        $headerMap[$colLetter] = 'name';
                    } elseif (str_contains($cleanHeader, 'BAGIAN') || str_contains($cleanHeader, 'DIVISI') || str_contains($cleanHeader, 'DEPT') || str_contains($cleanHeader, 'SECTION')) {
                        $headerMap[$colLetter] = 'division';
                    } elseif (str_contains($cleanHeader, 'EMAIL') || str_contains($cleanHeader, 'SUREL')) {
                        $headerMap[$colLetter] = 'email';
                    } elseif (str_contains($cleanHeader, 'PASSWORD') || str_contains($cleanHeader, 'SANDI') || str_contains($cleanHeader, 'PASS')) {
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

        $ignoreNames = [
            'NAME', 'NAMA', 'NAMA SISWA', 'NAMA PESERTA', 'STUDENT NAME', 'PESERTA', 'STUDENT',
            '0', '-', 'TOTAL', 'FINAL SCORE', 'AVERAGE', 'JUMLAH', 'ATTENDANCE', 'WEEKLY FEEDBACK',
            'CLASS :', 'TEACHER :', 'PERIODE :', 'SUBJECT :', 'EXAMINATION', 'MEETING',
        ];

        $result = [];
        $seenStudents = [];

        foreach ($rawRows as $rNum => $cols) {
            if ($rNum <= $headerRow) {
                continue;
            }

            $item = [
                'class_name' => $inferredClass,
                'nrp' => null,
                'name' => null,
                'division' => null,
                'email' => null,
                'password' => null,
                'phone' => null,
            ];

            foreach ($cols as $letter => $val) {
                if (isset($headerMap[$letter])) {
                    $cleanVal = trim((string)$val);
                    if ($cleanVal !== '') {
                        $item[$headerMap[$letter]] = $cleanVal;
                    }
                }
            }

            $name = trim((string)($item['name'] ?? ''));

            // Skip if empty or numeric (e.g. row numbers) or too short or in ignore list
            if (empty($name) || is_numeric($name) || strlen($name) < 2 || in_array(strtoupper($name), $ignoreNames)) {
                continue;
            }

            // Skip if name matches header/banner phrases
            $upperName = strtoupper($name);
            if (str_starts_with($upperName, 'CLASS :') || str_starts_with($upperName, 'TEACHER :') ||
                str_starts_with($upperName, 'PERIODE :') || str_starts_with($upperName, 'SUBJECT :') ||
                str_contains($upperName, 'ATTENDANCE LIST') || str_contains($upperName, 'WEEKLY FEEDBACK') ||
                str_contains($upperName, '1ST WEEK') || str_contains($upperName, '2ND WEEK') ||
                str_contains($upperName, '3RD WEEK') || str_contains($upperName, '4TH WEEK')) {
                continue;
            }

            // Deduplicate within the same sheet
            $dedupKey = ($item['nrp'] ?? '') . '|' . strtolower($item['name']) . '|' . ($item['class_name'] ?? '');
            if (isset($seenStudents[$dedupKey])) {
                continue;
            }
            $seenStudents[$dedupKey] = true;

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Import an array of parsed student rows into the database for a specific subject with ultra-fast bulk execution.
     */
    public function importStudents(array $rows, int $subjectId, ?int $creatorId = null, ?int $teacherId = null): array
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $subject = Subject::with('levels')->findOrFail($subjectId);
        $teacher = $teacherId ? User::find($teacherId) : null;
        $firstLevel = $subject->levels->firstWhere('order', 1);

        $validRows = [];
        $seenInBatch = [];
        foreach ($rows as $index => $row) {
            $name = trim($row['name'] ?? '');
            if (empty($name) || is_numeric($name) || strlen($name) < 2 || in_array(strtoupper($name), ['NAMA', 'NAME', 'NAMA PESERTA', 'PESERTA', 'STUDENT', '0', '-'])) {
                continue;
            }
            $nrp = !empty($row['nrp']) ? trim($row['nrp']) : '';
            $className = !empty($row['class_name']) ? trim($row['class_name']) : '';
            $email = !empty($row['email']) ? strtolower(trim($row['email'])) : '';

            $batchKey = $email ?: ($nrp ? "nrp:{$nrp}" : "name:" . strtolower($name)) . "|cls:{$className}";
            if (isset($seenInBatch[$batchKey])) {
                continue;
            }
            $seenInBatch[$batchKey] = true;
            $validRows[] = $row;
        }

        if (empty($validRows)) {
            return [
                'imported' => 0,
                'updated' => 0,
                'errors' => [],
                'created_classes' => [],
            ];
        }

        $now = now();
        $nowStr = $now->toDateTimeString();

        // 1. Bulk ensure classes exist in english_classes (1 query)
        $classNames = array_values(array_filter(array_unique(array_map(fn($r) => trim($r['class_name'] ?? ''), $validRows))));
        $existingClasses = !empty($classNames)
            ? EnglishClass::where('subject_id', $subjectId)->whereIn('name', $classNames)->pluck('name')->toArray()
            : [];
        $missingClasses = array_diff($classNames, $existingClasses);
        $createdClasses = [];

        if (!empty($missingClasses)) {
            $classInserts = [];
            foreach ($missingClasses as $clsName) {
                $classInserts[] = [
                    'name' => $clsName,
                    'subject_id' => $subjectId,
                    'level_name' => 'Beginner',
                    'is_active' => true,
                    'sort_order' => 99,
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ];
                $createdClasses[$clsName] = true;
            }
            EnglishClass::insert($classInserts);
        }

        // 2. Pre-load existing students by NRP and Email (2 queries)
        $nrps = array_values(array_filter(array_unique(array_map(fn($r) => trim((string)($r['nrp'] ?? '')), $validRows))));
        $explicitEmails = array_values(array_filter(array_unique(array_map(fn($r) => strtolower(trim((string)($r['email'] ?? ''))), $validRows))));

        $existingStudentsByNrp = !empty($nrps)
            ? User::where('role', User::ROLE_STUDENT)->where('subject_id', $subjectId)->whereIn('nrp', $nrps)->get()->keyBy('nrp')
            : collect();

        $existingStudentsByEmail = !empty($explicitEmails)
            ? User::where('role', User::ROLE_STUDENT)->where('subject_id', $subjectId)->whereIn('email', $explicitEmails)->get()->keyBy(fn($u) => strtolower($u->email))
            : collect();

        // 3. Pre-load all existing emails into an in-memory hash set (1 fast query)
        $existingEmailsMap = User::pluck('email')
            ->filter()
            ->mapWithKeys(fn($e) => [strtolower($e) => true])
            ->toArray();

        // 4. Password hashing cache (rounds 4 for ultra-fast mass bcrypt processing ~1.9ms vs 420ms)
        $passwordCache = [];
        $defaultHashedPassword = Hash::make('password', ['rounds' => 4]);

        $importedCount = 0;
        $updatedCount = 0;
        $errors = [];
        $newUsersData = [];
        $studentsToEnroll = []; // list of ['student_id' => int, 'class_name' => ?string]

        foreach ($validRows as $index => $row) {
            $rowNumber = $index + 1;
            $name = trim($row['name']);
            $nrp = !empty($row['nrp']) ? trim($row['nrp']) : null;
            $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';
            $className = !empty($row['class_name']) ? trim($row['class_name']) : null;
            $division = !empty($row['division']) ? trim($row['division']) : null;
            $phone = !empty($row['phone']) ? trim($row['phone']) : null;
            $explicitEmail = !empty($row['email']) ? strtolower(trim($row['email'])) : null;

            $defaultPassword = $cleanNrp ? "{$cleanNrp}@musashi" : 'password';
            $plainPassword = !empty($row['password']) && trim($row['password']) !== 'password'
                ? trim($row['password'])
                : $defaultPassword;

            if (!isset($passwordCache[$plainPassword])) {
                $passwordCache[$plainPassword] = ($plainPassword === 'password')
                    ? $defaultHashedPassword
                    : Hash::make($plainPassword, ['rounds' => 4]);
            }
            $hashedPassword = $passwordCache[$plainPassword];

            // Match existing student
            $existing = null;
            if ($nrp && isset($existingStudentsByNrp[$nrp])) {
                $existing = $existingStudentsByNrp[$nrp];
            } elseif ($explicitEmail && isset($existingStudentsByEmail[$explicitEmail])) {
                $existing = $existingStudentsByEmail[$explicitEmail];
            }

            try {
                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'division' => $division ?? $existing->division,
                        'class_name' => $className ?? $existing->class_name,
                        'phone' => $phone ?? $existing->phone,
                        'status' => 'active',
                    ]);
                    $studentsToEnroll[] = [
                        'student_id' => $existing->id,
                        'class_name' => $className ?? $existing->class_name,
                    ];
                    $updatedCount++;
                } else {
                    // Generate unique email in-memory
                    $email = $this->generateFastEmail($name, $cleanNrp, $explicitEmail, $subjectId, $existingEmailsMap);
                    $existingEmailsMap[$email] = true;

                    $newUsersData[] = [
                        'name' => $name,
                        'nrp' => $nrp,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => User::ROLE_STUDENT,
                        'division' => $division,
                        'class_name' => $className,
                        'subject_id' => $subjectId,
                        'phone' => $phone,
                        'status' => 'active',
                        'created_by' => $teacherId ?? $creatorId,
                        'created_at' => $nowStr,
                        'updated_at' => $nowStr,
                    ];
                    $importedCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Baris {$rowNumber} ({$name}): " . $e->getMessage();
            }
        }

        // 5. Bulk insert all new users in chunks (1 query per 100 rows)
        if (!empty($newUsersData)) {
            foreach (array_chunk($newUsersData, 100) as $chunk) {
                DB::table('users')->insert($chunk);
            }
            $newEmails = array_column($newUsersData, 'email');
            $createdStudents = User::whereIn('email', $newEmails)->get(['id', 'class_name']);
            foreach ($createdStudents as $student) {
                $studentsToEnroll[] = [
                    'student_id' => $student->id,
                    'class_name' => $student->class_name,
                ];
            }
        }

        // 6. Bulk upsert StudentEnrollment (1 query for all students)
        if (!empty($studentsToEnroll)) {
            $enrollmentRows = [];
            foreach ($studentsToEnroll as $item) {
                $enrollmentRows[] = [
                    'student_id' => $item['student_id'],
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'class_code' => $item['class_name'] ?? 'REGULAR',
                    'enrolled_at' => $nowStr,
                    'status' => 'active',
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ];
            }
            StudentEnrollment::upsert(
                $enrollmentRows,
                ['student_id', 'subject_id'],
                ['teacher_id', 'class_code', 'status', 'updated_at']
            );
        }

        // 7. Bulk upsert Level 1 UserLevelStatus and UserProgress (2 queries for all students)
        if ($firstLevel && !empty($studentsToEnroll)) {
            $levelStatusRows = [];
            $progressRows = [];
            foreach ($studentsToEnroll as $item) {
                $levelStatusRows[] = [
                    'user_id' => $item['student_id'],
                    'level_id' => $firstLevel->id,
                    'points' => 0,
                    'is_unlocked' => true,
                    'is_completed' => false,
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ];
                $progressRows[] = [
                    'user_id' => $item['student_id'],
                    'subject_id' => $subjectId,
                    'current_level_id' => $firstLevel->id,
                    'current_points' => 0,
                    'is_completed' => false,
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ];
            }
            UserLevelStatus::upsert($levelStatusRows, ['user_id', 'level_id'], ['is_unlocked', 'updated_at']);
            UserProgress::upsert($progressRows, ['user_id', 'subject_id'], ['current_level_id', 'updated_at']);
        }

        // 8. Bulk upsert EnglishGrade for English students (1 query for all students)
        if ($subjectId === 1 && !empty($studentsToEnroll)) {
            $gradeRows = [];
            foreach ($studentsToEnroll as $item) {
                if (empty($item['class_name'])) {
                    continue;
                }
                $gradeRows[] = [
                    'student_id' => $item['student_id'],
                    'class_name' => $item['class_name'],
                    'week' => 1,
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
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ];
            }
            if (!empty($gradeRows)) {
                EnglishGrade::upsert(
                    $gradeRows,
                    ['student_id', 'class_name', 'week'],
                    ['teacher_id', 'updated_at']
                );
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
     * Fast in-memory email generation without DB round-trips.
     */
    protected function generateFastEmail(string $name, string $cleanNrp, ?string $explicitEmail, int $subjectId, array &$existingMap): string
    {
        if (!empty($explicitEmail) && !isset($existingMap[$explicitEmail])) {
            return $explicitEmail;
        }

        $subjectSuffix = match ($subjectId) {
            2 => '.jp',
            3 => '.mat',
            default => '',
        };

        if (!empty($cleanNrp)) {
            $c1 = strtolower("{$cleanNrp}{$subjectSuffix}@musashi.co.id");
            if (!isset($existingMap[$c1])) {
                return $c1;
            }
            $c2 = strtolower("{$cleanNrp}{$subjectSuffix}@musashi.id");
            if (!isset($existingMap[$c2])) {
                return $c2;
            }
        }

        $slug = Str::slug($name, '.');
        if (!empty($slug)) {
            $cSlug = strtolower("{$slug}{$subjectSuffix}@musashi.co.id");
            if (!isset($existingMap[$cSlug])) {
                return $cSlug;
            }
        }

        $prefix = !empty($cleanNrp) ? "{$cleanNrp}{$subjectSuffix}" : ($slug ?: 'student');
        $counter = 1;
        while (true) {
            $cNum = strtolower("{$prefix}.{$counter}@musashi.co.id");
            if (!isset($existingMap[$cNum])) {
                return $cNum;
            }
            $counter++;
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
}
