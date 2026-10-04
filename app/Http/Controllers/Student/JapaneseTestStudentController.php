<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JapaneseTest;
use App\Models\JapaneseTestSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JapaneseTestStudentController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        
        $tests = JapaneseTest::where('is_active', true)
            ->withCount('questions')
            ->orderBy('start_meeting', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $meetingTests = $tests->where('category', 'per_pertemuan')->values();
        $periodicTests = $tests->where('category', 'per_4_pertemuan')->values();

        $userSubmissions = JapaneseTestSubmission::where('student_id', $user->id)
            ->get()
            ->keyBy('test_id');

        return view('student.japanese.tests.index', compact('tests', 'meetingTests', 'periodicTests', 'userSubmissions'));
    }

    public function show(JapaneseTest $test): View|RedirectResponse
    {
        if (!$test->is_active) {
            return redirect()->route('student.japanese.tests.index')
                ->with('error', 'Mohon maaf, latihan/evaluasi ini belum diaktifkan oleh Guru Bahasa Jepang. Anda baru bisa mengakses setelah guru mengaktifkannya.');
        }

        // Randomize questions: 10 questions for per_pertemuan, 15 questions for per_4_pertemuan
        $targetCount = $test->target_questions ?: ($test->category === 'per_4_pertemuan' ? 15 : 10);
        $questions = $test->questions()->inRandomOrder()->take($targetCount)->get();

        if ($questions->isEmpty()) {
            return redirect()->route('student.japanese.tests.index')
                ->with('error', 'Soal untuk modul ini sedang dipersiapkan oleh guru.');
        }

        $user = Auth::user();
        $previousSubmission = JapaneseTestSubmission::where('student_id', $user->id)
            ->where('test_id', $test->id)
            ->latest()
            ->first();

        return view('student.japanese.tests.show', compact('test', 'questions', 'previousSubmission'));
    }

    public function submit(Request $request, JapaneseTest $test): View|RedirectResponse
    {
        if (!$test->is_active) {
            return redirect()->route('student.japanese.tests.index')
                ->with('error', 'Ujian/latihan soal ini sedang dinonaktifkan oleh Guru Bahasa Jepang.');
        }

        $user = Auth::user();
        $submittedAnswers = $request->input('answers', []);
        $questionIds = $request->input('question_ids', []);

        if (!empty($questionIds)) {
            $questions = $test->questions()->whereIn('id', $questionIds)->get();
        } else {
            $questions = $test->questions;
        }

        $totalQuestions = $questions->count();
        $correctCount = 0;
        $totalScore = 0;
        $maxPossible = 0;

        foreach ($questions as $q) {
            $maxPossible += $q->points;
            $userAns = strtolower($submittedAnswers[$q->id] ?? '');
            if ($userAns && $userAns === strtolower($q->correct_option)) {
                $correctCount++;
                $totalScore += $q->points;
            }
        }

        $finalPercentage = $maxPossible > 0 ? round(($totalScore / $maxPossible) * 100) : 0;
        $isPassed = $finalPercentage >= $test->pass_score;

        $feedback = $isPassed
            ? ($test->category === 'per_pertemuan'
                ? "Bagus sekali! Anda telah menyelesaikan latihan Pertemuan {$test->start_meeting} dengan nilai memuaskan."
                : "Selamat! Anda lulus evaluasi 4 pertemuan ini dengan pemahaman materi yang sangat baik.")
            : "Nilai belum mencapai standar kelulusan (KKM {$test->pass_score}%). Silakan pelajari kembali modul materi dan ulangi latihan soal.";

        $submission = JapaneseTestSubmission::create([
            'test_id' => $test->id,
            'student_id' => $user->id,
            'score' => $finalPercentage,
            'total_questions' => $totalQuestions,
            'correct_count' => $correctCount,
            'is_passed' => $isPassed,
            'submitted_answers' => $submittedAnswers,
            'teacher_feedback' => $feedback,
        ]);

        return view('student.japanese.tests.result', compact('test', 'submission', 'submittedAnswers', 'questions'));
    }

    public function gradeHistory(): View
    {
        $user = Auth::user();
        $submissions = JapaneseTestSubmission::where('student_id', $user->id)
            ->with('test')
            ->latest()
            ->paginate(15);

        $passedCount = JapaneseTestSubmission::where('student_id', $user->id)->where('is_passed', true)->count();
        $averageScore = JapaneseTestSubmission::where('student_id', $user->id)->avg('score');

        return view('student.japanese.grades.index', compact('submissions', 'passedCount', 'averageScore'));
    }
}
