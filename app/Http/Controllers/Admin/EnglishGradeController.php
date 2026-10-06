<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnglishClass;
use App\Models\EnglishGrade;
use App\Models\ExerciseAttempt;
use App\Models\Level;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserLevelStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnglishGradeController extends Controller
{
    /**
     * Teacher portal: Manage grades and feedback per class and month.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && (int)$currentUser->subject_id !== 1) {
            abort(403, 'Akses nilai & feedback khusus untuk Guru Bahasa Inggris.');
        }

        $classes = EnglishClass::where('is_active', true)
            ->where('subject_id', 1)
            ->orderBy('sort_order')
            ->get();

        // Default to first English class if not selected, or if selected class is not an English class
        $validClassNames = $classes->pluck('name')->toArray();
        $requestedClass = $request->input('class_name');
        $selectedClass = ($requestedClass && in_array($requestedClass, $validClassNames))
            ? $requestedClass
            : ($classes->first()?->name ?? 'Class B1');

        $selectedMonth = (int) $request->input('month', $request->input('week', 1));
        if ($selectedMonth < 1 || $selectedMonth > 5) {
            $selectedMonth = 1;
        }
        $selectedWeek = $selectedMonth; // Backwards compatibility for existing views/tests

        // Fetch students in this class enrolled in English under this teacher
        $assignedStudentIds = $currentUser->getAssignedStudentIds();
        $students = User::where('role', User::ROLE_STUDENT)
            ->where('subject_id', 1)
            ->where('class_name', $selectedClass)
            ->whereIn('id', $assignedStudentIds)
            ->orderBy('name', 'asc')
            ->get();

        // Fetch existing grades for this class, month, and teacher
        $existingGrades = EnglishGrade::where('class_name', $selectedClass)
            ->where('week', $selectedMonth)
            ->where('teacher_id', $currentUser->id)
            ->get()
            ->keyBy('student_id');

        // Make sure grade entries exist for all students in this class
        $gradesList = [];
        foreach ($students as $student) {
            if ($existingGrades->has($student->id)) {
                $gradesList[$student->id] = $existingGrades->get($student->id);
            } else {
                // Instantiate in-memory or create
                $grade = EnglishGrade::firstOrCreate([
                    'student_id' => $student->id,
                    'class_name' => $selectedClass,
                    'week' => $selectedMonth,
                ], [
                    'teacher_id' => $currentUser->id,
                    'meeting_1' => 0,
                    'meeting_2' => 0,
                    'meeting_3' => 0,
                    'meeting_4' => 0,
                    'attendance_score' => 0,
                    'fluency' => 0,
                    'grammar' => 0,
                    'pronunciation' => 0,
                    'vocabulary' => 0,
                    'total_exam' => 0,
                    'final_score' => 0,
                    'feedback' => null,
                ]);
                $gradesList[$student->id] = $grade;
            }
        }

        // Summary stats
        $totalEvaluated = collect($gradesList)->filter(fn($g) => $g->final_score > 0 || !empty($g->feedback))->count();
        $averageFinalScore = count($gradesList) > 0 ? round(collect($gradesList)->avg('final_score'), 1) : 0;

        return view('admin.grades.index', compact(
            'classes',
            'selectedClass',
            'selectedMonth',
            'selectedWeek',
            'students',
            'gradesList',
            'totalEvaluated',
            'averageFinalScore'
        ));
    }

    /**
     * Bulk save grades and feedback for students in selected class and month.
     */
    public function save(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && (int)$currentUser->subject_id !== 1) {
            abort(403, 'Akses simpan nilai khusus untuk Guru Bahasa Inggris.');
        }

        $className = $request->input('class_name');
        $month = (int) $request->input('month', $request->input('week', 1));
        if ($month < 1 || $month > 5) {
            $month = 1;
        }
        $gradesData = $request->input('grades', []);

        if (empty($gradesData) || !is_array($gradesData)) {
            return back()->with('info', 'Tidak ada data nilai yang dikirim.');
        }

        $assignedStudentIds = $currentUser->getAssignedStudentIds();

        foreach ($gradesData as $studentId => $data) {
            if (!in_array((int)$studentId, $assignedStudentIds)) {
                continue; // Isolasi: jangan ubah nilai siswa milik guru lain
            }
            $m1 = (float) ($data['meeting_1'] ?? 0);
            $m2 = (float) ($data['meeting_2'] ?? 0);
            $m3 = (float) ($data['meeting_3'] ?? 0);
            $m4 = (float) ($data['meeting_4'] ?? 0);

            // Auto calculate attendance score if not explicitly given
            $attendance = isset($data['attendance_score']) && $data['attendance_score'] !== '' 
                ? (float) $data['attendance_score'] 
                : round(($m1 + $m2 + $m3 + $m4) / 4, 2);

            $fluency = (float) ($data['fluency'] ?? 0);
            $grammar = (float) ($data['grammar'] ?? 0);
            $pronunciation = (float) ($data['pronunciation'] ?? 0);
            $vocabulary = (float) ($data['vocabulary'] ?? 0);

            $totalExam = isset($data['total_exam']) && $data['total_exam'] !== ''
                ? (float) $data['total_exam']
                : round(($fluency + $grammar + $pronunciation + $vocabulary) / 4, 2);

            $finalScore = isset($data['final_score']) && $data['final_score'] !== ''
                ? (float) $data['final_score']
                : round(($attendance + $totalExam) / 2, 2);

            $feedback = !empty(trim($data['feedback'] ?? '')) ? trim($data['feedback']) : null;

            EnglishGrade::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_name' => $className,
                    'week' => $month,
                ],
                [
                    'teacher_id' => $currentUser->id,
                    'meeting_1' => $m1,
                    'meeting_2' => $m2,
                    'meeting_3' => $m3,
                    'meeting_4' => $m4,
                    'attendance_score' => $attendance,
                    'fluency' => $fluency,
                    'grammar' => $grammar,
                    'pronunciation' => $pronunciation,
                    'vocabulary' => $vocabulary,
                    'total_exam' => $totalExam,
                    'final_score' => $finalScore,
                    'feedback' => $feedback,
                ]
            );
        }

        return back()->with('success', "🎉 Seluruh nilai & feedback untuk {$className} (Bulan ke-{$month}) berhasil disimpan!");
    }

    /**
     * Export grades and feedback in exact Absensi.xlsx format for English Teacher.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $currentUser = Auth::user();
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && (int)$currentUser->subject_id !== 1) {
            abort(403, 'Fitur unduh nilai Excel ini dikhususkan untuk Guru Bahasa Inggris.');
        }

        $rawMonth = $request->input('month', $request->input('week', 'all'));
        $month = strtolower((string)$rawMonth);
        if ($month !== 'all') {
            $monthNum = (int) $month;
            if ($monthNum < 1 || $monthNum > 5) {
                $month = 'all';
            } else {
                $month = (string) $monthNum;
            }
        }

        $classSheet = $request->input('class_name', $request->input('class_sheet', 'all'));

        try {
            $filePath = \App\Services\EnglishGradeExportService::generate($month, $classSheet, $currentUser);
            
            $cleanClass = $classSheet === 'all' ? 'Semua_Kelas' : str_replace(' ', '_', $classSheet);
            $periodLabel = $month === 'all' ? 'Bulan_1_sd_5' : "Bulan_{$month}";
            $downloadName = "Nilai_Bahasa_Inggris_{$cleanClass}_{$periodLabel}.xlsx";

            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('English grades export controller error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor file nilai Excel: ' . $e->getMessage());
        }
    }

    /**
     * Student view: View student's grades, teacher feedback, and exercise score history.
     */
    public function studentGrades(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Japanese students use periodic evaluation tests & separate grade history
        if ((int)$user->subject_id === 2) {
            return redirect()->route('student.japanese.grades.index');
        }

        $subjectId = $user->subject_id ?: 1;
        $subject = Subject::find($subjectId) ?? Subject::where('id', 1)->first();

        // Get monthly grades & teacher feedback for this student
        $monthlyGrades = EnglishGrade::where('student_id', $user->id)
            ->with('teacher')
            ->orderBy('week', 'asc')
            ->get();

        // Get all levels for student's subject with exercises count
        $levels = Level::where('subject_id', $subjectId)
            ->withCount('exercises')
            ->orderBy('order', 'asc')
            ->get();

        // Get student's status & points for each level
        $statuses = UserLevelStatus::where('user_id', $user->id)
            ->get()
            ->keyBy('level_id');

        // Get student attempts grouped by level (Postgres/MySQL/SQLite compatible boolean check)
        $attempts = ExerciseAttempt::where('user_id', $user->id)
            ->select(
                'level_id',
                DB::raw('count(*) as total_attempts'),
                DB::raw('sum(case when is_correct then 1 else 0 end) as correct_attempts'),
                DB::raw('max(updated_at) as last_attempt_at')
            )
            ->groupBy('level_id')
            ->get()
            ->keyBy('level_id');

        // Calculate summary metrics
        $totalLevels = $levels->count();
        $completedLevels = 0;
        $totalPoints = 0;
        $attemptedCount = 0;

        foreach ($levels as $lvl) {
            $st = $statuses->get($lvl->id);
            $pts = $st ? (int)$st->points : 0;
            $totalPoints += $pts;
            if ($st && ($st->is_completed || $pts >= 100)) {
                $completedLevels++;
            }
            if ($pts > 0 || ($attempts->has($lvl->id))) {
                $attemptedCount++;
            }
        }

        $averageScore = $attemptedCount > 0 ? round($totalPoints / $attemptedCount, 1) : 0;

        return view('student.grades.index', compact(
            'user',
            'subject',
            'monthlyGrades',
            'levels',
            'statuses',
            'attempts',
            'totalLevels',
            'completedLevels',
            'totalPoints',
            'attemptedCount',
            'averageScore'
        ));
    }
}
