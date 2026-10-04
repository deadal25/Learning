<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnglishClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnglishClassProfileController extends Controller
{
    /**
     * Show class management and teacher profile page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $subjects = \App\Models\Subject::where('is_active', true)->orderBy('id')->get();
        $selectedSubjectId = $request->input('subject_id', ($isTeacher && $user->subject_id ? $user->subject_id : 1));

        $classesQuery = EnglishClass::withCount(['students', 'materials'])
            ->with('subject')
            ->orderBy('sort_order', 'asc');

        if ($isTeacher && $user->subject_id) {
            $classesQuery->where('subject_id', $user->subject_id);
        } elseif (!empty($selectedSubjectId) && $selectedSubjectId !== 'all') {
            $classesQuery->where('subject_id', $selectedSubjectId);
        }

        $classes = $classesQuery->get();

        return view('admin.classes.index', compact('user', 'classes', 'subjects', 'selectedSubjectId', 'isTeacher'));
    }

    /**
     * Update a class name and details.
     */
    public function updateClass(Request $request, EnglishClass $class): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id && (int)$class->subject_id !== (int)$user->subject_id) {
            abort(403, 'Anda hanya dapat mengedit kelas mata pelajaran Anda.');
        }

        $validated = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('english_classes', 'name')->ignore($class->id)],
            'level_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas ini sudah digunakan.',
        ]);

        $oldName = $class->name;
        $newName = $validated['name'];

        $levelName = !empty($validated['level_name'])
            ? $validated['level_name']
            : ($class->subject_id == 2 ? $newName : $class->level_name);

        $updateData = [
            'name' => $newName,
            'level_name' => $levelName,
            'description' => $validated['description'] ?? $class->description,
        ];
        if (!$isTeacher && !empty($validated['subject_id'])) {
            $updateData['subject_id'] = $validated['subject_id'];
        }

        $class->update($updateData);

        // If class name changed, update students and materials assigned to the old name
        if ($oldName !== $newName) {
            User::where('class_name', $oldName)->update(['class_name' => $newName]);
            \App\Models\Material::where('class_name', $oldName)->update(['class_name' => $newName]);
            \App\Models\EnglishGrade::where('class_name', $oldName)->update(['class_name' => $newName]);
        }

        $term = $class->subject_id == 2 ? 'Grup' : 'Kelas';
        return back()->with('success', "{$term} '{$newName}' berhasil diperbarui.");
    }

    /**
     * Add a new class / group.
     */
    public function storeClass(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $validated = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:100', 'unique:english_classes,name'],
            'level_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama grup/kelas baru wajib diisi.',
            'name.unique' => 'Nama grup/kelas ini sudah terdaftar.',
        ]);

        $subjectId = ($isTeacher && $user->subject_id) ? $user->subject_id : ($validated['subject_id'] ?? 1);
        $maxSort = EnglishClass::where('subject_id', $subjectId)->max('sort_order') ?? 0;
        $nextOrder = $maxSort + 1;
        $levelName = !empty($validated['level_name'])
            ? $validated['level_name']
            : ($subjectId == 2 ? $validated['name'] : "Level {$nextOrder}");

        EnglishClass::create([
            'subject_id' => $subjectId,
            'name' => $validated['name'],
            'level_name' => $levelName,
            'description' => $validated['description'] ?? ($subjectId == 2 ? "Grup Belajar Bahasa Jepang Musashi - {$validated['name']}" : null),
            'sort_order' => $nextOrder,
            'is_active' => true,
        ]);

        $term = $subjectId == 2 ? 'Grup' : 'Kelas';
        return back()->with('success', "{$term} baru '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Delete an English/Japanese class or group.
     */
    public function destroyClass(EnglishClass $class): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id && (int)$class->subject_id !== (int)$user->subject_id) {
            abort(403, 'Anda hanya dapat menghapus kelas/grup mata pelajaran Anda.');
        }

        $term = $class->subject_id == 2 ? 'Grup' : 'Kelas';
        $name = $class->name;
        $class->delete();

        return back()->with('success', "{$term} '{$name}' telah berhasil dihapus.");
    }

    /**
     * Update teacher profile (name, email, phone, password).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama guru wajib diisi.',
            'email.required' => 'Email guru wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Profil dan akun pengajar Anda berhasil diperbarui.');
    }
}
