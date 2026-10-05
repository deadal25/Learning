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

        $templatePath = base_path('Absensi.xlsx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template Absensi.xlsx tidak ditemukan di {$templatePath}");
        }

        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($templatePath);

            if ($classSheet !== 'all') {
                $requestedClass = strtolower(trim($classSheet));
                $sheetNames = $spreadsheet->getSheetNames();
                $matchedSheetName = null;
                foreach ($sheetNames as $sname) {
                    if (strtolower(trim($sname)) === $requestedClass) {
                        $matchedSheetName = $sname;
                        break;
                    }
                }
                if ($matchedSheetName) {
                    foreach ($sheetNames as $sname) {
                        if ($sname !== $matchedSheetName) {
                            $idx = $spreadsheet->getIndex($spreadsheet->getSheetByName($sname));
                            $spreadsheet->removeSheetByIndex($idx);
                        }
                    }
                } else {
                    $firstSheet = $spreadsheet->getSheet(0);
                    $firstSheet->setTitle(substr($classSheet, 0, 31));
                    for ($i = count($sheetNames) - 1; $i >= 1; $i--) {
                        $spreadsheet->removeSheetByIndex($i);
                    }
                }
            }

            $time = strtotime($date) ?: time();
            $day = (int)date('j', $time);
            if ($day >= 11 && $day <= 13) {
                $suffix = 'th';
            } else {
                $suffix = match($day % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };
            }
            $formattedDate = date('F ', $time) . $day . $suffix . date(', Y', $time);

            $weekTitles = [
                '1' => '1ST WEEK',
                '2' => '2ND WEEK',
                '3' => '3RD WEEK',
                '4' => '4TH WEEK',
            ];

            $selectedWeek = strtolower(trim((string)$week));

            if (in_array($selectedWeek, ['1', '2', '3', '4'], true)) {
                $wTitle = $weekTitles[$selectedWeek];

                foreach ($spreadsheet->getAllSheets() as $ws) {
                    $cleanClassName = trim($ws->getTitle());
                    $ws->getCell('A1')->setValue($wTitle);
                    $ws->getCell('C4')->setValue($cleanClassName);
                    $ws->getCell('G4')->setValue($teacherName);
                    $ws->getCell('C5')->setValue($formattedDate);
                    $ws->getCell('G5')->setValue($subjectName);

                    for ($i = 0; $i < 15; $i++) {
                        $rowIdx = 8 + $i;
                        $ws->getCell([1, $rowIdx])->setValue($i + 1);

                        if ($i < count($studentsData)) {
                            $std = $studentsData[$i];
                            $ws->getCell([2, $rowIdx])->setValue($std['name'] ?? '');
                            $ws->getCell([3, $rowIdx])->setValue($std['notes'] ?: ($std['feedback'] ?? ''));

                            $m1 = $std['m1'] ?? null;
                            $m2 = $std['m2'] ?? null;
                            $m3 = $std['m3'] ?? null;
                            $m4 = $std['m4'] ?? null;

                            $ws->getCell([4, $rowIdx])->setValue($m1 !== null ? (float)$m1 : null);
                            $ws->getCell([5, $rowIdx])->setValue($m2 !== null ? (float)$m2 : null);
                            $ws->getCell([6, $rowIdx])->setValue($m3 !== null ? (float)$m3 : null);
                            $ws->getCell([7, $rowIdx])->setValue($m4 !== null ? (float)$m4 : null);

                            $fluency = $std['fluency'] ?? null;
                            $grammar = $std['grammar'] ?? null;
                            $pronunciation = $std['pronunciation'] ?? null;
                            $vocabulary = $std['vocabulary'] ?? null;

                            $ws->getCell([9, $rowIdx])->setValue($fluency !== null ? (float)$fluency : null);
                            $ws->getCell([10, $rowIdx])->setValue($grammar !== null ? (float)$grammar : null);
                            $ws->getCell([11, $rowIdx])->setValue($pronunciation !== null ? (float)$pronunciation : null);
                            $ws->getCell([12, $rowIdx])->setValue($vocabulary !== null ? (float)$vocabulary : null);
                        } else {
                            $ws->getCell([2, $rowIdx])->setValue(null);
                            $ws->getCell([3, $rowIdx])->setValue(null);
                            $ws->getCell([4, $rowIdx])->setValue(null);
                            $ws->getCell([5, $rowIdx])->setValue(null);
                            $ws->getCell([6, $rowIdx])->setValue(null);
                            $ws->getCell([7, $rowIdx])->setValue(null);
                            $ws->getCell([9, $rowIdx])->setValue(null);
                            $ws->getCell([10, $rowIdx])->setValue(null);
                            $ws->getCell([11, $rowIdx])->setValue(null);
                            $ws->getCell([12, $rowIdx])->setValue(null);
                        }

                        $ws->getCell([8, $rowIdx])->setValue("=(D{$rowIdx}+E{$rowIdx}+F{$rowIdx}+G{$rowIdx})/4");
                        $ws->getCell([13, $rowIdx])->setValue("=(I{$rowIdx}+J{$rowIdx}+K{$rowIdx}+L{$rowIdx})/4");
                        $ws->getCell([14, $rowIdx])->setValue("=((H{$rowIdx}+M{$rowIdx})/2)");
                    }

                    foreach ($ws->getMergeCells() as $range) {
                        $coords = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::splitRange($range);
                        $startRow = (int)preg_replace('/[^0-9]/', '', $coords[0][0]);
                        if ($startRow >= 23) {
                            $ws->unmergeCells($range);
                        }
                    }

                    $highestRow = $ws->getHighestRow();
                    if ($highestRow > 22) {
                        $ws->removeRow(23, $highestRow - 22);
                    }
                }
            } else {
                $weeksMeta = [
                    '1' => ['start_row' => 8, 'period_cell' => 'C5', 'teacher_cell' => 'G4', 'subject_cell' => 'G5', 'class_cell' => 'C4'],
                    '2' => ['start_row' => 31, 'period_cell' => 'C28', 'teacher_cell' => 'G27', 'subject_cell' => 'G28', 'class_cell' => 'C27'],
                    '3' => ['start_row' => 54, 'period_cell' => 'C51', 'teacher_cell' => 'G50', 'subject_cell' => 'G51', 'class_cell' => 'C50'],
                    '4' => ['start_row' => 77, 'period_cell' => 'C74', 'teacher_cell' => 'G73', 'subject_cell' => 'G74', 'class_cell' => 'C73'],
                ];

                foreach ($spreadsheet->getAllSheets() as $ws) {
                    $cleanClassName = trim($ws->getTitle());
                    foreach ($weeksMeta as $wKey => $meta) {
                        $ws->getCell($meta['class_cell'])->setValue($cleanClassName);
                        $ws->getCell($meta['teacher_cell'])->setValue($teacherName);
                        $ws->getCell($meta['subject_cell'])->setValue($subjectName);
                        $ws->getCell($meta['period_cell'])->setValue($formattedDate);
                    }

                    for ($i = 0; $i < 15; $i++) {
                        $rowIdx = 8 + $i;
                        if ($i < count($studentsData)) {
                            $std = $studentsData[$i];
                            $stdName = $std['name'] ?? '';
                            $feedback = $std['notes'] ?: ($std['feedback'] ?? '');

                            $ws->getCell([2, $rowIdx])->setValue($stdName);

                            foreach (['1', '2', '3', '4'] as $wKey) {
                                $currRow = $weeksMeta[$wKey]['start_row'] + $i;
                                if ($wKey !== '1') {
                                    $ws->getCell([2, $currRow])->setValue("=B8");
                                }
                                $ws->getCell([3, $currRow])->setValue($feedback);

                                $m1 = $std['m1'] ?? null;
                                $m2 = $std['m2'] ?? null;
                                $m3 = $std['m3'] ?? null;
                                $m4 = $std['m4'] ?? null;

                                $ws->getCell([4, $currRow])->setValue($m1 !== null ? (float)$m1 : null);
                                $ws->getCell([5, $currRow])->setValue($m2 !== null ? (float)$m2 : null);
                                $ws->getCell([6, $currRow])->setValue($m3 !== null ? (float)$m3 : null);
                                $ws->getCell([7, $currRow])->setValue($m4 !== null ? (float)$m4 : null);

                                $fluency = $std['fluency'] ?? null;
                                $grammar = $std['grammar'] ?? null;
                                $pronunciation = $std['pronunciation'] ?? null;
                                $vocabulary = $std['vocabulary'] ?? null;

                                $ws->getCell([9, $currRow])->setValue($fluency !== null ? (float)$fluency : null);
                                $ws->getCell([10, $currRow])->setValue($grammar !== null ? (float)$grammar : null);
                                $ws->getCell([11, $currRow])->setValue($pronunciation !== null ? (float)$pronunciation : null);
                                $ws->getCell([12, $currRow])->setValue($vocabulary !== null ? (float)$vocabulary : null);

                                $ws->getCell([8, $currRow])->setValue("=(D{$currRow}+E{$currRow}+F{$currRow}+G{$currRow})/4");
                                $ws->getCell([13, $currRow])->setValue("=(I{$currRow}+J{$currRow}+K{$currRow}+L{$currRow})/4");
                                $ws->getCell([14, $currRow])->setValue("=((H{$currRow}+M{$currRow})/2)");
                            }
                        }
                    }
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outputFile);

            return $outputFile;
        } catch (\Throwable $e) {
            Log::error('English attendance export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Gagal mengekspor data absensi ke format Excel: ' . $e->getMessage(), 0, $e);
        }

        return $outputFile;
    }
}
