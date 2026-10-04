<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Show the course activation / enrollment catalog.
     */
    public function index(): View
    {
        $user = Auth::user();

        // All active subjects with their assigned teachers
        $subjects = Subject::with(['levels'])->where('is_active', true)->orderBy('order', 'asc')->get();

        // Get teachers for each subject
        $teachers = User::where('role', User::ROLE_ADMIN)
            ->whereNotNull('subject_id')
            ->get()
            ->keyBy('subject_id');

        // Student's active enrollments
        $enrollments = $user->enrollments()->with(['subject', 'teacher'])->get()->keyBy('subject_id');

        return view('student.enrollment.index', compact('subjects', 'teachers', 'enrollments', 'user'));
    }

    /**
     * Redeem class code to enroll/buy a subject.
     */
    public function enroll(Request $request): RedirectResponse
    {
        $request->validate([
            'class_code' => ['required', 'string', 'max:50'],
        ], [
            'class_code.required' => 'Silakan masukkan Kode Kelas dari guru mata pelajaran.',
        ]);

        $code = strtoupper(trim($request->input('class_code')));
        $user = Auth::user();

        // Find teacher by class_code
        $teacher = User::where('role', User::ROLE_ADMIN)
            ->whereRaw('UPPER(class_code) = ?', [$code])
            ->first();

        if (!$teacher || !$teacher->subject_id) {
            return back()->with('error', "Kode kelas '{$code}' tidak valid atau belum terdaftar pada pengajar mana pun. Silakan hubungi guru Anda untuk mendapatkan kode kelas yang benar.");
        }

        $subject = $teacher->subject;
        if (!$subject) {
            return back()->with('error', 'Mata pelajaran yang terhubung dengan kode kelas ini tidak aktif.');
        }

        // Check if student already enrolled in this subject
        if ($user->isEnrolledIn($subject->id)) {
            return redirect()->route('student.subjects.show', $subject)
                ->with('info', "Anda sudah terdaftar dan aktif di mata pelajaran {$subject->name}.");
        }

        // Enroll student
        $user->enrollInSubject($subject, $teacher, $code);

        return redirect()->route('student.subjects.show', $subject)
            ->with('success', "🎉 Selamat! Anda berhasil mengaktifkan mata pelajaran {$subject->name} bersama {$teacher->name} (Kode: {$code}). Selamat belajar!");
    }
}
