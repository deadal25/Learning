<?php

namespace App\Services;

use App\Models\EnglishClass;
use App\Models\EnglishGrade;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnglishGradeExportService
{
    /**
     * Generate an Excel file for English Grades & Feedback based on Absensi.xlsx template.
     *
     * @param string $month 'all', '1', '2', '3', '4', or '5'
     * @param string $classSheet 'all' or specific class name e.g. 'Class B1'
     * @param User|null $teacher
     * @return string Absolute path to generated .xlsx file
     */
    public static function generate(string $month = 'all', string $classSheet = 'all', ?User $teacher = null): string
    {
        $teacher = $teacher ?? Auth::user();
        $teacherName = $teacher?->name ?? 'Miss Sarah Jenkins';
        $assignedStudentIds = $teacher->getAssignedStudentIds();

        // 1. Determine classes to export
        if ($classSheet !== 'all') {
            $classes = EnglishClass::where('is_active', true)
                ->where('subject_id', 1)
                ->where('name', $classSheet)
                ->get();
            if ($classes->isEmpty()) {
                $classes = collect([(object)['name' => $classSheet]]);
            }
        } else {
            $classes = EnglishClass::where('is_active', true)
                ->where('subject_id', 1)
                ->orderBy('sort_order')
                ->get();
        }

        $classesPayload = [];

        // 2. Fetch data for each class
        foreach ($classes as $cls) {
            $className = $cls->name;

            // Students in this class
            $students = User::where('role', User::ROLE_STUDENT)
                ->where('subject_id', 1)
                ->where('class_name', $className)
                ->whereIn('id', $assignedStudentIds)
                ->orderBy('name', 'asc')
                ->get();

            $monthsData = [];
            $monthsToFetch = ($month === 'all') ? [1, 2, 3, 4, 5] : [(int)$month];

            foreach ($monthsToFetch as $m) {
                $grades = EnglishGrade::where('class_name', $className)
                    ->where('week', $m)
                    ->where('teacher_id', $teacher->id)
                    ->get()
                    ->keyBy('student_id');

                $studentsList = [];
                foreach ($students as $idx => $student) {
                    $g = $grades->get($student->id);
                    $studentsList[] = [
                        'no' => $idx + 1,
                        'name' => $student->name,
                        'feedback' => $g?->feedback ?? '',
                        'meeting_1' => $g ? $g->meeting_1 : null,
                        'meeting_2' => $g ? $g->meeting_2 : null,
                        'meeting_3' => $g ? $g->meeting_3 : null,
                        'meeting_4' => $g ? $g->meeting_4 : null,
                        'attendance_score' => $g ? $g->attendance_score : null,
                        'fluency' => $g ? $g->fluency : null,
                        'grammar' => $g ? $g->grammar : null,
                        'pronunciation' => $g ? $g->pronunciation : null,
                        'vocabulary' => $g ? $g->vocabulary : null,
                        'total_exam' => $g ? $g->total_exam : null,
                        'final_score' => $g ? $g->final_score : null,
                    ];
                }

                $monthsData[(string)$m] = $studentsList;
            }

            $classesPayload[$className] = [
                'teacher' => $teacherName,
                'period' => ($month === 'all') ? 'Bulan 1 - Bulan 5' : "Bulan Ke-{$month}",
                'months' => $monthsData,
            ];
        }

        // 3. Save payload to temporary JSON file
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $jsonFile = $tempDir . '/grades_payload_' . uniqid() . '.json';
        file_put_contents($jsonFile, json_encode(['classes' => $classesPayload], JSON_UNESCAPED_UNICODE));

        // 4. Output file destination
        $exportsDir = storage_path('app/exports');
        if (!is_dir($exportsDir)) {
            mkdir($exportsDir, 0755, true);
        }

        $cleanClass = $classSheet === 'all' ? 'Semua_Kelas' : str_replace(' ', '_', $classSheet);
        $periodTag = $month === 'all' ? 'Bulan_1_sd_5' : "Bulan_{$month}";
        $outputFile = $exportsDir . "/Nilai_English_{$cleanClass}_{$periodTag}_" . uniqid() . ".xlsx";

        // 5. Invoke python script
        $python = DocumentConverterService::getPythonPath();
        $script = base_path('scripts/export_english_grades.py');
        $template = base_path('Absensi.xlsx');

        $cmd = escapeshellarg($python) . ' ' .
               escapeshellarg($script) . ' ' .
               '--template ' . escapeshellarg($template) . ' ' .
               '--output ' . escapeshellarg($outputFile) . ' ' .
               '--month ' . escapeshellarg($month) . ' ' .
               '--class-sheet ' . escapeshellarg($classSheet) . ' ' .
               '--teacher ' . escapeshellarg($teacherName) . ' ' .
               '--subject ' . escapeshellarg('English') . ' ' .
               '--grades-json ' . escapeshellarg($jsonFile);

        exec($cmd, $output, $returnCode);

        // Clean up temp json
        if (file_exists($jsonFile)) {
            @unlink($jsonFile);
        }

        if ($returnCode !== 0 || !file_exists($outputFile)) {
            Log::error('English grades export failed', [
                'returnCode' => $returnCode,
                'output' => $output,
                'cmd' => $cmd
            ]);
            throw new \RuntimeException('Gagal mengekspor data nilai ke format Excel: ' . implode(' ', $output));
        }

        return $outputFile;
    }
}
