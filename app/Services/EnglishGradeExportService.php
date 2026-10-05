<?php

namespace App\Services;

use App\Models\EnglishClass;
use App\Models\EnglishGrade;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
        $teacherName = $teacher?->name ?? 'Guru Bahasa Inggris';
        $assignedStudentIds = $teacher ? $teacher->getAssignedStudentIds() : [];

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
            $studentsQuery = User::where('role', User::ROLE_STUDENT)
                ->where('subject_id', 1)
                ->where('class_name', $className);

            if (!empty($assignedStudentIds)) {
                $studentsQuery->whereIn('id', $assignedStudentIds);
            }

            $students = $studentsQuery->orderBy('name', 'asc')->get();

            $monthsData = [];
            $monthsToFetch = ($month === 'all') ? [1, 2, 3, 4, 5] : [(int)$month];

            foreach ($monthsToFetch as $m) {
                $gradesQuery = EnglishGrade::where('class_name', $className)->where('week', $m);
                if ($teacher && !$teacher->isSuperAdmin()) {
                    $gradesQuery->where('teacher_id', $teacher->id);
                }
                $grades = $gradesQuery->get()->keyBy('student_id');

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

        // 3. Load template Absensi.xlsx
        $templatePath = base_path('Absensi.xlsx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template Absensi.xlsx tidak ditemukan di {$templatePath}");
        }

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($templatePath);

            // 4. Filter sheet for specific class
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

            $monthTitles = [
                '1' => '1ST MAN',
                '2' => '2ND MAN',
                '3' => '3RD MAN',
                '4' => '4TH MAN',
                '5' => '5TH MAN',
            ];

            $blocksMeta = [
                '1' => ['start_row' => 8, 'banner_cell' => 'A1', 'class_cell' => 'C4', 'teacher_cell' => 'G4', 'period_cell' => 'C5', 'subject_cell' => 'G5'],
                '2' => ['start_row' => 31, 'banner_cell' => 'A24', 'class_cell' => 'C27', 'teacher_cell' => 'G27', 'period_cell' => 'C28', 'subject_cell' => 'G28'],
                '3' => ['start_row' => 54, 'banner_cell' => 'A47', 'class_cell' => 'C50', 'teacher_cell' => 'G50', 'period_cell' => 'C51', 'subject_cell' => 'G51'],
                '4' => ['start_row' => 77, 'banner_cell' => 'A70', 'class_cell' => 'C73', 'teacher_cell' => 'G73', 'period_cell' => 'C74', 'subject_cell' => 'G74'],
                '5' => ['start_row' => 100, 'banner_cell' => 'A93', 'class_cell' => 'C96', 'teacher_cell' => 'G96', 'period_cell' => 'C97', 'subject_cell' => 'G97'],
            ];

            $selectedMonth = strtolower(trim((string)$month));

            $getClassData = function(string $sheetTitle) use ($classesPayload, $teacherName): array {
                $cleanName = trim($sheetTitle);
                if (isset($classesPayload[$cleanName])) {
                    return $classesPayload[$cleanName];
                }
                foreach ($classesPayload as $k => $v) {
                    if (strcasecmp(trim($k), $cleanName) === 0) {
                        return $v;
                    }
                }
                if (count($classesPayload) === 1) {
                    return reset($classesPayload);
                }
                return ['teacher' => $teacherName, 'period' => 'Period', 'months' => []];
            };

            if (in_array($selectedMonth, ['1', '2', '3', '4', '5'], true)) {
                // =========================================================================
                // SINGLE MONTH EXPORT (Month 1, 2, 3, 4, or 5)
                // =========================================================================
                $mTitle = $monthTitles[$selectedMonth] ?? "Bulan Ke-{$selectedMonth}";

                foreach ($spreadsheet->getAllSheets() as $ws) {
                    $sheetName = $ws->getTitle();
                    $cleanClassName = trim($sheetName);
                    $cData = $getClassData($sheetName);
                    $tName = $cData['teacher'] ?? $teacherName;
                    $periodStr = $cData['period'] ?? "Bulan Ke-{$selectedMonth}";

                    $monthsData = $cData['months'] ?? [];
                    $studentsList = $monthsData[$selectedMonth] ?? $monthsData[(int)$selectedMonth] ?? [];

                    // 1. Update Title Banner (A1)
                    $ws->getCell('A1')->setValue($mTitle);

                    // 2. Update Class, Teacher, Period, Subject
                    $ws->getCell('C4')->setValue($cleanClassName);
                    $ws->getCell('G4')->setValue($tName);
                    $ws->getCell('C5')->setValue($periodStr);
                    $ws->getCell('G5')->setValue('English');

                    // 3. Populate student rows 8 to 22
                    for ($i = 0; $i < 15; $i++) {
                        $rowIdx = 8 + $i;
                        $ws->getCell([1, $rowIdx])->setValue($i + 1);

                        if ($i < count($studentsList)) {
                            $std = $studentsList[$i];
                            $ws->getCell([2, $rowIdx])->setValue($std['name'] ?? '');
                            $ws->getCell([3, $rowIdx])->setValue($std['feedback'] ?? '');

                            $m1 = $std['meeting_1'] ?? null;
                            $m2 = $std['meeting_2'] ?? null;
                            $m3 = $std['meeting_3'] ?? null;
                            $m4 = $std['meeting_4'] ?? null;

                            $ws->getCell([4, $rowIdx])->setValue(($m1 !== null && $m1 !== '') ? (float)$m1 : null);
                            $ws->getCell([5, $rowIdx])->setValue(($m2 !== null && $m2 !== '') ? (float)$m2 : null);
                            $ws->getCell([6, $rowIdx])->setValue(($m3 !== null && $m3 !== '') ? (float)$m3 : null);
                            $ws->getCell([7, $rowIdx])->setValue(($m4 !== null && $m4 !== '') ? (float)$m4 : null);

                            $fluency = $std['fluency'] ?? null;
                            $grammar = $std['grammar'] ?? null;
                            $pronunciation = $std['pronunciation'] ?? null;
                            $vocabulary = $std['vocabulary'] ?? null;

                            $ws->getCell([9, $rowIdx])->setValue(($fluency !== null && $fluency !== '') ? (float)$fluency : null);
                            $ws->getCell([10, $rowIdx])->setValue(($grammar !== null && $grammar !== '') ? (float)$grammar : null);
                            $ws->getCell([11, $rowIdx])->setValue(($pronunciation !== null && $pronunciation !== '') ? (float)$pronunciation : null);
                            $ws->getCell([12, $rowIdx])->setValue(($vocabulary !== null && $vocabulary !== '') ? (float)$vocabulary : null);
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

                    // 4. Remove merged cells in rows >= 23
                    foreach ($ws->getMergeCells() as $range) {
                        $coords = Coordinate::splitRange($range);
                        $startRow = (int)preg_replace('/[^0-9]/', '', $coords[0][0]);
                        if ($startRow >= 23) {
                            $ws->unmergeCells($range);
                        }
                    }

                    // 5. Delete rows 23 onward
                    $highestRow = $ws->getHighestRow();
                    if ($highestRow > 22) {
                        $ws->removeRow(23, $highestRow - 22);
                    }
                }
            } else {
                // =========================================================================
                // ALL MONTHS EXPORT (Bulan 1 sampai Bulan 5)
                // =========================================================================
                foreach ($spreadsheet->getAllSheets() as $ws) {
                    $sheetName = $ws->getTitle();
                    $cleanClassName = trim($sheetName);
                    $cData = $getClassData($sheetName);
                    $tName = $cData['teacher'] ?? $teacherName;
                    $monthsData = $cData['months'] ?? [];

                    // 1. Clone Block 4 (rows 70-92) to Block 5 (rows 93-115)
                    $srcStart = 70;
                    $dstStart = 93;
                    $rowsCount = 23;
                    $offset = $dstStart - $srcStart;

                    for ($r = 0; $r < $rowsCount; $r++) {
                        $sRow = $srcStart + $r;
                        $dRow = $dstStart + $r;
                        $ws->getRowDimension($dRow)->setRowHeight($ws->getRowDimension($sRow)->getRowHeight());
                        for ($c = 1; $c <= 14; $c++) {
                            $srcVal = $ws->getCell([$c, $sRow])->getValue();
                            $ws->getCell([$c, $dRow])->setValue($srcVal);
                            $coordDst = Coordinate::stringFromColumnIndex($c) . $dRow;
                            $ws->duplicateStyle($ws->getStyle([$c, $sRow]), $coordDst);
                        }
                    }

                    foreach ($ws->getMergeCells() as $range) {
                        $coords = Coordinate::splitRange($range);
                        $sR = (int)preg_replace('/[^0-9]/', '', $coords[0][0]);
                        $eR = (int)preg_replace('/[^0-9]/', '', $coords[0][1]);
                        $sC = preg_replace('/[0-9]/', '', $coords[0][0]);
                        $eC = preg_replace('/[0-9]/', '', $coords[0][1]);
                        if ($sR >= $srcStart && $sR < ($srcStart + $rowsCount)) {
                            $ws->mergeCells("{$sC}" . ($sR + $offset) . ":{$eC}" . ($eR + $offset));
                        }
                    }

                    $ws->getCell('A93')->setValue('5TH MAN');

                    // 2. Update Header Information for All 5 Blocks
                    foreach (['1', '2', '3', '4', '5'] as $mKey) {
                        $meta = $blocksMeta[$mKey];
                        $ws->getCell($meta['class_cell'])->setValue($cleanClassName);
                        $ws->getCell($meta['teacher_cell'])->setValue($tName);
                        $ws->getCell($meta['subject_cell'])->setValue('English');
                        $ws->getCell($meta['period_cell'])->setValue("Bulan Ke-{$mKey}");
                    }

                    // 3. Master student list
                    $allStudentsMaster = [];
                    foreach (['1', '2', '3', '4', '5'] as $mKey) {
                        $mStds = $monthsData[$mKey] ?? $monthsData[(int)$mKey] ?? [];
                        if (count($mStds) > count($allStudentsMaster)) {
                            $allStudentsMaster = $mStds;
                        }
                    }

                    for ($i = 0; $i < 15; $i++) {
                        $hasStudent = $i < count($allStudentsMaster);
                        $masterName = $hasStudent ? ($allStudentsMaster[$i]['name'] ?? '') : '';

                        foreach (['1', '2', '3', '4', '5'] as $mKey) {
                            $meta = $blocksMeta[$mKey];
                            $currRow = $meta['start_row'] + $i;
                            $mStds = $monthsData[$mKey] ?? $monthsData[(int)$mKey] ?? [];
                            $std = $mStds[$i] ?? ($hasStudent ? $allStudentsMaster[$i] : null);

                            $ws->getCell([1, $currRow])->setValue($i + 1);

                            if ($hasStudent) {
                                if ($mKey === '1') {
                                    $ws->getCell([2, $currRow])->setValue($masterName);
                                } else {
                                    $m1Row = $blocksMeta['1']['start_row'] + $i;
                                    $ws->getCell([2, $currRow])->setValue("=B{$m1Row}");
                                }

                                $feedback = $std['feedback'] ?? '';
                                $ws->getCell([3, $currRow])->setValue($feedback);

                                $m1 = $std['meeting_1'] ?? null;
                                $m2 = $std['meeting_2'] ?? null;
                                $m3 = $std['meeting_3'] ?? null;
                                $m4 = $std['meeting_4'] ?? null;

                                $ws->getCell([4, $currRow])->setValue(($m1 !== null && $m1 !== '') ? (float)$m1 : null);
                                $ws->getCell([5, $currRow])->setValue(($m2 !== null && $m2 !== '') ? (float)$m2 : null);
                                $ws->getCell([6, $currRow])->setValue(($m3 !== null && $m3 !== '') ? (float)$m3 : null);
                                $ws->getCell([7, $currRow])->setValue(($m4 !== null && $m4 !== '') ? (float)$m4 : null);

                                $fluency = $std['fluency'] ?? null;
                                $grammar = $std['grammar'] ?? null;
                                $pronunciation = $std['pronunciation'] ?? null;
                                $vocabulary = $std['vocabulary'] ?? null;

                                $ws->getCell([9, $currRow])->setValue(($fluency !== null && $fluency !== '') ? (float)$fluency : null);
                                $ws->getCell([10, $currRow])->setValue(($grammar !== null && $grammar !== '') ? (float)$grammar : null);
                                $ws->getCell([11, $currRow])->setValue(($pronunciation !== null && $pronunciation !== '') ? (float)$pronunciation : null);
                                $ws->getCell([12, $currRow])->setValue(($vocabulary !== null && $vocabulary !== '') ? (float)$vocabulary : null);
                            } else {
                                $ws->getCell([2, $currRow])->setValue(null);
                                $ws->getCell([3, $currRow])->setValue(null);
                                $ws->getCell([4, $currRow])->setValue(null);
                                $ws->getCell([5, $currRow])->setValue(null);
                                $ws->getCell([6, $currRow])->setValue(null);
                                $ws->getCell([7, $currRow])->setValue(null);
                                $ws->getCell([9, $currRow])->setValue(null);
                                $ws->getCell([10, $currRow])->setValue(null);
                                $ws->getCell([11, $currRow])->setValue(null);
                                $ws->getCell([12, $currRow])->setValue(null);
                            }

                            $ws->getCell([8, $currRow])->setValue("=(D{$currRow}+E{$currRow}+F{$currRow}+G{$currRow})/4");
                            $ws->getCell([13, $currRow])->setValue("=(I{$currRow}+J{$currRow}+K{$currRow}+L{$currRow})/4");
                            $ws->getCell([14, $currRow])->setValue("=((H{$currRow}+M{$currRow})/2)");
                        }
                    }
                }
            }

            // 5. Output file destination
            $exportsDir = storage_path('app/exports');
            if (!is_dir($exportsDir)) {
                mkdir($exportsDir, 0755, true);
            }

            $cleanClass = $classSheet === 'all' ? 'Semua_Kelas' : str_replace(' ', '_', $classSheet);
            $periodTag = $month === 'all' ? 'Bulan_1_sd_5' : "Bulan_{$month}";
            $outputFile = $exportsDir . "/Nilai_English_{$cleanClass}_{$periodTag}_" . uniqid() . ".xlsx";

            $writer = new XlsxWriter($spreadsheet);
            $writer->save($outputFile);

            return $outputFile;
        } catch (\Throwable $e) {
            Log::error('English grades export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Gagal mengekspor data nilai ke format Excel: ' . $e->getMessage(), 0, $e);
        }
    }
}
