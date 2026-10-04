<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LevelController extends Controller
{
    protected function authorizeSubject(Subject $subject): void
    {
        $user = auth()->user();
        if ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
            abort_if($user->subject_id !== $subject->id, 403, 'Anda hanya berhak mengelola tingkatan level untuk mata pelajaran Anda.');
        }
    }

    public function index(Subject $subject): View
    {
        $this->authorizeSubject($subject);

        $subject->load(['levels' => function ($q) {
            $q->withCount(['materials', 'exercises'])->orderBy('order', 'asc');
        }]);

        return view('admin.levels.index', compact('subject'));
    }

    public function create(Subject $subject): View
    {
        $this->authorizeSubject($subject);

        $nextOrder = ($subject->levels()->max('order') ?? 0) + 1;

        return view('admin.levels.create', compact('subject', 'nextOrder'));
    }

    public function store(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizeSubject($subject);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('levels')->where('subject_id', $subject->id),
            ],
            'description' => ['nullable', 'string'],
            'required_points' => ['required', 'integer', 'min:10', 'max:500'],
        ], [
            'name.required' => 'Nama tingkatan/level wajib diisi.',
            'order.required' => 'Urutan level wajib ditentukan.',
            'order.unique' => 'Urutan level ini sudah digunakan pada mapel ini.',
            'required_points.required' => 'Syarat poin untuk naik level wajib ditentukan (standar 100).',
        ]);

        $subject->levels()->create($validated);

        return redirect()->route('admin.subjects.levels.index', $subject)
            ->with('success', "Tingkatan/Level '{$validated['name']}' berhasil ditambahkan ke mata pelajaran {$subject->name}.");
    }

    public function edit(Subject $subject, Level $level): View
    {
        $this->authorizeSubject($subject);
        abort_if($level->subject_id !== $subject->id, 404);

        return view('admin.levels.edit', compact('subject', 'level'));
    }

    public function update(Request $request, Subject $subject, Level $level): RedirectResponse
    {
        $this->authorizeSubject($subject);
        abort_if($level->subject_id !== $subject->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('levels')->where('subject_id', $subject->id)->ignore($level->id),
            ],
            'description' => ['nullable', 'string'],
            'required_points' => ['required', 'integer', 'min:10', 'max:500'],
        ], [
            'name.required' => 'Nama tingkatan/level wajib diisi.',
            'order.unique' => 'Urutan level ini sudah digunakan pada mapel ini.',
        ]);

        $level->update($validated);

        return redirect()->route('admin.subjects.levels.index', $subject)
            ->with('success', "Level '{$level->name}' berhasil diperbarui.");
    }

    public function destroy(Subject $subject, Level $level): RedirectResponse
    {
        $this->authorizeSubject($subject);
        abort_if($level->subject_id !== $subject->id, 404);

        $name = $level->name;
        $level->delete();

        return redirect()->route('admin.subjects.levels.index', $subject)
            ->with('success', "Level '{$name}' berhasil dihapus.");
    }
}
