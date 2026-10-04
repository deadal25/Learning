<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserLevelStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnglishAttendanceExportService
{
    /**
     * Format a YYYY-MM-DD date into English format with ordinal suffix (e.g. July 13th, 2026).
     */
    public static function formatOrdinalDate(string $dateStr): string
    {
        try {
            $dt = Carbon::parse($dateStr);
            $day = $dt->day;
            if ($day >= 11 && $day <= 13) {
                $suffix = 'th';
            } else {
                $suffix = match ($day % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };
            }
            return $dt->format('F') . ' ' . $day . $suffix . ', ' . $dt->format('Y');
        } catch (\Exception) {
            return $dateStr;
        }
    }

    /**
     * Get preview data for website rendering before download.
     */
    public static function getPreviewData(string $date, string $week = '1', string $classSheet = 'Class B1', ?User $teacher = null): array
    {
        $teacher = $teacher ?? Auth::user();
        $englishSubject = Subject::where('slug', 'bahasa-inggris')
            ->orWhere('name', 'like', '%Inggris%')
            ->first();

        $teacherName = $teacher?->name ?? 'Miss Sarah Jenkins';
        $subjectName = 'English';
        $cleanClassName = $classSheet === 'all' ? 'Class B1' : $classSheet;

        // Fetch students enrolled in English
        $studentsQuery = User::where('role', User::ROLE_STUDENT)
            ->where(function ($query) use ($englishSubject, $teacher) {
                if ($englishSubject) {
                    $query->whereHas('enrollments', function ($q) use ($englishSubject) {
                        $q->where('subject_id', $englishSubject->id);
                    });
                }
                if ($teacher && $teacher->isAdmin() && !$teacher->isSuperAdmin()) {
                    $query->orWhereHas('enrollments', function ($q) use ($teacher) {
                        $q->where('teacher_id', $teacher->id);
                    });
                }
            })
            ->orderBy('name', 'asc');

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $students = User::where('role', User::ROLE_STUDENT)->orderBy('name', 'asc')->get();
        }

        // Fetch attendances for selected date
        $attendances = Attendance::whereIn('user_id', $students->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        // Week list to render
        $weekTitles = [
            '1' => '1ST WEEK',
            '2' => '2ND WEEK',
            '3' => '3RD WEEK',
            '4' => '4TH WEEK',
        ];

        $weeksToRender = [];
        if ($week === 'all') {
            foreach ($weekTitles as $k => $title) {
                $weeksToRender[] = ['key' => $k, 'title' => $title];
            }
        } else {
            $k = (string)$week;
            $title = $weekTitles[$k] ?? '1ST WEEK';
            $weeksToRender[] = ['key' => $k, 'title' => $title];
        }

        // Build 15 student rows
        $rows = [];
        for ($i = 0; $i < 15; $i++) {
            $no = $i + 1;
            if ($i < $students->count()) {
                $student = $students[$i];
                $att = $attendances->get($student->id);
                $status = $att?->status ?? 'belum_absen';
                $notes = $att?->notes ?? '';

                $latestScore = UserLevelStatus::where('user_id', $student->id)
                    ->where('points', '>', 0)
                    ->latest('updated_at')
                    ->value('points');

                $examBase = $latestScore ? min(100, max(60, (int)$latestScore)) : ($status === 'hadir' ? 85 : null);

                $m1 = $status === 'hadir' ? 85.0 : ($status === 'izin_keterangan' ? 75.0 : ($status === 'izin_tanpa_keterangan' ? 0.0 : null));
                $m2 = null;
                $m3 = null;
                $m4 = null;

                // Attendance formula: (D+E+F+G)/4
                $mValues = array_filter([$m1, $m2, $m3, $m4], fn($v) => $v !== null);
                $attendanceAvg = !empty($mValues) ? round(array_sum($mValues) / 4, 1) : null;

                $fluency = $examBase ? (float)$examBase : null;
                $grammar = $examBase ? (float)min(100, $examBase + 2) : null;
                $pronunciation = $examBase ? (float)max(60, $examBase - 2) : null;
                $vocabulary = $examBase ? (float)min(100, $examBase + 3) : null;

                $examValues = array_filter([$fluency, $grammar, $pronunciation, $vocabulary], fn($v) => $v !== null);
                $totalExam = !empty($examValues) ? round(array_sum($examValues) / 4, 1) : null;

                $finalScore = ($attendanceAvg !== null && $totalExam !== null) ? round(($attendanceAvg + $totalExam) / 2, 1) : ($attendanceAvg ?? $totalExam);

                $rows[] = [
                    'no' => $no,
                    'name' => $student->name,
                    'feedback' => $notes ?: ($status === 'hadir' ? 'Good participation' : ''),
                    'm1' => $m1,
                    'm2' => $m2,
                    'm3' => $m3,
                    'm4' => $m4,
                    'attendance' => $attendanceAvg,
                    'fluency' => $fluency,
                    'grammar' => $grammar,
                    'pronunciation' => $pronunciation,
                    'vocabulary' => $vocabulary,
                    'total_exam' => $totalExam,
                    'final_score' => $finalScore,
                ];
            } else {
                $rows[] = [
                    'no' => $no,
                    'name' => '',
                    'feedback' => '',
                    'm1' => null,
                    'm2' => null,
                    'm3' => null,
                    'm4' => null,
                    'attendance' => null,
                    'fluency' => null,
                    'grammar' => null,
                    'pronunciation' => null,
                    'vocabulary' => null,
                    'total_exam' => null,
                    'final_score' => null,
                ];
            }
        }

        return [
            'date' => $date,
            'formatted_date' => self::formatOrdinalDate($date),
            'week' => $week,
            'class_sheet' => $classSheet,
            'class_name' => $cleanClassName,
            'teacher_name' => $teacherName,
            'subject_name' => $subjectName,
            'weeks_to_render' => $weeksToRender,
            'rows' => $rows,
            'total_students' => $students->count(),
        ];
    }

    /**
     * Generate an Excel file for English attendance based on Absensi.xlsx template.
     *
     * @param string $date YYYY-MM-DD
     * @param string $week 'all', '1', '2', '3', '4'
     * @param string $classSheet 'all' or specific sheet name like 'Class B1'
     * @param User|null $teacher
     * @return string Absolute file path to the generated Excel file
     */
    public static function generate(string $date, string $week = 'all', string $classSheet = 'all', ?User $teacher = null): string
    {
        $teacher = $teacher ?? Auth::user();
        $englishSubject = Subject::where('slug', 'bahasa-inggris')
            ->orWhere('name', 'like', '%Inggris%')
            ->first();

        $teacherName = $teacher?->name ?? 'Miss Sarah Jenkins';
        $subjectName = 'English';

        // 1. Fetch students enrolled in English
        $studentsQuery = User::where('role', User::ROLE_STUDENT)
            ->where(function ($query) use ($englishSubject, $teacher) {
                if ($englishSubject) {
                    $query->whereHas('enrollments', function ($q) use ($englishSubject) {
                        $q->where('subject_id', $englishSubject->id);
                    });
                }
                if ($teacher && $teacher->isAdmin() && !$teacher->isSuperAdmin()) {
                    $query->orWhereHas('enrollments', function ($q) use ($teacher) {
                        $q->where('teacher_id', $teacher->id);
                    });
                }
            })
            ->orderBy('name', 'asc');

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $students = User::where('role', User::ROLE_STUDENT)->orderBy('name', 'asc')->get();
        }

        // 2. Fetch attendances for selected date
        $attendances = Attendance::whereIn('user_id', $students->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        // 3. Prepare student array for export
        $studentsData = [];
        foreach ($students as $student) {
            $att = $attendances->get($student->id);
            $status = $att?->status ?? 'belum_absen';
            $notes = $att?->notes ?? '';

            $latestScore = UserLevelStatus::where('user_id', $student->id)
                ->where('points', '>', 0)
                ->latest('updated_at')
                ->value('points');

            $examBase = $latestScore ? min(100, max(60, (int)$latestScore)) : ($status === 'hadir' ? 85 : null);

            $studentEntry = [
                'id' => $student->id,
                'name' => $student->name,
                'status' => $status,
                'notes' => $notes,
                'feedback' => $att?->notes ?? ($status === 'hadir' ? 'Good participation' : ''),
                'm1' => $status === 'hadir' ? 85 : ($status === 'izin_keterangan' ? 75 : ($status === 'izin_tanpa_keterangan' ? 0 : null)),
                'm2' => null,
                'm3' => null,
                'm4' => null,
                'fluency' => $examBase ? $examBase : null,
                'grammar' => $examBase ? min(100, $examBase + 2) : null,
                'pronunciation' => $examBase ? max(60, $examBase - 2) : null,
                'vocabulary' => $examBase ? min(100, $examBase + 3) : null,
            ];

            $studentsData[] = $studentEntry;
        }

        // 4. Save students data to a temporary json file for python script
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $jsonFile = $tempDir . '/students_' . uniqid() . '.json';
        file_put_contents($jsonFile, json_encode($studentsData, JSON_UNESCAPED_UNICODE));

        // 5. Output file
        $exportsDir = storage_path('app/exports');
        if (!is_dir($exportsDir)) {
            mkdir($exportsDir, 0755, true);
        }
        $cleanDate = str_replace('-', '', $date);
        $outputFile = $exportsDir . "/Absensi_English_{$cleanDate}_w{$week}_" . uniqid() . ".xlsx";

        // 6. Python script invocation
        $python = DocumentConverterService::getPythonPath();
        $script = base_path('scripts/export_english_attendance.py');
        $template = base_path('Absensi.xlsx');

        $cmd = escapeshellarg($python) . ' ' .
               escapeshellarg($script) . ' ' .
               '--template ' . escapeshellarg($template) . ' ' .
               '--output ' . escapeshellarg($outputFile) . ' ' .
               '--date ' . escapeshellarg($date) . ' ' .
               '--week ' . escapeshellarg($week) . ' ' .
               '--class-sheet ' . escapeshellarg($classSheet) . ' ' .
               '--teacher ' . escapeshellarg($teacherName) . ' ' .
               '--subject ' . escapeshellarg($subjectName) . ' ' .
               '--students-json ' . escapeshellarg($jsonFile);

        exec($cmd, $output, $returnCode);

        // Clean up temp json
        if (file_exists($jsonFile)) {
            @unlink($jsonFile);
        }

        if ($returnCode !== 0 || !file_exists($outputFile)) {
            Log::error('English attendance export failed', [
                'returnCode' => $returnCode,
                'output' => $output,
                'cmd' => $cmd
            ]);
            throw new \RuntimeException('Gagal mengekspor data absensi ke format Excel: ' . implode(' ', $output));
        }

        return $outputFile;
    }
}
