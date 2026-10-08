<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SuperAdminAttendanceExportService
{
    /**
     * Generate an Excel spreadsheet report of attendance for Super Admin.
     *
     * @param Subject $subject
     * @param string $tab 'guru', 'siswa', or 'both'
     * @param array $filters
     * @return string Absolute file path to the generated XLSX file
     */
    public static function generate(Subject $subject, string $tab = 'siswa', array $filters = []): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Super Admin Musashi')
            ->setLastModifiedBy('Super Admin Musashi')
            ->setTitle("Rekap Absensi - {$subject->name}")
            ->setSubject("Rekapitulasi Kehadiran {$subject->name}")
            ->setDescription("Laporan data presensi guru dan siswa pada mata pelajaran {$subject->name}");

        $exportsDir = storage_path('app/exports');
        if (!is_dir($exportsDir)) {
            mkdir($exportsDir, 0755, true);
        }

        if ($tab === 'both' || $tab === 'all') {
            // Sheet 1: Siswa
            $sheetSiswa = $spreadsheet->getActiveSheet();
            $sheetSiswa->setTitle('Absensi Siswa');
            self::buildSheet($sheetSiswa, $subject, 'siswa', $filters);

            // Sheet 2: Guru
            $sheetGuru = $spreadsheet->createSheet();
            $sheetGuru->setTitle('Absensi Guru');
            self::buildSheet($sheetGuru, $subject, 'guru', $filters);

            $spreadsheet->setActiveSheetIndex(0);
        } else {
            $sheet = $spreadsheet->getActiveSheet();
            $title = $tab === 'guru' ? 'Absensi Guru' : 'Absensi Siswa';
            $sheet->setTitle($title);
            self::buildSheet($sheet, $subject, $tab, $filters);
        }

        $cleanSlug = Str::slug($subject->name);
        $timestamp = date('Ymd_His');
        $filename = "Rekap_Absensi_{$cleanSlug}_{$tab}_{$timestamp}.xlsx";
        $filePath = $exportsDir . DIRECTORY_SEPARATOR . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Build and style a single worksheet for either 'siswa' or 'guru'.
     */
    protected static function buildSheet($sheet, Subject $subject, string $roleTab, array $filters): void
    {
        $isStudent = ($roleTab === 'siswa');

        // 1. Fetch relevant user IDs
        if ($isStudent) {
            $userIds = User::where('role', User::ROLE_STUDENT)
                ->forSubject($subject->id)
                ->pluck('id');
        } else {
            $userIds = User::where('role', User::ROLE_ADMIN)
                ->where('subject_id', $subject->id)
                ->pluck('id');
        }

        // 2. Query attendance
        $query = Attendance::whereIn('user_id', $userIds)->with('user');

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['month'])) {
            $query->whereYear('date', substr($filters['month'], 0, 4))
                  ->whereMonth('date', substr($filters['month'], 5, 2));
        }

        if ($isStudent && !empty($filters['class_name']) && $filters['class_name'] !== 'all') {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('class_name', $filters['class_name']);
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        // Stats calculation
        $totalRecords = $attendances->count();
        $totalHadir = $attendances->where('status', Attendance::STATUS_HADIR)->count();
        $totalIzinKet = $attendances->where('status', Attendance::STATUS_IZIN_KETERANGAN)->count();
        $totalIzinTanpa = $attendances->where('status', Attendance::STATUS_IZIN_TANPA_KETERANGAN)->count();
        $rate = $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 100;

        // Determine column list
        // Columns for Student:
        // A: No, B: Tanggal, C: Hari, D: Jam Check-in, E: Nama Siswa, F: NRP, G: Email, H: Kelas, I: Divisi, J: Status, K: Keterangan
        // Columns for Teacher:
        // A: No, B: Tanggal, C: Hari, D: Jam Check-in, E: Nama Guru, F: Email, G: No. Telepon, H: Status, I: Keterangan
        $lastCol = $isStudent ? 'K' : 'I';

        // 3. Write Document Header Banner
        $sheet->setCellValue('A1', 'MUSASHI LEARNING SYSTEM - LAPORAN REKAPITULASI PRESENSI');
        $sheet->setCellValue('A2', "MATA PELAJARAN: " . strtoupper($subject->name) . " (" . ($isStudent ? 'ABSENSI SISWA' : 'ABSENSI GURU') . ")");

        $filterText = [];
        if (!empty($filters['date'])) {
            $filterText[] = 'Tanggal: ' . Carbon::parse($filters['date'])->translatedFormat('d F Y');
        }
        if (!empty($filters['month'])) {
            $filterText[] = 'Bulan: ' . Carbon::parse($filters['month'] . '-01')->translatedFormat('F Y');
        }
        if ($isStudent && !empty($filters['class_name']) && $filters['class_name'] !== 'all') {
            $filterText[] = 'Kelas: ' . $filters['class_name'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $filterText[] = 'Status: ' . ucfirst(str_replace('_', ' ', $filters['status']));
        }
        if (!empty($filters['search'])) {
            $filterText[] = 'Pencarian: "' . $filters['search'] . '"';
        }
        $filterSummary = empty($filterText) ? 'Semua Data' : implode(' | ', $filterText);

        $sheet->setCellValue('A3', "Filter: {$filterSummary}  •  Tanggal Ekspor: " . Carbon::now()->translatedFormat('d F Y, H:i') . " WIB");

        // Merge title rows
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A3:{$lastCol}3");

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle("A1:{$lastCol}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 4. Summary KPI mini-table
        $sheet->setCellValue('A5', 'Ringkasan:');
        $sheet->setCellValue('B5', "Total Presensi: {$totalRecords}");
        $sheet->setCellValue('C5', "Hadir: {$totalHadir}");
        $sheet->setCellValue('D5', "Izin (Keterangan): {$totalIzinKet}");
        $sheet->setCellValue('E5', "Izin (Tanpa Ket): {$totalIzinTanpa}");
        $sheet->setCellValue('F5', "Tingkat Kehadiran: {$rate}%");
        $sheet->getStyle('A5:F5')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // 5. Table Header Row (Row 7)
        $headerRow = 7;
        if ($isStudent) {
            $headers = [
                'A' => 'No',
                'B' => 'Tanggal',
                'C' => 'Hari',
                'D' => 'Jam Check-in',
                'E' => 'Nama Siswa',
                'F' => 'NRP',
                'G' => 'Email',
                'H' => 'Kelas / Grup',
                'I' => 'Divisi',
                'J' => 'Status Presensi',
                'K' => 'Keterangan / Alasan',
            ];
        } else {
            $headers = [
                'A' => 'No',
                'B' => 'Tanggal',
                'C' => 'Hari',
                'D' => 'Jam Check-in',
                'E' => 'Nama Guru',
                'F' => 'Email',
                'G' => 'No. Telepon',
                'H' => 'Status Presensi',
                'I' => 'Keterangan / Alasan',
            ];
        }

        foreach ($headers as $col => $heading) {
            $sheet->setCellValue("{$col}{$headerRow}", $heading);
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // 6. Data Rows
        $currentRow = $headerRow + 1;
        $no = 1;

        foreach ($attendances as $att) {
            $user = $att->user;
            $dateCarbon = Carbon::parse($att->date);
            $dayName = $dateCarbon->translatedFormat('l');
            $dateFormatted = $dateCarbon->format('d/m/Y');
            $timeFormatted = $att->check_in_time ? substr($att->check_in_time, 0, 5) . ' WIB' : '-';

            $statusText = match ($att->status) {
                Attendance::STATUS_HADIR => 'Hadir',
                Attendance::STATUS_IZIN_KETERANGAN => 'Izin (Keterangan)',
                Attendance::STATUS_IZIN_TANPA_KETERANGAN => 'Izin (Tanpa Keterangan)',
                default => ucfirst(str_replace('_', ' ', $att->status ?? '-')),
            };

            $sheet->setCellValue("A{$currentRow}", $no);
            $sheet->setCellValue("B{$currentRow}", $dateFormatted);
            $sheet->setCellValue("C{$currentRow}", $dayName);
            $sheet->setCellValue("D{$currentRow}", $timeFormatted);

            if ($isStudent) {
                $sheet->setCellValue("E{$currentRow}", $user->name ?? 'Tidak Diketahui');
                $sheet->setCellValue("F{$currentRow}", $user->nrp ?? '-');
                $sheet->setCellValue("G{$currentRow}", $user->email ?? '-');
                $sheet->setCellValue("H{$currentRow}", $user->class_name ?? '-');
                $sheet->setCellValue("I{$currentRow}", $user->division ?? '-');
                $sheet->setCellValue("J{$currentRow}", $statusText);
                $sheet->setCellValue("K{$currentRow}", $att->notes ?? '-');
            } else {
                $sheet->setCellValue("E{$currentRow}", $user->name ?? 'Tidak Diketahui');
                $sheet->setCellValue("F{$currentRow}", $user->email ?? '-');
                $sheet->setCellValue("G{$currentRow}", $user->phone ?? '-');
                $sheet->setCellValue("H{$currentRow}", $statusText);
                $sheet->setCellValue("I{$currentRow}", $att->notes ?? '-');
            }

            // Alignments
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Status styling
            $statusCol = $isStudent ? 'J' : 'H';
            $sheet->getStyle("{$statusCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($att->status === Attendance::STATUS_HADIR) {
                $sheet->getStyle("{$statusCol}{$currentRow}")->getFont()->setBold(true)->getColor()->setARGB('FF047857');
            } elseif ($att->status === Attendance::STATUS_IZIN_KETERANGAN) {
                $sheet->getStyle("{$statusCol}{$currentRow}")->getFont()->setBold(true)->getColor()->setARGB('FF1D4ED8');
            } else {
                $sheet->getStyle("{$statusCol}{$currentRow}")->getFont()->setBold(true)->getColor()->setARGB('FFB91C1C');
            }

            $currentRow++;
            $no++;
        }

        $lastDataRow = max($headerRow + 1, $currentRow - 1);

        // Apply borders to table
        if ($totalRecords > 0) {
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFE2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        } else {
            $sheet->setCellValue("A{$currentRow}", 'Tidak ada data presensi yang sesuai.');
            $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        }

        // Auto-fit column widths
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}
