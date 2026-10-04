<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    /**
     * Show student profile data and edit form.
     */
    public function edit(): View
    {
        $user = Auth::user();
        $user->load('subject');

        $subjectName = $user->subject?->name ?: match((int)$user->subject_id) {
            2 => 'Bahasa Jepang',
            3 => 'Matematika',
            default => 'Bahasa Inggris',
        };

        return view('student.profile.index', compact('user', 'subjectName'));
    }

    /**
     * Update student profile info (name, email, and password).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil dan informasi akun Anda berhasil diperbarui!');
    }
}
