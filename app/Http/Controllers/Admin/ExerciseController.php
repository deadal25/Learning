<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher) {
            if ((int)$user->subject_id === 1) {
                return redirect()->route('admin.grades.index');
            } elseif ((int)$user->subject_id === 2) {
                return redirect()->route('admin.japanese.tests.index');
            }
        }

        $subjects = $isTeacher && $user->subject_id
            ? Subject::where('id', $user->subject_id)->with('levels')->get()
            : Subject::with('levels')->get();

        $query = Exercise::with('level.subject');

        if ($isTeacher && $user->subject_id) {
            $query->whereHas('level', function ($q) use ($user) {
                $q->where('subject_id', $user->subject_id);
            });
        } elseif ($subjectId = $request->input('subject_id')) {
            $query->whereHas('level', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        if ($levelId = $request->input('level_id')) {
            $query->where('level_id', $levelId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('explanation', 'like', "%{$search}%");
            });
        }

        $exercises = $query->orderBy('level_id')
            ->orderBy('question_number')
            ->paginate(15)
            ->withQueryString();

        return view('admin.exercises.index', compact('exercises', 'subjects', 'isTeacher'));
    }

    public function create(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && (int)$user->subject_id !== 3) {
            abort(403, 'Akses kelola bank soal hanya untuk Guru Matematika.');
        }

        $subjects = $isTeacher && $user->subject_id
            ? Subject::where('id', $user->subject_id)->with('levels')->get()
            : Subject::with('levels')->get();

        $selectedLevelId = $request->input('level_id');
        $nextNumber = 1;

        if ($selectedLevelId) {
            $nextNumber = (Exercise::where('level_id', $selectedLevelId)->max('question_number') ?? 0) + 1;
        }

        return view('admin.exercises.create', compact('subjects', 'selectedLevelId', 'nextNumber', 'isTeacher'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && (int)$user->subject_id !== 3) {
            abort(403, 'Akses kelola bank soal hanya untuk Guru Matematika.');
        }

        $validated = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'question_number' => ['required', 'integer', 'min:1', 'max:20'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'level_id.required' => 'Tingkatan/Level wajib dipilih.',
            'question_number.required' => 'Nomor urut soal wajib diisi.',
            'question.required' => 'Teks pertanyaan wajib diisi.',
            'option_a.required' => 'Pilihan A wajib diisi.',
            'option_b.required' => 'Pilihan B wajib diisi.',
            'option_c.required' => 'Pilihan C wajib diisi.',
            'option_d.required' => 'Pilihan D wajib diisi.',
            'correct_option.required' => 'Kunci jawaban benar wajib ditentukan.',
        ]);

        if ($isTeacher && $user->subject_id) {
            $level = Level::findOrFail($validated['level_id']);
            abort_if($level->subject_id !== $user->subject_id, 403, 'Anda hanya dapat menambah soal untuk mata pelajaran Anda.');
        }

        Exercise::create($validated);

        return redirect()->route('admin.exercises.index', ['level_id' => $validated['level_id']])
            ->with('success', "Soal latihan #{$validated['question_number']} berhasil ditambahkan.");
    }

    public function edit(Exercise $exercise): View
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && (int)$user->subject_id !== 3) {
            abort(403, 'Akses kelola bank soal hanya untuk Guru Matematika.');
        }

        if ($isTeacher && $user->subject_id) {
            abort_if($exercise->level->subject_id !== $user->subject_id, 403, 'Anda tidak berhak mengedit soal ini.');
            $subjects = Subject::where('id', $user->subject_id)->with('levels')->get();
        } else {
            $subjects = Subject::with('levels')->get();
        }

        return view('admin.exercises.edit', compact('exercise', 'subjects', 'isTeacher'));
    }

    public function update(Request $request, Exercise $exercise): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && (int)$user->subject_id !== 3) {
            abort(403, 'Akses kelola bank soal hanya untuk Guru Matematika.');
        }

        if ($isTeacher && $user->subject_id) {
            abort_if($exercise->level->subject_id !== $user->subject_id, 403);
        }

        $validated = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'question_number' => ['required', 'integer', 'min:1', 'max:20'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'level_id.required' => 'Tingkatan/Level wajib dipilih.',
            'question_number.required' => 'Nomor urut soal wajib diisi.',
            'question.required' => 'Teks pertanyaan wajib diisi.',
            'correct_option.required' => 'Kunci jawaban benar wajib ditentukan.',
        ]);

        if ($isTeacher && $user->subject_id) {
            $level = Level::findOrFail($validated['level_id']);
            abort_if($level->subject_id !== $user->subject_id, 403);
        }

        $exercise->update($validated);

        return redirect()->route('admin.exercises.index', ['level_id' => $validated['level_id']])
            ->with('success', "Soal latihan #{$validated['question_number']} berhasil diperbarui.");
    }

    public function destroy(Exercise $exercise): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();
        if ($isTeacher && (int)$user->subject_id !== 3) {
            abort(403, 'Akses kelola bank soal hanya untuk Guru Matematika.');
        }
        if ($isTeacher && $user->subject_id) {
            abort_if($exercise->level->subject_id !== $user->subject_id, 403);
        }

        $levelId = $exercise->level_id;
        $num = $exercise->question_number;
        $exercise->delete();

        return redirect()->route('admin.exercises.index', ['level_id' => $levelId])
            ->with('success', "Soal latihan #{$num} berhasil dihapus.");
    }
}
