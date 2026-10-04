<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        $query = Subject::withCount(['levels', 'progresses'])
            ->with(['levels' => function ($q) {
                $q->withCount(['materials', 'exercises']);
            }])
            ->orderBy('order', 'asc');

        if ($isTeacher && $user->subject_id) {
            $query->where('id', $user->subject_id);
        }

        $subjects = $query->get();

        return view('admin.subjects.index', compact('subjects', 'isTeacher'));
    }

    public function create(): View
    {
        $user = auth()->user();
        if ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Guru hanya dapat mengelola 1 mata pelajaran yang telah ditugaskan.');
        }

        return view('admin.subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Guru hanya dapat mengelola 1 mata pelajaran yang telah ditugaskan.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
            'description' => ['nullable', 'string'],
            'badge_color' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique' => 'Mata pelajaran ini sudah ada.',
            'badge_color.required' => 'Warna lencana tema wajib dipilih.',
        ]);

        $subject = Subject::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'badge_color' => $validated['badge_color'],
            'order' => $validated['order'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.subjects.index')
            ->with('success', "Mata pelajaran '{$subject->name}' berhasil ditambahkan. Silakan atur level bertingkatnya.");
    }

    public function edit(Subject $subject): View
    {
        $user = auth()->user();
        if ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
            abort_if($subject->id !== $user->subject_id, 403);
        }

        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
            abort_if($subject->id !== $user->subject_id, 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects')->ignore($subject->id)],
            'description' => ['nullable', 'string'],
            'badge_color' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique' => 'Nama mata pelajaran ini sudah ada.',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'badge_color' => $validated['badge_color'],
            'order' => $validated['order'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subjects.index')
            ->with('success', "Mata pelajaran '{$subject->name}' berhasil diperbarui.");
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Guru tidak memiliki izin untuk menghapus mata pelajaran.');
        }

        $name = $subject->name;
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', "Mata pelajaran '{$name}' beserta seluruh level dan materinya berhasil dihapus.");
    }
}
