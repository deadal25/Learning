<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnglishClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();
        $selectedClass = $request->input('class_name');
        $selectedSubjectId = $request->input('subject_id');

        $classesQuery = EnglishClass::where('is_active', true)->orderBy('sort_order');
        if ($isTeacher && $user->subject_id) {
            $classesQuery->where('subject_id', $user->subject_id);
        } elseif (!empty($selectedSubjectId) && $selectedSubjectId !== 'all') {
            $classesQuery->where('subject_id', $selectedSubjectId);
        }
        $classes = $classesQuery->get();

        $query = User::where('role', User::ROLE_STUDENT)
            ->with(['creator', 'progresses.subject', 'progresses.currentLevel', 'enrollments.subject']);

        if ($isTeacher && $user->subject_id) {
            $query->forSubject($user->subject_id);
            // Khusus guru bahasa Inggris: hanya siswa yang diajar oleh guru ini
            if ((int)$user->subject_id === 1) {
                $assignedIds = $user->getAssignedStudentIds();
                $query->whereIn('users.id', $assignedIds);
            }
        } elseif (!empty($selectedSubjectId) && $selectedSubjectId !== 'all') {
            $query->forSubject($selectedSubjectId);
        }

        if (!empty($selectedClass) && $selectedClass !== 'all') {
            $query->where('class_name', $selectedClass);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $students = $query->latest()->paginate(15)->withQueryString();
        $subjects = Subject::where('is_active', true)->get();

        return view('admin.students.index', compact('students', 'classes', 'selectedClass', 'isTeacher', 'user', 'subjects', 'selectedSubjectId'));
    }

    public function create(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        $classesQuery = EnglishClass::where('is_active', true)->orderBy('sort_order');
        if ($isTeacher && $user->subject_id) {
            $classesQuery->where('subject_id', $user->subject_id);
        } elseif ($subjId = $request->input('subject_id')) {
            $classesQuery->where('subject_id', $subjId);
        }
        $classes = $classesQuery->get();
        $subjects = Subject::where('is_active', true)->get();

        return view('admin.students.create', compact('classes', 'isTeacher', 'user', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        $isTeacher = $currentUser && $currentUser->isAdmin() && !$currentUser->isSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nrp' => ['nullable', 'string', 'max:50'],
            'division' => ['nullable', 'string', 'max:100'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'email.required' => 'Email siswa wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $subjectId = $isTeacher ? $currentUser->subject_id : ($validated['subject_id'] ?? 1);
        $cleanNrp = !empty($validated['nrp']) ? preg_replace('/[^a-zA-Z0-9]/', '', $validated['nrp']) : '';
        $plainPassword = !empty($validated['password']) ? $validated['password'] : ($cleanNrp ? "{$cleanNrp}@musashi" : 'password');

        DB::transaction(function () use ($validated, $currentUser, $isTeacher, $subjectId, $plainPassword) {
            $student = User::create([
                'name' => $validated['name'],
                'nrp' => $validated['nrp'] ?? null,
                'division' => $validated['division'] ?? null,
                'class_name' => $validated['class_name'] ?? null,
                'subject_id' => $subjectId,
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_STUDENT,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'created_by' => $currentUser->id,
            ]);

            // Automatically enroll student into their subject
            $subject = Subject::find($subjectId);
            if ($subject) {
                $student->enrollInSubject(
                    $subject,
                    $isTeacher ? $currentUser : null,
                    $student->class_name ?? ($isTeacher ? $currentUser->class_code : 'REGULAR')
                );
            }
                // Initialize progress for all existing subjects
                $subjects = Subject::with('levels')->get();
                foreach ($subjects as $subject) {
                    $firstLevel = $subject->levels->firstWhere('order', 1);
                    if ($firstLevel) {
                        UserLevelStatus::firstOrCreate([
                            'user_id' => $student->id,
                            'level_id' => $firstLevel->id,
                        ], [
                            'points' => 0,
                            'is_unlocked' => true,
                            'is_completed' => false,
                        ]);

                        foreach ($subject->levels->where('order', '>', 1) as $higherLevel) {
                            UserLevelStatus::firstOrCreate([
                                'user_id' => $student->id,
                                'level_id' => $higherLevel->id,
                            ], [
                                'points' => 0,
                                'is_unlocked' => false,
                                'is_completed' => false,
                            ]);
                        }

                        UserProgress::firstOrCreate([
                            'user_id' => $student->id,
                            'subject_id' => $subject->id,
                        ], [
                            'current_level_id' => $firstLevel->id,
                            'current_points' => 0,
                            'is_completed' => false,
                        ]);
                    }
                }

            // English Grade record is ONLY created for English subject students
            if ((int)$subjectId === 1 && $student->class_name) {
                \App\Models\EnglishGrade::firstOrCreate([
                    'student_id' => $student->id,
                    'class_name' => $student->class_name,
                    'week' => 1,
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
            }
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Akun Pelajar berhasil dibuat dan siap digunakan.');
    }

    public function show(User $student): View
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);

        $currentUser = Auth::user();
        $isTeacher = $currentUser && $currentUser->isAdmin() && !$currentUser->isSuperAdmin();

        if ($isTeacher && $currentUser->subject_id) {
            abort_unless($student->isEnrolledIn($currentUser->subject_id), 403, 'Siswa belum terdaftar pada kelas mata pelajaran Anda.');
            if ((int)$currentUser->subject_id === 1) {
                $assignedIds = $currentUser->getAssignedStudentIds();
                abort_unless(in_array($student->id, $assignedIds), 403, 'Anda tidak berhak melihat data murid guru lain.');
            }
            $student->load([
                'progresses' => fn($q) => $q->where('subject_id', $currentUser->subject_id),
                'progresses.subject',
                'progresses.currentLevel',
                'levelStatuses.level.subject',
                'enrollments.subject',
                'enrollments.teacher'
            ]);
            $subjects = Subject::where('id', $currentUser->subject_id)->with(['levels.materials', 'levels.exercises'])->get();
        } else {
            $student->load(['progresses.subject', 'progresses.currentLevel', 'levelStatuses.level.subject', 'enrollments.subject', 'enrollments.teacher']);
            $subjects = Subject::with(['levels.materials', 'levels.exercises'])->get();
        }

        return view('admin.students.show', compact('student', 'subjects', 'isTeacher'));
    }

    public function edit(User $student): View
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);

        $currentUser = Auth::user();
        $isTeacher = $currentUser && $currentUser->isAdmin() && !$currentUser->isSuperAdmin();

        if ($isTeacher && $currentUser->subject_id) {
            abort_unless($student->isEnrolledIn($currentUser->subject_id), 403);
            if ((int)$currentUser->subject_id === 1) {
                $assignedIds = $currentUser->getAssignedStudentIds();
                abort_unless(in_array($student->id, $assignedIds), 403, 'Anda tidak berhak mengedit data murid guru lain.');
            }
            $classes = EnglishClass::where('is_active', true)->where('subject_id', $currentUser->subject_id)->orderBy('sort_order')->get();
        } else {
            $classes = EnglishClass::where('is_active', true)->where('subject_id', $student->subject_id ?: 1)->orderBy('sort_order')->get();
        }

        return view('admin.students.edit', compact('student', 'classes', 'isTeacher'));
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);

        $currentUser = Auth::user();
        $isTeacher = $currentUser && $currentUser->isAdmin() && !$currentUser->isSuperAdmin();

        if ($isTeacher && $currentUser->subject_id) {
            abort_unless($student->isEnrolledIn($currentUser->subject_id), 403);
            if ((int)$currentUser->subject_id === 1) {
                $assignedIds = $currentUser->getAssignedStudentIds();
                abort_unless(in_array($student->id, $assignedIds), 403, 'Anda tidak berhak memperbarui murid guru lain.');
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nrp' => ['nullable', 'string', 'max:50'],
            'division' => ['nullable', 'string', 'max:100'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'email.required' => 'Email siswa wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar untuk pengguna lain.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'nrp' => !empty($validated['nrp']) ? trim($validated['nrp']) : null,
            'division' => $validated['division'] ?? null,
            'class_name' => $validated['class_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $student->update($updateData);

        return redirect()->route('admin.students.index')
            ->with('success', "Data profil siswa '{$student->name}' berhasil diperbarui.");
    }

    public function destroy(User $student): RedirectResponse
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);

        $currentUser = Auth::user();
        $isTeacher = $currentUser && $currentUser->isAdmin() && !$currentUser->isSuperAdmin();

        if ($isTeacher && $currentUser->subject_id) {
            abort_unless($student->isEnrolledIn($currentUser->subject_id), 403);
            if ((int)$currentUser->subject_id === 1) {
                $assignedIds = $currentUser->getAssignedStudentIds();
                abort_unless(in_array($student->id, $assignedIds), 403, 'Anda tidak berhak menghapus murid guru lain.');
            }
        }

        $name = $student->name;
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "Akun pelajar '{$name}' berhasil dihapus.");
    }
}
