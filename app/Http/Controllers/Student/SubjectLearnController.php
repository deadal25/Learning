<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubjectLearnController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $enrolledSubjectIds = $user->enrollments()->pluck('subject_id')->toArray();

        if (empty($enrolledSubjectIds)) {
            return redirect()->route('student.enroll.index')
                ->with('info', 'Anda belum memiliki mata pelajaran aktif. Silakan masukkan Kode Kelas dari guru Anda.');
        }

        $subjects = Subject::with([
                'levels.materials' => fn($mq) => $mq->where('is_active', true),
                'levels.exercises'
            ])
            ->whereIn('id', $enrolledSubjectIds)
            ->orderBy('order', 'asc')
            ->get();

        $userProgressMap = UserProgress::where('user_id', $user->id)
            ->pluck('current_level_id', 'subject_id')
            ->toArray();

        $userPointsMap = UserProgress::where('user_id', $user->id)
            ->pluck('current_points', 'subject_id')
            ->toArray();

        $allSubjectsCount = Subject::where('is_active', true)->count();

        return view('student.subjects.index', compact('subjects', 'userProgressMap', 'userPointsMap', 'allSubjectsCount'));
    }

    public function show(Subject $subject): View|RedirectResponse
    {
        $user = Auth::user();

        // Enforce subject enrollment
        if (!$user->isEnrolledIn($subject->id)) {
            return redirect()->route('student.enroll.index')
                ->with('error', "Mata pelajaran {$subject->name} belum Anda aktifkan. Silakan masukkan Kode Kelas dari guru yang bersangkutan untuk mulai belajar.");
        }

        $subject->load(['levels' => function ($q) use ($user) {
            $q->with([
                'materials' => function ($mq) use ($user) {
                    $mq->where('is_active', true);
                    if (!empty($user->class_name)) {
                        $mq->forStudentClass($user->class_name);
                    }
                },
                'exercises'
            ])->orderBy('order', 'asc');
        }]);

        $unlockedLevelIds = UserLevelStatus::where('user_id', $user->id)
            ->where('is_unlocked', true)
            ->pluck('level_id')
            ->toArray();

        $completedLevelIds = UserLevelStatus::where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('level_id')
            ->toArray();

        $levelScores = UserLevelStatus::where('user_id', $user->id)
            ->pluck('points', 'level_id')
            ->toArray();

        $userProgress = UserProgress::where('user_id', $user->id)
            ->where('subject_id', $subject->id)
            ->first();

        return view('student.subjects.show', compact(
            'subject',
            'unlockedLevelIds',
            'completedLevelIds',
            'levelScores',
            'userProgress'
        ));
    }

    public function materialsIndex(\Illuminate\Http\Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $activeSubjectId = (int)(session('active_subject_id') ?? $user->subject_id ?? 1);

        $subjects = Subject::where('id', $activeSubjectId)->get();
        $currentSubject = $subjects->first() ?? Subject::find($activeSubjectId) ?? Subject::find(1);

        $query = Material::where('is_active', true)
            ->whereHas('level', function ($q) use ($activeSubjectId) {
                $q->where('subject_id', $activeSubjectId);
            })
            ->with(['level.subject'])
            ->orderBy('order', 'asc');

        // SCOPING KELAS & GRUP: Siswa mengakses materi sesuai kelas spesifik, grup huruf (I/B/E), atau materi umum
        if (!empty($user->class_name)) {
            $query->forStudentClass($user->class_name);
        }

        if ($levelId = $request->input('level_id')) {
            $query->where('level_id', $levelId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $materials = $query->paginate(12)->withQueryString();

        return view('student.materials.index', compact('materials', 'subjects', 'currentSubject', 'activeSubjectId'));
    }

    public function viewMaterial(Material $material): View|RedirectResponse
    {
        $user = Auth::user();
        $material->load('level.subject');
        $level = $material->level;

        // Check if material is active
        if (!$material->is_active) {
            return redirect()->route('student.materials.index')
                ->with('error', 'Materi ini sedang dinonaktifkan oleh guru pengajar dan belum dapat diakses.');
        }

        // Check if student enrolled in this subject
        if (!$user->isEnrolledIn($level->subject_id)) {
            return redirect()->route('student.dashboard')
                ->with('error', "Materi ini milik mata pelajaran {$level->subject->name} yang belum Anda aktifkan.");
        }

        // Set or update active subject in session seamlessly
        session(['active_subject_id' => $level->subject_id]);

        // Check class restriction: if material is assigned to a different class/group
        if (!$material->isAccessibleByClass($user->class_name)) {
            return redirect()->route('student.materials.index')
                ->with('error', "Materi ini hanya dapat diakses oleh siswa di {$material->formatted_class_label}. Anda terdaftar di {$user->class_name}.");
        }

        // Check if student has unlocked the exercise level (for info only)
        $status = UserLevelStatus::where('user_id', $user->id)
            ->where('level_id', $level->id)
            ->first();

        $isLevelUnlocked = (bool) ($status?->is_unlocked ?? ($level->order === 1));

        $otherMaterialsQuery = Material::where('level_id', $level->id)
            ->where('id', '!=', $material->id)
            ->where('is_active', true);

        if (!empty($user->class_name)) {
            $otherMaterialsQuery->forStudentClass($user->class_name);
        }

        $otherMaterials = $otherMaterialsQuery->orderBy('order', 'asc')->get();

        $presentationData = \App\Services\DocumentConverterService::ensureConverted($material);

        // Hubungkan latihan soal langsung dengan Pertemuan ($level->order) dari materi ini
        $jpTest = null;
        if ((int)$level->subject_id === 2) {
            $jpTest = \App\Models\JapaneseTest::where('category', 'per_pertemuan')
                ->where('start_meeting', $level->order)
                ->first();
        }

        $exerciseUrl = $jpTest
            ? route('student.japanese.tests.show', $jpTest)
            : route('student.exercises.show', $level);

        $exerciseCount = $level->exercises()->count();
        if ($exerciseCount === 0 && $jpTest) {
            $exerciseCount = $jpTest->questions()->count();
        }

        // Resolusi kelompok kelas siswa (Isolasi komentar per pertemuan & kelas)
        $studentClassGroup = \App\Models\MeetingComment::resolveClassGroup(
            $user->class_name ?: $material->class_name,
            $level->subject_id
        );

        $meetingComments = \App\Models\MeetingComment::where('level_id', $level->id)
            ->where('class_group', $studentClassGroup)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        $commentsCount = \App\Models\MeetingComment::where('level_id', $level->id)
            ->where('class_group', $studentClassGroup)
            ->count();

        $averageRating = $meetingComments->whereNotNull('rating')->avg('rating');

        return view('student.materials.view', compact(
            'material',
            'level',
            'isLevelUnlocked',
            'otherMaterials',
            'presentationData',
            'exerciseUrl',
            'exerciseCount',
            'jpTest',
            'studentClassGroup',
            'meetingComments',
            'commentsCount',
            'averageRating'
        ));
    }

    public function downloadMaterial(Material $material): BinaryFileResponse|RedirectResponse
    {
        $user = Auth::user();

        // Check if material is active
        if (!$material->is_active) {
            return redirect()->route('student.materials.index')
                ->with('error', 'Materi ini sedang dinonaktifkan oleh guru pengajar dan tidak dapat diunduh.');
        }

        if (!$user->isEnrolledIn($material->level->subject_id)) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Akses unduh materi terkunci karena Anda belum terdaftar di mata pelajaran ini.');
        }

        session(['active_subject_id' => $material->level->subject_id]);

        // Check class restriction: letter groups and specific classes
        if (!$material->isAccessibleByClass($user->class_name)) {
            return redirect()->route('student.materials.index')
                ->with('error', "File ini hanya dapat diunduh oleh siswa di {$material->formatted_class_label}. Anda terdaftar di {$user->class_name}.");
        }

        if (!$material->file_path) {
            return back()->with('error', 'Materi ini tidak memiliki file lampiran fisik untuk diunduh.');
        }

        $path = \App\Services\DocumentConverterService::resolveFilePath($material->file_path);
        if (!$path) {
            return back()->with('error', 'File slide atau dokumen materi belum tersedia di server.');
        }

        $downloadName = $material->file_name ?? ($material->title . '.' . ($material->file_type ?? 'pdf'));

        return response()->download($path, $downloadName);
    }

    /**
     * Preview material file safely inline in the browser without dumping raw binary codes.
     */
    public function previewMaterial(Material $material): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        $user = Auth::user();

        // Check if material is active
        if (!$material->is_active) {
            return redirect()->route('student.materials.index')
                ->with('error', 'Materi ini sedang dinonaktifkan oleh guru pengajar.');
        }

        if (!$user->isEnrolledIn($material->level->subject_id)) {
            return redirect()->route('student.enroll.index')
                ->with('error', 'Akses pratinjau materi terkunci karena Anda belum terdaftar di mata pelajaran ini.');
        }

        session(['active_subject_id' => $material->level->subject_id]);

        if (!$material->file_path) {
            return redirect()->route('student.materials.view', $material);
        }

        // 1. PDF File: Stream inline as application/pdf
        if ($material->isPdf()) {
            $absPath = \App\Services\DocumentConverterService::resolveFilePath($material->file_path);
            if ($absPath) {
                return response()->file($absPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . addslashes($material->file_name ?? 'materi.pdf') . '"',
                ]);
            }
        }

        // 2. PPT File: Stream converted PDF inline or redirect to student materials view
        if ($material->isPpt()) {
            $pdfPath = \App\Services\DocumentConverterService::getPdfPath($material);
            if ($pdfPath && file_exists($pdfPath)) {
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . addslashes(pathinfo($material->file_name ?? 'slide.pdf', PATHINFO_FILENAME)) . '.pdf"',
                ]);
            }

            return redirect()->route('student.materials.view', $material);
        }

        // 3. Image File
        if ($material->isImage()) {
            $absPath = \App\Services\DocumentConverterService::resolveFilePath($material->file_path);
            if ($absPath) {
                return response()->file($absPath);
            }
        }

        return redirect()->route('student.materials.view', $material);
    }
}

