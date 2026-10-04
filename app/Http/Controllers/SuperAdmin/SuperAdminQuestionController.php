<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\JapaneseTest;
use App\Models\JapaneseTestQuestion;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminQuestionController extends Controller
{
    /**
     * Display question management for Super Admin across 3 subjects: English, Japanese, and Math.
     */
    public function index(Request $request): View
    {
        $subjects = Subject::orderBy('order', 'asc')->get();
        $selectedSubjectId = (int)$request->input('subject_id', 1);
        $currentSubject = Subject::find($selectedSubjectId) ?? $subjects->first();

        // 1. Japanese Subject Logic (Subject ID 2)
        if ($currentSubject->id === 2) {
            $isJapaneseTestMode = true;

            // Categories: 'per_pertemuan' (12 meetings) or 'per_4_pertemuan' (3 evaluations)
            $rawCategory = $request->input('category');
            if (!$rawCategory) {
                $rawMode = $request->input('mode');
                $rawCategory = ($rawMode === 'eval_tests') ? 'per_4_pertemuan' : 'per_pertemuan';
            }
            $selectedJpCategory = in_array($rawCategory, ['per_pertemuan', 'per_4_pertemuan'], true) ? $rawCategory : 'per_pertemuan';

            $meetingTests = JapaneseTest::withCount('questions')
                ->where('category', 'per_pertemuan')
                ->orderBy('start_meeting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $periodicTests = JapaneseTest::withCount('questions')
                ->where('category', 'per_4_pertemuan')
                ->orderBy('start_meeting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $activeJpTests = ($selectedJpCategory === 'per_4_pertemuan') ? $periodicTests : $meetingTests;

            $selectedTestId = (int)$request->input('test_id', $activeJpTests->first()?->id);
            $currentTest = $activeJpTests->firstWhere('id', $selectedTestId) ?? $activeJpTests->first();

            $testQuestions = collect();
            $nextQuestionNumber = 1;

            if ($currentTest) {
                $testQuestions = $currentTest->questions()->orderBy('question_number', 'asc')->get();
                $nextQuestionNumber = ($testQuestions->max('question_number') ?? 0) + 1;
            }

            return view('superadmin.questions.index', [
                'subjects' => $subjects,
                'currentSubject' => $currentSubject,
                'isJapaneseTestMode' => true,
                'selectedJpCategory' => $selectedJpCategory,
                'meetingTests' => $meetingTests,
                'periodicTests' => $periodicTests,
                'activeJpTests' => $activeJpTests,
                'currentTest' => $currentTest,
                'testQuestions' => $testQuestions,
                'nextQuestionNumber' => $nextQuestionNumber,
                'levels' => collect(),
                'currentLevel' => null,
                'selectedLevelId' => null,
                'exercises' => collect(),
            ]);
        }

        // 2. English (Subject ID 1) and Math (Subject ID 3) Logic
        $levels = Level::where('subject_id', $currentSubject->id)
            ->withCount('exercises')
            ->orderBy('order', 'asc')
            ->get();

        $selectedLevelId = (int)$request->input('level_id', $levels->first()?->id);
        $currentLevel = $levels->firstWhere('id', $selectedLevelId) ?? $levels->first();

        $exercises = collect();
        $nextQuestionNumber = 1;

        if ($currentLevel) {
            $exercises = Exercise::where('level_id', $currentLevel->id)
                ->orderBy('question_number', 'asc')
                ->get();
            $nextQuestionNumber = ($exercises->max('question_number') ?? 0) + 1;
        }

        return view('superadmin.questions.index', [
            'subjects' => $subjects,
            'currentSubject' => $currentSubject,
            'isJapaneseTestMode' => false,
            'selectedJpCategory' => 'per_pertemuan',
            'meetingTests' => collect(),
            'periodicTests' => collect(),
            'activeJpTests' => collect(),
            'currentTest' => null,
            'testQuestions' => collect(),
            'nextQuestionNumber' => $nextQuestionNumber,
            'levels' => $levels,
            'currentLevel' => $currentLevel,
            'selectedLevelId' => $selectedLevelId,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Store a newly created question (Exercise or JapaneseTestQuestion).
     */
    public function store(Request $request): RedirectResponse
    {
        $isJpTest = $request->boolean('is_japanese_test');

        if ($isJpTest) {
            $validated = $request->validate([
                'test_id' => ['required', 'exists:japanese_tests,id'],
                'question_number' => ['required', 'integer', 'min:1'],
                'question' => ['required', 'string'],
                'option_a' => ['required', 'string'],
                'option_b' => ['required', 'string'],
                'option_c' => ['required', 'string'],
                'option_d' => ['required', 'string'],
                'correct_option' => ['required', 'in:a,b,c,d'],
                'explanation' => ['nullable', 'string'],
                'points' => ['required', 'integer', 'min:1', 'max:100'],
            ]);

            $test = JapaneseTest::findOrFail($validated['test_id']);
            JapaneseTestQuestion::create($validated);

            $label = $test->category === 'per_pertemuan' ? "Latihan Pertemuan {$test->start_meeting}" : "Ujian {$test->title}";

            return redirect()->route('superadmin.questions.index', [
                'subject_id' => 2,
                'category' => $test->category,
                'test_id' => $test->id,
            ])->with('success', "✅ Butir soal #{$validated['question_number']} berhasil ditambahkan ke {$label}.");
        }

        $validated = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'question_number' => ['required', 'integer', 'min:1'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $level = Level::findOrFail($validated['level_id']);

        Exercise::create($validated);

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => $level->subject_id,
            'level_id' => $level->id,
        ])->with('success', "✅ Butir soal #{$validated['question_number']} berhasil ditambahkan ke {$level->name}.");
    }

    /**
     * Update an existing question.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $isJpTest = $request->boolean('is_japanese_test');

        $validated = $request->validate([
            'question_number' => ['required', 'integer', 'min:1'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if ($isJpTest) {
            $jpQuestion = JapaneseTestQuestion::findOrFail($id);
            $jpQuestion->update($validated);
            $test = $jpQuestion->test;

            return redirect()->route('superadmin.questions.index', [
                'subject_id' => 2,
                'category' => $test?->category ?? 'per_pertemuan',
                'test_id' => $jpQuestion->test_id,
            ])->with('success', "✅ Butir soal #{$jpQuestion->question_number} berhasil diperbarui.");
        }

        $exercise = Exercise::findOrFail($id);
        $exercise->update($validated);

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => $exercise->level->subject_id,
            'level_id' => $exercise->level_id,
        ])->with('success', "✅ Butir soal #{$exercise->question_number} berhasil diperbarui.");
    }

    /**
     * Delete an existing question.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $isJpTest = $request->boolean('is_japanese_test');

        if ($isJpTest) {
            $jpQuestion = JapaneseTestQuestion::findOrFail($id);
            $test = $jpQuestion->test;
            $testId = $jpQuestion->test_id;
            $category = $test?->category ?? 'per_pertemuan';
            $qNum = $jpQuestion->question_number;
            $jpQuestion->delete();

            return redirect()->route('superadmin.questions.index', [
                'subject_id' => 2,
                'category' => $category,
                'test_id' => $testId,
            ])->with('success', "🗑️ Butir soal #{$qNum} berhasil dihapus.");
        }

        $exercise = Exercise::findOrFail($id);
        $level = $exercise->level;
        $qNum = $exercise->question_number;
        $exercise->delete();

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => $level->subject_id,
            'level_id' => $level->id,
        ])->with('success', "🗑️ Butir soal #{$qNum} berhasil dihapus dari {$level->name}.");
    }

    /**
     * Toggle active/inactive for English / Level meeting.
     */
    public function toggleLevelActive(Level $level): RedirectResponse
    {
        $level->is_active = !$level->is_active;
        $level->save();

        $statusLabel = $level->is_active
            ? 'DIAKTIFKAN. Siswa sekarang dapat melihat dan mengerjakan latihan soal pertemuan ini.'
            : 'DINONAKTIFKAN (Terkunci). Siswa tidak dapat mengakses latihan ini sampai diaktifkan kembali.';

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => $level->subject_id,
            'level_id' => $level->id,
        ])->with('success', "✅ Status {$level->name} ({$level->subject->name}) berhasil {$statusLabel}");
    }

    /**
     * Bulk toggle active/inactive for all Levels in a subject (e.g. Bahasa Inggris).
     */
    public function toggleAllLevels(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $isActive = ($validated['action'] === 'activate');
        $subjectId = (int)$validated['subject_id'];
        $subject = Subject::findOrFail($subjectId);

        $updatedCount = Level::where('subject_id', $subjectId)->update(['is_active' => $isActive]);

        $statusLabel = $isActive
            ? 'DIAKTIFKAN (Siswa dapat mengakses)'
            : 'DINONAKTIFKAN (Terkunci untuk siswa)';

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => $subjectId,
        ])->with('success', "⚡ Berhasil: Seluruh {$updatedCount} pertemuan pada mata pelajaran {$subject->name} telah {$statusLabel}.");
    }

    /**
     * Toggle active/inactive for Japanese Test (Per Pertemuan or Per 4 Pertemuan).
     */
    public function toggleJapaneseActive(JapaneseTest $test): RedirectResponse
    {
        $test->is_active = !$test->is_active;
        $test->save();

        $categoryPrefix = $test->category === 'per_pertemuan'
            ? 'Latihan Soal Pertemuan ' . $test->start_meeting
            : 'Ujian Evaluasi ' . $test->title;

        $statusLabel = $test->is_active
            ? 'DIAKTIFKAN. Siswa kelas Jepang sekarang dapat melihat dan mengerjakan soal ini.'
            : 'DINONAKTIFKAN (Terkunci). Siswa tidak dapat melihat atau mengakses soal ini.';

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => 2,
            'category' => $test->category,
            'test_id' => $test->id,
        ])->with('success', "✅ {$categoryPrefix} ('{$test->title}') berhasil {$statusLabel}");
    }

    /**
     * Bulk toggle active/inactive for Japanese tests.
     */
    public function toggleAllJapanese(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate'],
            'category' => ['nullable', 'in:all,per_pertemuan,per_4_pertemuan'],
        ]);

        $isActive = ($validated['action'] === 'activate');
        $query = JapaneseTest::query();

        if (!empty($validated['category']) && $validated['category'] !== 'all') {
            $query->where('category', $validated['category']);
        }

        $updatedCount = $query->update(['is_active' => $isActive]);

        $categoryLabel = match ($validated['category'] ?? 'all') {
            'per_pertemuan' => 'Latihan Soal Per Pertemuan (1-12)',
            'per_4_pertemuan' => 'Ujian Evaluasi Per 4 Pertemuan (1-3)',
            default => 'Semua Modul Soal Bahasa Jepang',
        };

        $statusLabel = $isActive ? 'DIAKTIFKAN untuk siswa' : 'DINONAKTIFKAN (Terkunci)';

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => 2,
            'category' => ($validated['category'] === 'per_4_pertemuan') ? 'per_4_pertemuan' : 'per_pertemuan',
        ])->with('success', "⚡ Berhasil: {$updatedCount} modul pada {$categoryLabel} telah {$statusLabel}.");
    }

    /**
     * Update Japanese test metadata (title, duration, pass_score, etc.).
     */
    public function updateJapaneseTest(Request $request, JapaneseTest $test): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'pass_score' => ['required', 'integer', 'min:10', 'max:100'],
            'target_questions' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $test->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'pass_score' => $validated['pass_score'],
            'target_questions' => $validated['target_questions'] ?? $test->target_questions,
        ]);

        return redirect()->route('superadmin.questions.index', [
            'subject_id' => 2,
            'category' => $test->category,
            'test_id' => $test->id,
        ])->with('success', "⚙️ Pengaturan modul ujian/latihan '{$test->title}' berhasil diperbarui.");
    }
}
