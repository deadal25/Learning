<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Get subjects that the student has enrolled in via class code
        $enrolledSubjectIds = $user->enrollments()->pluck('subject_id')->toArray();

        // Load enrolled subjects with levels
        $subjects = Subject::with(['levels' => function ($q) {
            $q->orderBy('order', 'asc');
        }])->whereIn('id', $enrolledSubjectIds)->get();

        foreach ($subjects as $subject) {
            $firstLevel = $subject->levels->first();
            if ($firstLevel) {
                UserLevelStatus::firstOrCreate([
                    'user_id' => $user->id,
                    'level_id' => $firstLevel->id,
                ], [
                    'points' => 0,
                    'is_unlocked' => true,
                    'is_completed' => false,
                ]);

                UserProgress::firstOrCreate([
                    'user_id' => $user->id,
                    'subject_id' => $subject->id,
                ], [
                    'current_level_id' => $firstLevel->id,
                    'current_points' => 0,
                    'is_completed' => false,
                ]);
            }
        }

        // Load refreshed progress for enrolled subjects
        $progresses = UserProgress::where('user_id', $user->id)
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->with([
                'subject.levels',
                'currentLevel.materials' => fn($mq) => $mq->where('is_active', true),
                'currentLevel.exercises'
            ])
            ->get();

        $unlockedLevelIds = UserLevelStatus::where('user_id', $user->id)
            ->where('is_unlocked', true)
            ->pluck('level_id')
            ->toArray();

        $completedLevelIds = UserLevelStatus::where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('level_id')
            ->toArray();

        $levelScores = UserLevelStatus::where('user_id', $user->id)
            ->pluck('points', 'level_id')
            ->toArray();

        $stats = [
            'total_subjects' => $subjects->count(),
            'completed_levels' => count($completedLevelIds),
            'total_points_earned' => array_sum($levelScores),
        ];

        // Today's attendance for the logged in student
        $todayAttendance = $user->todayAttendance();
        $todayFormatted = Carbon::now()->translatedFormat('l, d F Y');

        $activeSubjectId = session('active_subject_id') ?? $user->subject_id ?? 1;

        // Recent materials scoped strictly to active subject (Inggris, Jepang, Matematika)
        $materialsQuery = Material::query()->where('is_active', true)->with(['level.subject']);
        $materialsQuery->whereHas('level', fn($lq) => $lq->where('subject_id', $activeSubjectId));

        if (!empty($user->class_name)) {
            $materialsQuery->forStudentClass($user->class_name);
        }

        $recentMaterials = $materialsQuery->latest()->take(6)->get();

        // Compute personal attendance statistics (Jumlah kehadiran, persentase, dan realtime)
        $userAttendances = \App\Models\Attendance::where('user_id', $user->id)->get();
        $totalHadir = $userAttendances->where('status', \App\Models\Attendance::STATUS_HADIR)->count();
        $totalIzinKet = $userAttendances->where('status', \App\Models\Attendance::STATUS_IZIN_KETERANGAN)->count();
        $totalIzinTanpa = $userAttendances->where('status', \App\Models\Attendance::STATUS_IZIN_TANPA_KETERANGAN)->count();
        $totalDays = $userAttendances->count();
        $attendancePercentage = $totalDays > 0 ? round(($totalHadir / $totalDays) * 100, 1) : 100;

        $studentAttendanceStats = [
            'total_hadir' => $totalHadir,
            'total_izin_ket' => $totalIzinKet,
            'total_izin_tanpa' => $totalIzinTanpa,
            'total_days' => $totalDays,
            'percentage' => $attendancePercentage,
        ];

        $totalAvailableSubjects = Subject::where('is_active', true)->count();

        return view('student.dashboard', compact(
            'subjects',
            'progresses',
            'unlockedLevelIds',
            'completedLevelIds',
            'levelScores',
            'stats',
            'todayAttendance',
            'todayFormatted',
            'recentMaterials',
            'studentAttendanceStats',
            'totalAvailableSubjects',
            'activeSubjectId'
        ));
    }
}
