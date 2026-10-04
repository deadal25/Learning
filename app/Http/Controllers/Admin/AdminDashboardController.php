<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exercise;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $teacherSubject = $user->subject;
        $classCode = $user->class_code;
        $teacherTodayAttendance = $user->todayAttendance();

        if ($isTeacher && $user->subject_id) {
            // Scope to teacher's single assigned subject
            if ((int)$user->subject_id === 1) {
                // Khusus guru bahasa Inggris: hanya siswa yang diajar oleh guru ini
                $teacherStudentIds = collect($user->getAssignedStudentIds());
            } else {
                $teacherStudentIds = User::where('role', User::ROLE_STUDENT)
                    ->forSubject($user->subject_id)
                    ->pluck('id');
            }

            $totalStudents = $teacherStudentIds->count();
            $hadirCount = Attendance::whereIn('user_id', $teacherStudentIds)
                ->whereDate('date', $today)
                ->where('status', Attendance::STATUS_HADIR)
                ->count();
            $izinKetCount = Attendance::whereIn('user_id', $teacherStudentIds)
                ->whereDate('date', $today)
                ->where('status', Attendance::STATUS_IZIN_KETERANGAN)
                ->count();
            $izinTanpaKetCount = Attendance::whereIn('user_id', $teacherStudentIds)
                ->whereDate('date', $today)
                ->where('status', Attendance::STATUS_IZIN_TANPA_KETERANGAN)
                ->count();

            $materialsCount = (int)$user->subject_id === 1
                ? Material::where('teacher_id', $user->id)->count()
                : Material::whereHas('level', fn($q) => $q->where('subject_id', $user->subject_id))->count();

            $gradesCount = 0;
            $exercisesCount = 0;
            $testsCount = 0;

            if ((int)$user->subject_id === 1) {
                $gradesCount = \App\Models\EnglishGrade::where('teacher_id', $user->id)->count();
            } elseif ((int)$user->subject_id === 2) {
                $testsCount = \App\Models\JapaneseTest::count();
            } elseif ((int)$user->subject_id === 3) {
                $exercisesCount = Exercise::whereHas('level', fn($q) => $q->where('subject_id', 3))->count();
            }

            $stats = [
                'total_students' => $totalStudents,
                'my_students' => $totalStudents,
                'total_classes' => \App\Models\EnglishClass::where('is_active', true)->where('subject_id', $user->subject_id)->count(),
                'total_materials' => $materialsCount,
                'total_grades' => $gradesCount,
                'total_exercises' => $exercisesCount,
                'total_tests' => $testsCount,
            ];

            $recentStudents = User::where('role', User::ROLE_STUDENT)
                ->whereIn('id', $teacherStudentIds)
                ->latest()
                ->take(8)
                ->get();

            $subjects = Subject::where('id', $user->subject_id)
                ->withCount(['levels'])
                ->get();
        } else {
            // Super Admin or unassigned admin: global view
            $totalStudents = User::where('role', User::ROLE_STUDENT)->count();
            $hadirCount = Attendance::whereHas('user', fn($q) => $q->where('role', User::ROLE_STUDENT))
                ->whereDate('date', $today)
                ->where('status', Attendance::STATUS_HADIR)
                ->count();
            $izinKetCount = Attendance::whereHas('user', fn($q) => $q->where('role', User::ROLE_STUDENT))
                ->whereDate('date', $today)
                ->where('status', Attendance::STATUS_IZIN_KETERANGAN)
                ->count();
            $izinTanpaKetCount = Attendance::whereHas('user', fn($q) => $q->where('role', User::ROLE_STUDENT))
                ->whereDate('date', $today)
                ->where('status', Attendance::STATUS_IZIN_TANPA_KETERANGAN)
                ->count();

            $stats = [
                'total_students' => $totalStudents,
                'my_students' => User::where('role', User::ROLE_STUDENT)
                    ->where('created_by', $user->id)
                    ->count(),
                'total_subjects' => Subject::count(),
                'total_materials' => Material::count(),
                'total_exercises' => Exercise::count(),
            ];

            $recentStudents = User::where('role', User::ROLE_STUDENT)
                ->with(['progresses.subject', 'progresses.currentLevel'])
                ->latest()
                ->take(5)
                ->get();

            $subjects = Subject::withCount(['levels', 'progresses'])->get();
        }

        $attendanceStats = [
            'total' => $totalStudents,
            'hadir' => $hadirCount,
            'izin_keterangan' => $izinKetCount,
            'izin_tanpa_keterangan' => $izinTanpaKetCount,
            'belum_absen' => max(0, $totalStudents - ($hadirCount + $izinKetCount + $izinTanpaKetCount)),
            'today_formatted' => Carbon::now()->translatedFormat('l, d F Y'),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentStudents',
            'subjects',
            'attendanceStats',
            'isTeacher',
            'teacherSubject',
            'classCode',
            'teacherTodayAttendance'
        ));
    }
}
