<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EnglishClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            ->get();
        $teacherIds = $teachers->pluck('id');

        // 2. Students for this subject
        $students = User::where('role', User::ROLE_STUDENT)
            ->where(function ($q) use ($currentSubject) {
                $q->where('subject_id', $currentSubject->id)
                  ->orWhereIn('class_name', function ($sub) use ($currentSubject) {
                      $sub->select('name')->from('english_classes')->where('subject_id', $currentSubject->id);
                  })
                  ->orWhereHas('enrollments', function ($sub) use ($currentSubject) {
                      $sub->where('subject_id', $currentSubject->id);
                  });
            })
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
}
