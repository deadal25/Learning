<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EnglishClass;
use App\Models\EnglishGrade;
use App\Models\ExerciseAttempt;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use App\Services\StudentExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminStudentController extends Controller
{
    protected StudentExcelService $excelService;

    public function __construct(StudentExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Display unified student management across English, Japanese, and Math.
     */
    public function index(Request $request): View
    {
        $subjects = Subject::where('is_active', true)->orderBy('order', 'asc')->get();
        $selectedSubjectId = (int)$request->input('subject_id', 1);
        $currentSubject = Subject::find($selectedSubjectId) ?? $subjects->first();

        // Get classes for current subject
        $classes = EnglishClass::where('subject_id', $currentSubject->id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $selectedClass = $request->input('class_name');
        $selectedTeacherId = $request->input('teacher_id');

        // Query students enrolled or assigned to this subject
        $query = User::where('role', User::ROLE_STUDENT)
            ->forSubject($currentSubject->id)
            ->with(['enrollments.subject', 'enrollments.teacher', 'creator']);

        if (!empty($selectedClass) && $selectedClass !== 'all') {
            $query->where('class_name', $selectedClass);
        }

        // Filter by teacher for English students
        if ($currentSubject->id === 1 && !empty($selectedTeacherId)) {
            if ($selectedTeacherId === 'unassigned') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('enrollments', function ($eq) {
                        $eq->where('subject_id', 1)->whereNotNull('teacher_id');
                    })->whereNull('created_by');
                });
            } else {
                $tId = (int)$selectedTeacherId;
                $query->where(function ($q) use ($tId) {
                    $q->whereHas('enrollments', function ($eq) use ($tId) {
                        $eq->where('subject_id', 1)->where('teacher_id', $tId);
                    })->orWhere('created_by', $tId);
                });
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nrp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $students = $query->latest('id')->paginate(15)->withQueryString();

        // Count students per subject for badge indicators
        $subjectCounts = [];
        foreach ($subjects as $subj) {
            $subjectCounts[$subj->id] = User::where('role', User::ROLE_STUDENT)
                ->forSubject($subj->id)
                ->count();
        }

        // List English teachers
        $englishTeachers = User::where('role', User::ROLE_ADMIN)
            ->where(function ($q) {
                $q->where('subject_id', 1)->orWhereNull('subject_id');
            })
            ->orderBy('name', 'asc')
            ->get();

        // Count English students without assigned teacher
        $unassignedEnglishCount = 0;
        if ($currentSubject->id === 1) {
            $unassignedEnglishCount = User::where('role', User::ROLE_STUDENT)
                ->forSubject(1)
                ->where(function ($q) {
                    $q->whereDoesntHave('enrollments', function ($eq) {
                        $eq->where('subject_id', 1)->whereNotNull('teacher_id');
                    })->whereNull('created_by');
                })
                ->count();
        }

        // Check if default excel file exists in public/images/ (Bahasa Inggris Siswa.xlsx or inggris.xlsx)
        $defaultEnglishFileName = null;
        if (file_exists(public_path('images/Bahasa Inggris Siswa.xlsx'))) {
            $defaultEnglishFileName = 'Bahasa Inggris Siswa.xlsx';
        } elseif (file_exists(public_path('images/inggris.xlsx'))) {
            $defaultEnglishFileName = 'inggris.xlsx';
        }
        $hasDefaultEnglishFile = ($defaultEnglishFileName !== null);

        return view('superadmin.students.index', compact(
            'subjects',
            'currentSubject',
            'classes',
            'selectedClass',
            'selectedTeacherId',
            'students',
            'subjectCounts',
            'englishTeachers',
            'unassignedEnglishCount',
            'hasDefaultEnglishFile',
            'defaultEnglishFileName'
        ));
    }

    /**
     * Show form to manually create a new student.
     */
    public function create(Request $request): View
    {
        $subjects = Subject::where('is_active', true)->orderBy('order', 'asc')->get();
        $selectedSubjectId = (int)$request->input('subject_id', 1);
        $currentSubject = Subject::find($selectedSubjectId) ?? $subjects->first();

        $classes = EnglishClass::where('subject_id', $currentSubject->id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $englishTeachers = User::where('role', User::ROLE_ADMIN)
            ->where('subject_id', 1)
            ->orderBy('name', 'asc')
            ->get();

        return view('superadmin.students.create', compact('subjects', 'currentSubject', 'classes', 'englishTeachers'));
    }

    /**
     * Store manually created student into the platform.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'nrp' => ['nullable', 'string', 'max:50'],
            'division' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'email.unique' => 'Alamat email ini sudah terdaftar untuk pengguna lain.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $subjectId = (int)$validated['subject_id'];
        $subject = Subject::findOrFail($subjectId);
        $className = !empty($validated['class_name']) ? trim($validated['class_name']) : null;
        $name = trim($validated['name']);
        $nrp = !empty($validated['nrp']) ? trim($validated['nrp']) : null;
        $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', $nrp) : '';
        $defaultPassword = match($subjectId) {
            2 => ($cleanNrp ? "{$cleanNrp}@jpnmusashi" : 'password'),
            3 => ($cleanNrp ? "{$cleanNrp}@mtkmusashi" : 'password'),
            default => ($cleanNrp ? "{$cleanNrp}@musashi" : 'password'),
        };
        $plainPassword = !empty($validated['password']) ? trim($validated['password']) : $defaultPassword;

        $teacherId = (!empty($validated['teacher_id']) && $subjectId === 1) ? (int)$validated['teacher_id'] : null;
        $teacher = $teacherId ? User::find($teacherId) : null;

        // Auto-generate email if left blank, supporting same NRP across different subjects
        $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;
        if (!$email) {
            $subjSuffix = match($subjectId) {
                2 => '.jp',
                3 => '.mat',
                default => '',
            };

            if ($cleanNrp) {
                if ($subjectId !== 1 && !empty($subjSuffix)) {
                    $candidate = strtolower("{$cleanNrp}{$subjSuffix}@musashi.id");
                    if (!User::where('email', $candidate)->exists()) {
                        $email = $candidate;
                    } else {
                        $email = strtolower("{$cleanNrp}{$subjSuffix}@musashi.co.id");
                    }
                } else {
                    $baseCandidate = strtolower("{$cleanNrp}@musashi.id");
                    if (!User::where('email', $baseCandidate)->exists()) {
                        $email = $baseCandidate;
                    } else {
                        $email = strtolower("{$cleanNrp}@musashi.co.id");
                    }
                }
            } else {
                $slug = Str::slug($name, '.');
                $email = strtolower("{$slug}{$subjSuffix}@musashi.id");
            }

            // Ensure email is unique across the entire users table
            $originalEmail = $email;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $parts = explode('@', $originalEmail);
                $email = $parts[0] . '.' . $counter . '@' . ($parts[1] ?? 'musashi.id');
                $counter++;
            }
        }

        DB::transaction(function () use ($validated, $name, $nrp, $email, $plainPassword, $className, $subjectId, $subject, $teacher, $teacherId) {
            // Ensure class exists
            if ($className) {
                EnglishClass::firstOrCreate(
                    ['name' => $className, 'subject_id' => $subjectId],
                    ['is_active' => true, 'sort_order' => 99]
                );
            }

            $student = User::create([
                'name' => $name,
                'nrp' => $nrp,
                'division' => $validated['division'] ?? null,
                'class_name' => $className,
                'subject_id' => $subjectId,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_STUDENT,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'created_by' => $teacherId ?? Auth::id(),
            ]);

            // Enroll into subject with teacher if applicable
            $student->enrollInSubject($subject, $teacher, $className ?? 'REGULAR');

            // Initialize progress for all subjects
            $allSubjects = Subject::with('levels')->get();
            foreach ($allSubjects as $subj) {
                $firstLevel = $subj->levels->firstWhere('order', 1);
                if ($firstLevel) {
                    UserLevelStatus::firstOrCreate([
                        'user_id' => $student->id,
                        'level_id' => $firstLevel->id,
                    ], [
                        'points' => 0,
                        'is_unlocked' => true,
                        'is_completed' => false,
                    ]);

                    foreach ($subj->levels->where('order', '>', 1) as $higherLevel) {
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
                        'subject_id' => $subj->id,
                    ], [
                        'current_level_id' => $firstLevel->id,
                        'current_points' => 0,
                        'is_completed' => false,
                    ]);
                }
            }

            // Create EnglishGrade record if English
            if ($subjectId === 1 && $className) {
                EnglishGrade::firstOrCreate([
                    'student_id' => $student->id,
                    'class_name' => $className,
                    'week' => 1,
                ], [
                    'teacher_id' => $teacherId,
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

        $teacherMsg = $teacher ? " dengan Guru Pengajar: {$teacher->name}" : "";
        return redirect()->route('superadmin.students.index', ['subject_id' => $subjectId])
            ->with('success', "Akun siswa '{$name}' ({$email}) berhasil ditambahkan{$teacherMsg}.");
    }

    /**
     * Show form to edit student profile.
     */
    public function edit(User $student): View
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);

        $subjects = Subject::where('is_active', true)->orderBy('order', 'asc')->get();
        $classes = EnglishClass::where('is_active', true)
            ->where('subject_id', $student->subject_id ?? 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        $englishTeachers = User::where('role', User::ROLE_ADMIN)
            ->where('subject_id', 1)
            ->orderBy('name', 'asc')
            ->get();

        $currentTeacherId = $student->enrollments->firstWhere('subject_id', 1)?->teacher_id ?? $student->created_by;

        return view('superadmin.students.edit', compact('student', 'subjects', 'classes', 'englishTeachers', 'currentTeacherId'));
    }

    /**
     * Update student profile.
     */
    public function update(Request $request, User $student): RedirectResponse
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'nrp' => ['nullable', 'string', 'max:50'],
            'division' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $student->id],
            'password' => ['nullable', 'string', 'min:4'],
            'phone' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi minimal 4 karakter jika ingin diubah.',
        ]);

        $subjectId = (int)$validated['subject_id'];
        $subject = Subject::findOrFail($subjectId);
        $className = !empty($validated['class_name']) ? trim($validated['class_name']) : null;
        $teacherId = (!empty($validated['teacher_id']) && $subjectId === 1) ? (int)$validated['teacher_id'] : null;
        $teacher = $teacherId ? User::find($teacherId) : null;

        DB::transaction(function () use ($student, $validated, $subjectId, $subject, $className, $teacher, $teacherId) {
            if ($className) {
                EnglishClass::firstOrCreate(
                    ['name' => $className, 'subject_id' => $subjectId],
                    ['is_active' => true, 'sort_order' => 99]
                );
            }

            $updateData = [
                'name' => trim($validated['name']),
                'nrp' => !empty($validated['nrp']) ? trim($validated['nrp']) : null,
                'division' => $validated['division'] ?? null,
                'class_name' => $className,
                'subject_id' => $subjectId,
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ];

            if ($subjectId === 1 && $teacherId) {
                $updateData['created_by'] = $teacherId;
            }

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $student->update($updateData);

            // Sync enrollment with teacher
            $student->enrollInSubject($subject, $teacher, $className ?? 'REGULAR');

            if ($subjectId === 1) {
                EnglishGrade::where('student_id', $student->id)->update([
                    'teacher_id' => $teacherId,
                ]);

                if ($className) {
                    EnglishGrade::firstOrCreate([
                        'student_id' => $student->id,
                        'class_name' => $className,
                        'week' => 1,
                    ], [
                        'teacher_id' => $teacherId,
                    ]);
                }
            }
        });

        return redirect()->route('superadmin.students.index', ['subject_id' => $subjectId])
            ->with('success', "Data siswa '{$student->name}' berhasil diperbarui.");
    }

    /**
     * Delete a single student account.
     */
    public function destroy(User $student): RedirectResponse
    {
        abort_if($student->role !== User::ROLE_STUDENT, 404);
        $name = $student->name;
        $subjectId = $student->subject_id ?? 1;

        DB::transaction(function () use ($student) {
            $student->enrollments()->delete();
            UserLevelStatus::where('user_id', $student->id)->delete();
            UserProgress::where('user_id', $student->id)->delete();
            EnglishGrade::where('student_id', $student->id)->delete();
            Attendance::where('user_id', $student->id)->delete();
            ExerciseAttempt::where('user_id', $student->id)->delete();
            $student->delete();
        });

        return redirect()->route('superadmin.students.index', ['subject_id' => $subjectId])
            ->with('success', "Akun siswa '{$name}' berhasil dihapus dari sistem.");
    }

    /**
     * Delete ALL students (across all subjects or for a specific subject).
     */
    public function deleteAll(Request $request): RedirectResponse
    {
        $request->validate([
            'subject_id' => ['required'],
            'confirm_text' => ['required', 'string'],
        ], [
            'confirm_text.required' => 'Ketikkan kata HAPUS untuk mengonfirmasi penghapusan seluruh siswa.',
        ]);

        if (trim(strtoupper($request->input('confirm_text'))) !== 'HAPUS') {
            return back()->with('error', 'Konfirmasi dibatalkan: Teks konfirmasi harus tepat kata "HAPUS" dengan huruf kapital.');
        }

        $subjectId = $request->input('subject_id');
        $query = User::where('role', User::ROLE_STUDENT);

        if ($subjectId !== 'all') {
            $query->forSubject((int)$subjectId);
        }

        $studentIds = $query->pluck('id')->toArray();
        $totalDeleted = count($studentIds);

        if ($totalDeleted > 0) {
            DB::transaction(function () use ($studentIds) {
                StudentEnrollment::whereIn('student_id', $studentIds)->delete();
                UserLevelStatus::whereIn('user_id', $studentIds)->delete();
                UserProgress::whereIn('user_id', $studentIds)->delete();
                EnglishGrade::whereIn('student_id', $studentIds)->delete();
                Attendance::whereIn('user_id', $studentIds)->delete();
                ExerciseAttempt::whereIn('user_id', $studentIds)->delete();
                User::whereIn('id', $studentIds)->delete();
            });
        }

        $subjName = ($subjectId === 'all')
            ? 'seluruh mata pelajaran'
            : (Subject::find((int)$subjectId)?->name ?? 'mata pelajaran terpilih');

        return redirect()->route('superadmin.students.index', ['subject_id' => $subjectId === 'all' ? 1 : $subjectId])
            ->with('success', "Berhasil menghapus {$totalDeleted} siswa dari {$subjName}. Seluruh data nilai dan progres belajar telah dibersihkan.");
    }

    /**
     * Bulk assign an English teacher to English students.
     */
    public function assignTeacher(Request $request): RedirectResponse
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'assignment_scope' => ['required', 'in:all_unassigned,all_in_subject,by_class'],
            'class_name' => ['nullable', 'string'],
        ], [
            'teacher_id.required' => 'Silakan pilih guru pengajar Bahasa Inggris.',
        ]);

        $teacher = User::findOrFail($request->teacher_id);
        $subject = Subject::find(1);

        $query = User::where('role', User::ROLE_STUDENT)->forSubject(1);

        if ($request->assignment_scope === 'all_unassigned') {
            $query->where(function ($q) {
                $q->whereDoesntHave('enrollments', function ($eq) {
                    $eq->where('subject_id', 1)->whereNotNull('teacher_id');
                })->whereNull('created_by');
            });
        } elseif ($request->assignment_scope === 'by_class' && $request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }

        $students = $query->get();
        $assignedCount = 0;

        DB::transaction(function () use ($students, $teacher, $subject, &$assignedCount) {
            foreach ($students as $student) {
                $student->created_by = $teacher->id;
                $student->save();

                $student->enrollInSubject($subject, $teacher, $student->class_name ?? 'REGULAR');
                EnglishGrade::where('student_id', $student->id)->update(['teacher_id' => $teacher->id]);

                $assignedCount++;
            }
        });

        $scopeDesc = match ($request->assignment_scope) {
            'all_unassigned' => 'yang sebelumnya belum memiliki guru',
            'by_class' => "di kelas {$request->class_name}",
            default => 'Bahasa Inggris',
        };

        return redirect()->route('superadmin.students.index', ['subject_id' => 1])
            ->with('success', "Berhasil menugaskan {$teacher->name} sebagai guru pengajar untuk {$assignedCount} siswa {$scopeDesc}.");
    }

    /**
     * Import students from an uploaded Excel / CSV file.
     */
    public function importExcel(Request $request): RedirectResponse
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'excel_file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value->isValid()) {
                        $fail('File gagal diunggah: ' . $value->getErrorMessage());
                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['xlsx', 'csv', 'xls', 'txt'])) {
                        $fail('Format file harus berupa Excel (.xlsx) atau CSV (.csv). Ekstensi terdeteksi: .' . $ext);
                    }
                },
                'max:20480', // max 20MB
            ],
        ], [
            'subject_id.required' => 'Mata pelajaran tujuan wajib dipilih.',
            'excel_file.required' => 'Silakan pilih file Excel (.xlsx / .csv) yang akan diunggah.',
            'excel_file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        $subjectId = (int)$request->input('subject_id');
        $subject = Subject::findOrFail($subjectId);
        $teacherId = (!empty($request->input('teacher_id')) && $subjectId === 1) ? (int)$request->input('teacher_id') : null;
        $file = $request->file('excel_file');

        try {
            $rows = $this->excelService->parseFile($file->getRealPath(), $file->getClientOriginalExtension());

            if (empty($rows)) {
                return back()->with('error', 'Tidak ditemukan data siswa valid pada file Excel yang diunggah. Pastikan file memiliki baris data nama siswa.');
            }

            $result = $this->excelService->importStudents($rows, $subjectId, Auth::id(), $teacherId);

            $classesMsg = !empty($result['created_classes'])
                ? ' Kelas baru dibuat: ' . implode(', ', $result['created_classes']) . '.'
                : '';

            $teacher = $teacherId ? User::find($teacherId) : null;
            $teacherMsg = $teacher ? " (Guru Pengajar: {$teacher->name})" : '';

            $msg = "Import Excel Berhasil! {$result['imported']} siswa baru ditambahkan, {$result['updated']} siswa diperbarui untuk {$subject->name}{$teacherMsg}.{$classesMsg}";

            if (!empty($result['errors'])) {
                $errorDetails = implode('<br>', array_slice($result['errors'], 0, 5));
                return redirect()->route('superadmin.students.index', ['subject_id' => $subjectId])
                    ->with('success', $msg)
                    ->with('warning', 'Beberapa baris data mengalami kendala:<br>' . $errorDetails);
            }

            return redirect()->route('superadmin.students.index', ['subject_id' => $subjectId])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Direct one-click import from public/images/Bahasa Inggris Siswa.xlsx or inggris.xlsx.
     */
    public function importDefaultEnglish(Request $request): RedirectResponse
    {
        $fileName = 'Bahasa Inggris Siswa.xlsx';
        $filePath = public_path('images/' . $fileName);
        if (!file_exists($filePath)) {
            $fileName = 'inggris.xlsx';
            $filePath = public_path('images/' . $fileName);
        }

        if (!file_exists($filePath)) {
            return back()->with('error', 'File berkas siswa Bahasa Inggris tidak ditemukan di public/images/.');
        }

        $teacherId = $request->filled('teacher_id') ? (int)$request->input('teacher_id') : null;
        $teacher = $teacherId ? User::find($teacherId) : null;

        try {
            $rows = $this->excelService->parseFile($filePath);
            $result = $this->excelService->importStudents($rows, 1, Auth::id(), $teacherId);

            $teacherMsg = $teacher ? " dengan Guru Pengajar: {$teacher->name}" : "";
            $totalCount = $result['imported'] + $result['updated'];
            $msg = "Berhasil memproses {$totalCount} data siswa Bahasa Inggris dari file {$fileName}! ({$result['imported']} siswa baru ditambahkan, {$result['updated']} data siswa diperbarui){$teacherMsg}.";

            return redirect()->route('superadmin.students.index', ['subject_id' => 1])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    /**
     * Download sample Excel template for Super Admin.
     */
    public function downloadTemplate(Request $request): Response
    {
        $subjectId = (int)$request->input('subject_id', 1);
        $subject = Subject::find($subjectId) ?? Subject::first();

        $filename = "template_siswa_" . Str::slug($subject->name) . ".csv";

        $sampleClass = match ($subjectId) {
            2 => 'Grup 1',
            3 => 'Kelas Matematika Dasar',
            default => 'I1',
        };

        $sampleClass2 = match ($subjectId) {
            2 => 'Grup 2',
            3 => 'Kelas Matematika Terapan',
            default => 'I2',
        };

        $csvRows = [
            ['KELAS', 'NRP', 'NAMA PESERTA', 'BAGIAN', 'EMAIL', 'PASSWORD'],
            [$sampleClass, '1001', 'BUDI SANTOSO', 'INFORMATION TECHNOLOGY', '1001@musashi.co.id', '1001@musashi'],
            [$sampleClass2, '1002', 'SITI RAHMAWATI', 'QUALITY CONTROL', '1002@musashi.co.id', '1002@musashi'],
            [$sampleClass, '1003', 'AHMAD FAUZI', 'PRODUCTION CONTROL', '', ''],
        ];

        $output = fopen('php://memory', 'w');
        // Add UTF-8 BOM so Excel opens it with proper characters
        fputs($output, "\xEF\xBB\xBF");

        foreach ($csvRows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
