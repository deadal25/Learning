<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EnglishClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\SuperAdminAttendanceExportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SuperAdminAttendanceController extends Controller
{
    /**
     * Display combined attendance history for Super Admin divided by subject and role (Guru & Siswa).
     */
    public function index(Request $request): View
    {
        $allSubjects = Subject::orderBy('order', 'asc')->get();
        $selectedSubjectId = (int)$request->input('subject_id', 1);
        $currentSubject = Subject::find($selectedSubjectId) ?? $allSubjects->first();

        $activeTab = $request->input('tab', 'guru'); // 'guru' or 'siswa'
        $selectedDate = $request->input('date');
        $selectedMonth = $request->input('month');
        $selectedClass = $request->input('class_name');
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        // Classes for current subject
        $classes = EnglishClass::where('subject_id', $currentSubject->id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 1. Teachers for this subject
        $teachers = User::where('role', User::ROLE_ADMIN)
            ->where('subject_id', $currentSubject->id)
            ->orderBy('name', 'asc')
            ->get();
        $teacherIds = $teachers->pluck('id');

        // 2. Students for this subject (using scopeForSubject to cover all enrollments & classes)
        $students = User::where('role', User::ROLE_STUDENT)
            ->forSubject($currentSubject->id)
            ->orderBy('name', 'asc')
            ->get();
        $studentIds = $students->pluck('id');

        // Base attendance query according to active tab
        if ($activeTab === 'siswa') {
            $query = Attendance::whereIn('user_id', $studentIds)
                ->with('user');

            if ($selectedClass && $selectedClass !== 'all') {
                $query->whereHas('user', function ($q) use ($selectedClass) {
                    $q->where('class_name', $selectedClass);
                });
            }
        } else {
            // 'guru' tab
            $query = Attendance::whereIn('user_id', $teacherIds)
                ->with('user');
        }

        // Apply shared filters
        if ($selectedDate) {
            $query->whereDate('date', $selectedDate);
        }

        if ($selectedMonth) {
            $query->whereYear('date', substr($selectedMonth, 0, 4))
                  ->whereMonth('date', substr($selectedMonth, 5, 2));
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calculate statistics for the active view
        $targetUserIds = ($activeTab === 'siswa') ? $studentIds : $teacherIds;
        $totalHadir = Attendance::whereIn('user_id', $targetUserIds)->where('status', Attendance::STATUS_HADIR)->count();
        $totalIzinKet = Attendance::whereIn('user_id', $targetUserIds)->where('status', Attendance::STATUS_IZIN_KETERANGAN)->count();
        $totalIzinTanpa = Attendance::whereIn('user_id', $targetUserIds)->where('status', Attendance::STATUS_IZIN_TANPA_KETERANGAN)->count();
        $totalRecords = $totalHadir + $totalIzinKet + $totalIzinTanpa;
        $attendanceRate = $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 100;

        $stats = [
            'total_users' => ($activeTab === 'siswa') ? $students->count() : $teachers->count(),
            'total_records' => $totalRecords,
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzinKet + $totalIzinTanpa,
            'rate' => $attendanceRate,
        ];

        return view('superadmin.attendance.index', compact(
            'allSubjects',
            'currentSubject',
            'activeTab',
            'teachers',
            'students',
            'classes',
            'attendances',
            'stats',
            'selectedDate',
            'selectedMonth',
            'selectedClass',
            'statusFilter',
            'search'
        ));
    }

    /**
     * Store or manually record attendance for a user (Teacher or Student).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin_keterangan,izin_tanpa_keterangan,belum_absen'],
            'check_in_time' => ['nullable', 'string', 'max:8'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'user_id.required' => 'Pengguna wajib dipilih.',
            'date.required' => 'Tanggal presensi wajib diisi.',
            'status.required' => 'Status presensi wajib dipilih.',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $date = $validated['date'];

        // If status is 'belum_absen', reset / remove attendance record
        if ($validated['status'] === 'belum_absen') {
            Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->delete();

            return back()->with('success', "Status presensi {$user->name} untuk tanggal " . Carbon::parse($date)->format('d/m/Y') . " berhasil diatur menjadi Belum Absen.");
        }

        $checkInTime = !empty($validated['check_in_time'])
            ? $validated['check_in_time']
            : now()->format('H:i:s');

        // Ensure seconds format if only HH:mm given
        if (strlen($checkInTime) === 5) {
            $checkInTime .= ':00';
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $date,
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['status'] === Attendance::STATUS_IZIN_KETERANGAN ? $validated['notes'] : ($validated['status'] === Attendance::STATUS_HADIR ? null : $validated['notes']),
                'check_in_time' => $checkInTime,
            ]
        );

        return back()->with('success', "Presensi untuk {$user->name} berhasil dicatat.");
    }

    /**
     * Update an existing attendance record.
     */
    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:hadir,izin_keterangan,izin_tanpa_keterangan,belum_absen'],
            'date' => ['nullable', 'date'],
            'check_in_time' => ['nullable', 'string', 'max:8'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userName = $attendance->user->name ?? 'Pengguna';

        // If Super Admin chooses "belum_absen", delete the attendance record
        if ($validated['status'] === 'belum_absen') {
            $attendance->delete();

            return back()->with('success', "Presensi untuk {$userName} berhasil diubah menjadi Belum Absen (catatan presensi dihapus).");
        }

        $checkInTime = !empty($validated['check_in_time'])
            ? $validated['check_in_time']
            : ($attendance->check_in_time ?: now()->format('H:i:s'));

        if (strlen($checkInTime) === 5) {
            $checkInTime .= ':00';
        }

        $attendance->update([
            'status' => $validated['status'],
            'notes' => $validated['status'] === Attendance::STATUS_IZIN_KETERANGAN ? $validated['notes'] : ($validated['status'] === Attendance::STATUS_HADIR ? null : $validated['notes']),
            'date' => $validated['date'] ?? $attendance->date,
            'check_in_time' => $checkInTime,
        ]);

        return back()->with('success', "Data presensi untuk {$userName} berhasil diperbarui.");
    }

    /**
     * Delete an individual attendance record.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $userName = $attendance->user->name ?? 'Pengguna';
        $dateFormatted = Carbon::parse($attendance->date)->format('d/m/Y');

        $attendance->delete();

        return back()->with('success', "Data presensi {$userName} pada {$dateFormatted} berhasil dihapus (status kembali Belum Absen).");
    }

    /**
     * Bulk delete selected attendance records.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:attendances,id'],
        ], [
            'ids.required' => 'Pilih minimal satu data presensi untuk dihapus.',
            'ids.min' => 'Pilih minimal satu data presensi untuk dihapus.',
        ]);

        $count = Attendance::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "Berhasil menghapus {$count} data presensi terpilih (status dikembalikan ke Belum Absen).");
    }

    /**
     * Reset / delete all attendances with flexible scopes.
     */
    public function resetAll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'scope' => ['required', 'in:filtered,tab,subject'],
            'tab' => ['required', 'in:guru,siswa,all'],
            'confirm_text' => ['required', 'in:RESET,reset'],
        ], [
            'confirm_text.in' => 'Konfirmasi kata RESET tidak cocok. Tindakan dibatalkan.',
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        $scope = $validated['scope'];
        $tab = $validated['tab'];

        // Get user IDs
        $teacherIds = User::where('role', User::ROLE_ADMIN)
            ->where('subject_id', $subject->id)
            ->pluck('id');

        $studentIds = User::where('role', User::ROLE_STUDENT)
            ->forSubject($subject->id)
            ->pluck('id');

        if ($scope === 'subject') {
            // Reset ALL attendance records for this subject (both teachers & students)
            $allIds = $teacherIds->merge($studentIds);
            $deletedCount = Attendance::whereIn('user_id', $allIds)->delete();

            return back()->with('success', "Berhasil mereset seluruh {$deletedCount} data absensi mata pelajaran {$subject->name} (Guru & Siswa).");
        }

        if ($scope === 'tab') {
            // Reset all records for current tab (either guru or siswa)
            $targetIds = ($tab === 'siswa') ? $studentIds : $teacherIds;
            $deletedCount = Attendance::whereIn('user_id', $targetIds)->delete();
            $roleLabel = ($tab === 'siswa') ? 'Siswa' : 'Guru';

            return back()->with('success', "Berhasil mereset seluruh {$deletedCount} data absensi {$roleLabel} pada mata pelajaran {$subject->name}.");
        }

        // scope === 'filtered' -> Reset only records matching current view filters
        $targetIds = ($tab === 'siswa') ? $studentIds : $teacherIds;
        $query = Attendance::whereIn('user_id', $targetIds);

        if ($tab === 'siswa' && $request->filled('class_name') && $request->input('class_name') !== 'all') {
            $className = $request->input('class_name');
            $query->whereHas('user', fn($q) => $q->where('class_name', $className));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('month')) {
            $month = $request->input('month');
            $query->whereYear('date', substr($month, 0, 4))
                  ->whereMonth('date', substr($month, 5, 2));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%");
            });
        }

        $deletedCount = $query->delete();

        return back()->with('success', "Berhasil menghapus {$deletedCount} data absensi yang sesuai dengan filter aktif saat ini.");
    }

    /**
     * Export attendance data to Excel for the chosen subject and filters.
     */
    public function export(Request $request): BinaryFileResponse|RedirectResponse
    {
        $subjectId = (int)$request->input('subject_id', 1);
        $subject = Subject::findOrFail($subjectId);
        $tab = $request->input('tab', 'siswa'); // 'siswa', 'guru', or 'both'

        $filters = [
            'date' => $request->input('date'),
            'month' => $request->input('month'),
            'class_name' => $request->input('class_name'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        try {
            $filePath = SuperAdminAttendanceExportService::generate($subject, $tab, $filters);
            $downloadName = basename($filePath);

            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor file absensi: ' . $e->getMessage());
        }
    }
}
