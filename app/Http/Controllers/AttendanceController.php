<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Submit today's attendance for the logged-in user.
     */
    public function submit(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,izin_keterangan,izin_tanpa_keterangan'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'Silakan pilih status presensi Anda.',
            'status.in' => 'Pilihan status presensi tidak valid.',
            'notes.max' => 'Keterangan izin maksimal 500 karakter.',
        ]);

        if ($validated['status'] === Attendance::STATUS_IZIN_KETERANGAN && empty(trim($validated['notes'] ?? ''))) {
            return back()->with('error', 'Harap isi alasan / keterangan jika memilih Izin dengan Keterangan.');
        }

        // Check if user already attended today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $timeNow = now()->format('H:i:s');

        if ($attendance) {
            $attendance->update([
                'status' => $validated['status'],
                'notes' => $validated['status'] === Attendance::STATUS_IZIN_KETERANGAN ? $validated['notes'] : null,
                'check_in_time' => $timeNow,
            ]);
            $msg = 'Presensi kehadiran hari ini berhasil diperbarui.';
        } else {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'status' => $validated['status'],
                'notes' => $validated['status'] === Attendance::STATUS_IZIN_KETERANGAN ? $validated['notes'] : null,
                'check_in_time' => $timeNow,
            ]);
            $msg = 'Presensi kehadiran hari ini berhasil dicatat. Terima kasih!';
        }

        return back()->with('success', $msg);
    }

    /**
     * Display student attendance history.
     */
    public function history(Request $request): View
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $todayAttendance = $user->todayAttendance();

        $query = Attendance::where('user_id', $user->id)->orderBy('date', 'desc');

        if ($month = $request->input('month')) {
            $query->whereMonth('date', Carbon::parse($month)->month)
                  ->whereYear('date', Carbon::parse($month)->year);
        }

        $attendances = $query->paginate(15)->withQueryString();

        $totalHadir = Attendance::where('user_id', $user->id)->where('status', Attendance::STATUS_HADIR)->count();
        $totalIzinKet = Attendance::where('user_id', $user->id)->where('status', Attendance::STATUS_IZIN_KETERANGAN)->count();
        $totalIzinTanpa = Attendance::where('user_id', $user->id)->where('status', Attendance::STATUS_IZIN_TANPA_KETERANGAN)->count();
        $totalDays = $totalHadir + $totalIzinKet + $totalIzinTanpa;

        $summary = [
            'total_hadir' => $totalHadir,
            'total_izin_keterangan' => $totalIzinKet,
            'total_izin_tanpa_keterangan' => $totalIzinTanpa,
            'total_days' => $totalDays,
            'percentage' => $totalDays > 0 ? round(($totalHadir / $totalDays) * 100, 1) : 100,
        ];

        return view('student.attendance.index', compact('user', 'todayAttendance', 'attendances', 'summary'));
    }

    /**
     * Admin portal: view all students' attendance, filter by class and date, search, stats.
     */
    public function adminIndex(Request $request): View
    {
        $currentUser = Auth::user();
        $isTeacher = $currentUser->isAdmin() && !$currentUser->isSuperAdmin();
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedClass = $request->input('class_name');
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        $classesQuery = \App\Models\EnglishClass::where('is_active', true)->orderBy('sort_order');
        if ($isTeacher && $currentUser->subject_id) {
            $classesQuery->where('subject_id', $currentUser->subject_id);
        }
        $classes = $classesQuery->get();

        // Filter students query
        $studentsQuery = User::where('role', User::ROLE_STUDENT)->orderBy('name', 'asc');

        if ($isTeacher && $currentUser->subject_id) {
            $studentsQuery->forSubject($currentUser->subject_id);
            // Khusus guru bahasa Inggris: hanya siswa yang diajar oleh guru ini
            if ((int)$currentUser->subject_id === 1) {
                $assignedIds = $currentUser->getAssignedStudentIds();
                $studentsQuery->whereIn('id', $assignedIds);
            }
        }

        if (!empty($selectedClass) && $selectedClass !== 'all') {
            $studentsQuery->where('class_name', $selectedClass);
        }

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->get();

        // Get attendances for selected date
        $attendances = Attendance::with('user')
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('user_id');

        // Teacher's attendance for this date
        $teacherDateAttendance = Attendance::where('user_id', $currentUser->id)
            ->whereDate('date', $selectedDate)
            ->first();

        $stats = [
            'total_students' => $students->count(),
            'hadir' => $students->filter(fn($s) => isset($attendances[$s->id]) && $attendances[$s->id]->status === Attendance::STATUS_HADIR)->count(),
            'izin_keterangan' => $students->filter(fn($s) => isset($attendances[$s->id]) && $attendances[$s->id]->status === Attendance::STATUS_IZIN_KETERANGAN)->count(),
            'izin_tanpa_keterangan' => $students->filter(fn($s) => isset($attendances[$s->id]) && $attendances[$s->id]->status === Attendance::STATUS_IZIN_TANPA_KETERANGAN)->count(),
        ];
        $stats['belum_absen'] = max(0, $stats['total_students'] - ($stats['hadir'] + $stats['izin_keterangan'] + $stats['izin_tanpa_keterangan']));

        $teacherSubject = $currentUser->subject;
        $isEnglishTeacher = ($isTeacher && $teacherSubject && (str_contains(strtolower($teacherSubject->name), 'inggris') || str_contains(strtolower($teacherSubject->slug ?? ''), 'inggris')));

        return view('admin.attendance.index', compact(
            'students',
            'attendances',
            'selectedDate',
            'selectedClass',
            'classes',
            'statusFilter',
            'search',
            'stats',
            'isTeacher',
            'teacherSubject',
            'teacherDateAttendance',
            'isEnglishTeacher'
        ));
    }

    /**
     * Admin manually marks or modifies a student's attendance.
     */
    public function adminStore(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        $isTeacher = $currentUser->isAdmin() && !$currentUser->isSuperAdmin();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin_keterangan,izin_tanpa_keterangan'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($isTeacher && (int)$currentUser->subject_id === 1) {
            $assignedIds = $currentUser->getAssignedStudentIds();
            abort_unless(in_array((int)$validated['user_id'], $assignedIds), 403, 'Anda tidak berhak mengubah absensi siswa guru lain.');
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'date' => $validated['date'],
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['status'] === Attendance::STATUS_IZIN_KETERANGAN ? $validated['notes'] : null,
                'check_in_time' => now()->format('H:i:s'),
            ]
        );

        return back()->with('success', 'Data presensi siswa berhasil disimpan.');
    }

    /**
     * Export attendance in exact Absensi.xlsx format for English Teacher.
     */
    public function exportEnglish(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $currentUser = Auth::user();
        $isTeacher = $currentUser->isAdmin() && !$currentUser->isSuperAdmin();
        $isEnglishTeacher = ($isTeacher && $currentUser->subject && (str_contains(strtolower($currentUser->subject->name), 'inggris') || str_contains(strtolower($currentUser->subject->slug ?? ''), 'inggris')));

        if (!$isEnglishTeacher && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Fitur unduh format rekap Absensi.xlsx ini dikhususkan untuk Guru Bahasa Inggris.');
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'week' => ['nullable', 'in:all,1,2,3,4'],
            'class_sheet' => ['nullable', 'string', 'max:50'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $week = $validated['week'] ?? 'all';
        $classSheet = $validated['class_sheet'] ?? 'all';

        try {
            $filePath = \App\Services\EnglishAttendanceExportService::generate($date, $week, $classSheet, $currentUser);
            $downloadName = "Absensi_Bahasa_Inggris_" . str_replace('-', '', $date) . ".xlsx";
            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor file absensi: ' . $e->getMessage());
        }
    }
}

