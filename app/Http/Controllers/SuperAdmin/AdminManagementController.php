<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', User::ROLE_ADMIN)->with('subject');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('class_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $admins = $query->withCount('teacherEnrollments')->latest()->paginate(10)->withQueryString();

        return view('superadmin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        $subjects = \App\Models\Subject::orderBy('order', 'asc')->get();
        return view('superadmin.admins.create', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->where(function ($query) use ($request) {
                return $query->whereRaw('LOWER(email) = ?', [strtolower(trim($request->input('email')))]);
            })],
            'password' => ['required', 'string', 'min:4'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_code' => ['nullable', 'string', 'max:50', 'unique:users,class_code'],
            'phone' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'subject_id.required' => 'Mata pelajaran yang diajar (Bahasa Inggris, Bahasa Jepang, atau Matematika) wajib dipilih.',
            'class_code.unique' => 'Kode kelas ini sudah digunakan oleh guru lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 4 karakter.',
        ]);

        $classCode = null;
        if (!empty($validated['class_code'])) {
            $classCode = strtoupper(trim($validated['class_code']));
        } else {
            $prefix = match((int)$validated['subject_id']) {
                1 => 'ENG',
                2 => 'JPN',
                3 => 'MATH',
                default => 'CLS'
            };
            $cleanName = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 6));
            $candidate = $prefix . '-' . ($cleanName ?: \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5)));
            while (User::where('class_code', $candidate)->exists()) {
                $candidate = $prefix . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5));
            }
            $classCode = $candidate;
        }

        $email = strtolower(trim($validated['email']));

        User::create([
            'name' => trim($validated['name']),
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_ADMIN,
            'subject_id' => $validated['subject_id'],
            'class_code' => $classCode,
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'status' => $validated['status'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('superadmin.admins.index')
            ->with('success', "Akun Guru pengajar '{$validated['name']}' ({$email}) berhasil dibuat dan siap digunakan untuk login.");
    }

    public function edit(User $admin): View
    {
        abort_if($admin->role !== User::ROLE_ADMIN, 404);
        $subjects = \App\Models\Subject::orderBy('order', 'asc')->get();

        return view('superadmin.admins.edit', compact('admin', 'subjects'));
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        abort_if($admin->role !== User::ROLE_ADMIN, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->where(function ($query) use ($request, $admin) {
                return $query->whereRaw('LOWER(email) = ?', [strtolower(trim($request->input('email')))])
                             ->where('id', '!=', $admin->id);
            })],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_code' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:4'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'subject_id.required' => 'Mata pelajaran yang diajar wajib dipilih.',
            'class_code.unique' => 'Kode kelas ini sudah digunakan oleh guru lain.',
            'password.min' => 'Kata sandi minimal 4 karakter jika ingin diubah.',
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));

        $updateData = [
            'name' => trim($validated['name']),
            'email' => $normalizedEmail,
            'subject_id' => $validated['subject_id'],
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['class_code'])) {
            $updateData['class_code'] = strtoupper(trim($validated['class_code']));
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $admin->update($updateData);

        return redirect()->route('superadmin.admins.index')
            ->with('success', "Data Guru '{$admin->name}' ({$normalizedEmail}) berhasil diperbarui dan kata sandi baru langsung aktif untuk login.");
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_if($admin->role !== User::ROLE_ADMIN, 404);

        $name = $admin->name;
        $admin->delete();

        return redirect()->route('superadmin.admins.index')
            ->with('success', "Akun Admin {$name} berhasil dihapus.");
    }
}
