<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JapaneseTest;
use App\Models\JapaneseTestQuestion;
use App\Models\JapaneseTestSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JapaneseTestController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

        $tests = JapaneseTest::withCount(['questions', 'submissions'])
            ->orderBy('start_meeting', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $meetingTests = $tests->where('category', 'per_pertemuan')->values();
        $periodicTests = $tests->where('category', 'per_4_pertemuan')->values();

        $selectedTestId = $request->input('test_id', $tests->first()?->id);
        $submissionsQuery = JapaneseTestSubmission::with(['student', 'test'])->latest();
        if ($selectedTestId && $selectedTestId !== 'all') {
            $submissionsQuery->where('test_id', $selectedTestId);
        }
        $submissions = $submissionsQuery->paginate(15)->withQueryString();

        return view('admin.japanese_tests.index', compact('tests', 'meetingTests', 'periodicTests', 'submissions', 'selectedTestId'));
    }

    public function questions(JapaneseTest $test): View
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && $user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

        $test->load(['questions']);

        return view('admin.japanese_tests.questions', compact('test'));
    }

    public function storeQuestion(Request $request, JapaneseTest $test): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

        $validated = $request->validate([
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $maxNumber = $test->questions()->max('question_number') ?? 0;

        $test->questions()->create([
            'question_number' => $maxNumber + 1,
            'question' => $validated['question'],
            'option_a' => $validated['option_a'],
            'option_b' => $validated['option_b'],
            'option_c' => $validated['option_c'],
            'option_d' => $validated['option_d'],
            'correct_option' => strtolower($validated['correct_option']),
            'explanation' => $validated['explanation'] ?? null,
            'points' => $validated['points'],
        ]);

        return back()->with('success', 'Soal tes baru berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, JapaneseTestQuestion $question): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

        $validated = $request->validate([
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $question->update([
            'question' => $validated['question'],
            'option_a' => $validated['option_a'],
            'option_b' => $validated['option_b'],
            'option_c' => $validated['option_c'],
            'option_d' => $validated['option_d'],
            'correct_option' => strtolower($validated['correct_option']),
            'explanation' => $validated['explanation'] ?? null,
            'points' => $validated['points'],
        ]);

        return back()->with('success', "Soal nomor #{$question->question_number} berhasil diperbarui.");
    }

    public function destroyQuestion(JapaneseTestQuestion $question): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

        $num = $question->question_number;
        $question->delete();

        return back()->with('success', "Soal nomor #{$num} telah dihapus.");
    }

    public function toggleActive(JapaneseTest $test): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

        $test->is_active = !$test->is_active;
        $test->save();

        $categoryPrefix = $test->category === 'per_pertemuan' ? 'Latihan Soal Pertemuan ' . $test->start_meeting : 'Ujian Evaluasi Pertemuan ' . $test->start_meeting . '-' . $test->end_meeting;

        $statusMessage = $test->is_active
            ? "{$categoryPrefix} ('{$test->title}') berhasil DIAKTIFKAN. Siswa kelas Jepang sekarang dapat mengakses dan mengerjakan soal ini."
            : "{$categoryPrefix} ('{$test->title}') berhasil DINONAKTIFKAN (Terkunci). Siswa tidak dapat mengakses soal ini sampai diaktifkan kembali.";

        return back()->with('success', $statusMessage);
    }

    public function toggleAll(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

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
            'per_pertemuan' => 'Latihan Soal Per Pertemuan (10 Soal/Pertemuan)',
            'per_4_pertemuan' => 'Ujian Evaluasi Per 4 Pertemuan (15 Soal)',
            default => 'Semua Soal Bahasa Jepang',
        };

        $statusLabel = $isActive ? 'DIAKTIFKAN (Siswa sekarang dapat mengakses)' : 'DINONAKTIFKAN (Terkunci untuk siswa)';

        return back()->with('success', "Berhasil: {$updatedCount} modul pada {$categoryLabel} telah {$statusLabel}.");
    }

    public function updateTest(Request $request, JapaneseTest $test): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && (int)$user->subject_id !== 2) {
            abort(403, 'Akses khusus untuk Guru Bahasa Jepang.');
        }

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

        return back()->with('success', "Pengaturan ujian '{$test->title}' berhasil disimpan.");
    }
}
