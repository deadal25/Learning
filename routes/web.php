<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\EnglishClassProfileController;
use App\Http\Controllers\Admin\EnglishGradeController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentManagementController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\ExercisePlayController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\SubjectLearnController;
use App\Http\Controllers\SuperAdmin\AdminManagementController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminAttendanceController;
use App\Http\Controllers\SuperAdmin\SuperAdminQuestionController;
use App\Http\Controllers\SuperAdmin\SuperAdminStudentController;
use App\Http\Controllers\Admin\JapaneseTestController;
use App\Http\Controllers\Admin\MeetingManagementController;
use App\Http\Controllers\Student\JapaneseTestStudentController;
use App\Http\Controllers\Student\StudentProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global attendance submission for logged-in user
    Route::post('/attendance', [AttendanceController::class, 'submit'])->name('attendance.submit');

    // 1. Super Admin Portal
    Route::middleware('role:super_admin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('admins', AdminManagementController::class)->except(['show']);

        // Kelola Siswa Super Admin (Bahasa Inggris, Bahasa Jepang, Matematika)
        Route::get('/students', [SuperAdminStudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [SuperAdminStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [SuperAdminStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [SuperAdminStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [SuperAdminStudentController::class, 'update'])->name('students.update');
        Route::delete('/students/delete-all', [SuperAdminStudentController::class, 'deleteAll'])->name('students.delete-all');
        Route::delete('/students/{student}', [SuperAdminStudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/assign-teacher', [SuperAdminStudentController::class, 'assignTeacher'])->name('students.assign-teacher');
        Route::post('/students/import-excel', [SuperAdminStudentController::class, 'importExcel'])->name('students.import-excel');
        Route::post('/students/import-default-english', [SuperAdminStudentController::class, 'importDefaultEnglish'])->name('students.import-default-english');
        Route::get('/students/download-template', [SuperAdminStudentController::class, 'downloadTemplate'])->name('students.download-template');

        // Kelola Soal (Bahasa Inggris, Bahasa Jepang, Matematika)
        Route::get('/questions', [SuperAdminQuestionController::class, 'index'])->name('questions.index');
        Route::post('/questions', [SuperAdminQuestionController::class, 'store'])->name('questions.store');
        Route::put('/questions/{id}', [SuperAdminQuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{id}', [SuperAdminQuestionController::class, 'destroy'])->name('questions.destroy');
        Route::post('/questions/levels/{level}/toggle-active', [SuperAdminQuestionController::class, 'toggleLevelActive'])->name('questions.levels.toggle-active');
        Route::post('/questions/levels/toggle-all', [SuperAdminQuestionController::class, 'toggleAllLevels'])->name('questions.levels.toggle-all');
        Route::post('/questions/japanese/{test}/toggle-active', [SuperAdminQuestionController::class, 'toggleJapaneseActive'])->name('questions.japanese.toggle-active');
        Route::post('/questions/japanese/toggle-all', [SuperAdminQuestionController::class, 'toggleAllJapanese'])->name('questions.japanese.toggle-all');
        Route::put('/questions/japanese/{test}/update-test', [SuperAdminQuestionController::class, 'updateJapaneseTest'])->name('questions.japanese.update-test');

        // Riwayat Absensi Super Admin (Guru & Siswa per 3 Mapel)
        Route::get('/attendance', [SuperAdminAttendanceController::class, 'index'])->name('attendance.index');
    });

    // 2. Admin (Guru/Miss) Portal - accessible by Admin and Super Admin
    Route::middleware('role:super_admin,admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Student management (no self-register)
        Route::resource('students', StudentManagementController::class);

        // Subject & Level management
        Route::resource('subjects', SubjectController::class);
        Route::resource('subjects.levels', LevelController::class)->except(['show']);

        // Learning Materials CRUD (PPT, PDF, Slide URLs)
        Route::resource('materials', MaterialController::class);
        Route::get('materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
        Route::get('materials/{material}/preview', [MaterialController::class, 'preview'])->name('materials.preview');
        Route::post('materials/{material}/reconvert', [MaterialController::class, 'reconvert'])->name('materials.reconvert');
        Route::post('materials/{material}/toggle-active', [MaterialController::class, 'toggleActive'])->name('materials.toggle-active');

        // Exercise Questions CRUD (10 per level)
        Route::resource('exercises', ExerciseController::class)->except(['show']);

        // Progress reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Attendance Management & Rekap Siswa
        Route::get('/attendance', [AttendanceController::class, 'adminIndex'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'adminStore'])->name('attendance.store');

        // English Class Management & Teacher Profile
        Route::get('/classes', [EnglishClassProfileController::class, 'index'])->name('classes.index');
        Route::post('/classes', [EnglishClassProfileController::class, 'storeClass'])->name('classes.store');
        Route::put('/classes/{class}', [EnglishClassProfileController::class, 'updateClass'])->name('classes.update');
        Route::delete('/classes/{class}', [EnglishClassProfileController::class, 'destroyClass'])->name('classes.destroy');
        Route::put('/profile', [EnglishClassProfileController::class, 'updateProfile'])->name('profile.update');

        // Meeting Management (Kelola Pertemuan untuk Jepang, Inggris, Matematika)
        Route::get('/meetings', [MeetingManagementController::class, 'index'])->name('meetings.index');
        Route::post('/meetings', [MeetingManagementController::class, 'store'])->name('meetings.store');
        Route::put('/meetings/{meeting}', [MeetingManagementController::class, 'update'])->name('meetings.update');
        Route::delete('/meetings/{meeting}', [MeetingManagementController::class, 'destroy'])->name('meetings.destroy');

        // English Grades & Feedback Management (Kelola Nilai)
        Route::get('/grades', [EnglishGradeController::class, 'index'])->name('grades.index');
        Route::post('/grades/save', [EnglishGradeController::class, 'save'])->name('grades.save');
        Route::get('/grades/export', [EnglishGradeController::class, 'export'])->name('grades.export');

        // Japanese Periodic Tests Management (Guru Jepang & Super Admin)
        Route::get('/japanese-tests', [JapaneseTestController::class, 'index'])->name('japanese.tests.index');
        Route::post('/japanese-tests/toggle-all', [JapaneseTestController::class, 'toggleAll'])->name('japanese.tests.toggle-all');
        Route::put('/japanese-tests/{test}', [JapaneseTestController::class, 'updateTest'])->name('japanese.tests.update');
        Route::post('/japanese-tests/{test}/toggle-active', [JapaneseTestController::class, 'toggleActive'])->name('japanese.tests.toggle-active');
        Route::get('/japanese-tests/{test}/questions', [JapaneseTestController::class, 'questions'])->name('japanese.tests.questions');
        Route::post('/japanese-tests/{test}/questions', [JapaneseTestController::class, 'storeQuestion'])->name('japanese.tests.questions.store');
        Route::put('/japanese-test-questions/{question}', [JapaneseTestController::class, 'updateQuestion'])->name('japanese.tests.questions.update');
        Route::delete('/japanese-test-questions/{question}', [JapaneseTestController::class, 'destroyQuestion'])->name('japanese.tests.questions.destroy');
    });

    // 3. Student (Pelajar) Portal
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        
        // Enrollment / Ambil & Beli Kelas Menggunakan Kode Kelas Guru
        Route::get('/enroll', [EnrollmentController::class, 'index'])->name('enroll.index');
        Route::post('/enroll', [EnrollmentController::class, 'enroll'])->name('enroll.submit');

        // Subjects & Materials
        Route::get('/subjects', [SubjectLearnController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/{subject}', [SubjectLearnController::class, 'show'])->name('subjects.show');
        Route::get('/materials', [SubjectLearnController::class, 'materialsIndex'])->name('materials.index');
        Route::get('/materials/{material}', [SubjectLearnController::class, 'viewMaterial'])->name('materials.view');
        Route::get('/materials/{material}/preview', [SubjectLearnController::class, 'previewMaterial'])->name('materials.preview');
        Route::get('/materials/{material}/download', [SubjectLearnController::class, 'downloadMaterial'])->name('materials.download');

        // Attendance History & Widget
        Route::get('/attendance', [AttendanceController::class, 'history'])->name('attendance.index');

        // English Student Grades & Feedback
        Route::get('/grades', [EnglishGradeController::class, 'studentGrades'])->name('grades.index');

        // Japanese Periodic Tests & Grade History (Khusus Kelas Jepang)
        Route::get('/japanese-tests', [JapaneseTestStudentController::class, 'index'])->name('japanese.tests.index');
        Route::get('/japanese-tests/{test}', [JapaneseTestStudentController::class, 'show'])->name('japanese.tests.show');
        Route::post('/japanese-tests/{test}/submit', [JapaneseTestStudentController::class, 'submit'])->name('japanese.tests.submit');
        Route::get('/japanese-grades', [JapaneseTestStudentController::class, 'gradeHistory'])->name('japanese.grades.index');

        // Exercises & Level Progression (Latihan Soal)
        Route::get('/exercises', [ExercisePlayController::class, 'index'])->name('exercises.index');
        Route::get('/levels/{level}/exercises', [ExercisePlayController::class, 'show'])->name('exercises.show');
        Route::post('/levels/{level}/exercises/submit', [ExercisePlayController::class, 'submit'])->name('exercises.submit');
        Route::post('/levels/{level}/unlock-next', [ExercisePlayController::class, 'unlockNextLevel'])->name('exercises.unlock-next');

        // Student Profile (Lihat Data & Ganti Email / Password)
        Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    });
});
