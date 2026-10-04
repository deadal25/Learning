<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnglishClass;
use App\Models\Level;
use App\Models\Material;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();
        $selectedClass = $request->input('class_name');

        $classesQuery = EnglishClass::where('is_active', true)->orderBy('sort_order');
        if ($isTeacher && $user->subject_id) {
            $classesQuery->where('subject_id', $user->subject_id);
        } elseif ($subjectId = $request->input('subject_id')) {
            $classesQuery->where('subject_id', $subjectId);
        }
        $classes = $classesQuery->get();

        $subjects = $isTeacher && $user->subject_id
            ? Subject::where('id', $user->subject_id)->with('levels')->get()
            : Subject::with('levels')->get();

        $query = Material::with('level.subject');

        if ($isTeacher && $user->subject_id) {
            $query->whereHas('level', function ($q) use ($user) {
                $q->where('subject_id', $user->subject_id);
            });
            // Khusus guru bahasa inggris: isolasi materi antar sesama guru bahasa inggris
            if ((int)$user->subject_id === 1) {
                $query->where('teacher_id', $user->id);
            }
        } elseif ($subjectId = $request->input('subject_id')) {
            $query->whereHas('level', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        if (!empty($selectedClass) && $selectedClass !== 'all') {
            $prefix = strtoupper(trim($selectedClass));
            if (in_array($prefix, ['I', 'B', 'E'])) {
                $query->where(function ($cq) use ($prefix) {
                    $cq->where('class_name', $prefix)
                       ->orWhere('class_name', 'like', $prefix . '%')
                       ->orWhere('class_name', 'like', 'Grup ' . $prefix . '%')
                       ->orWhere('class_name', 'like', 'Kelas ' . $prefix . '%');
                });
            } else {
                $query->where('class_name', $selectedClass);
            }
        }

        if ($levelId = $request->input('level_id')) {
            $query->where('level_id', $levelId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $selectedStatus = $request->input('status');
        if ($selectedStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($selectedStatus === 'inactive') {
            $query->where('is_active', false);
        }

        $teacherLevels = $isTeacher && $subjects->isNotEmpty() ? $subjects->first()->levels : collect();

        $materials = $query->latest()->paginate(15)->withQueryString();

        return view('admin.materials.index', compact('materials', 'subjects', 'classes', 'selectedClass', 'selectedStatus', 'isTeacher', 'teacherLevels'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $classesQuery = EnglishClass::where('is_active', true)->orderBy('sort_order');
        if ($isTeacher && $user->subject_id) {
            $classesQuery->where('subject_id', $user->subject_id);
        }
        $classes = $classesQuery->get();

        $subjects = $isTeacher && $user->subject_id
            ? Subject::where('id', $user->subject_id)->with('levels')->get()
            : Subject::with('levels')->get();

        return view('admin.materials.create', compact('subjects', 'classes', 'isTeacher', 'user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $validated = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'slide_file' => [
                'nullable',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value->isValid()) {
                        $fail('File gagal diunggah: ' . $value->getErrorMessage());
                        return;
                    }
                    $allowed = ['ppt', 'pptx', 'pdf', 'pps', 'ppsx', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'webp'];
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowed)) {
                        $fail('Format file materi harus berupa PPT, PPTX, PDF, DOC, DOCX, TXT, atau gambar (ekstensi file Anda: .' . ($ext ?: 'tidak terdeteksi') . ').');
                    }
                },
                'max:51200', // max 50MB
            ],
            'slide_url' => ['nullable', 'url', 'max:500'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'level_id.required' => 'Tingkatan/Level wajib dipilih.',
            'title.required' => 'Judul materi wajib diisi.',
            'slide_file.max' => 'Ukuran file materi maksimal 50 MB.',
            'slide_url.url' => 'Format URL slide/embed tidak valid.',
        ]);

        if ($isTeacher && $user->subject_id) {
            $level = Level::findOrFail($validated['level_id']);
            abort_if($level->subject_id !== $user->subject_id, 403, 'Anda hanya dapat menambahkan materi untuk mata pelajaran Anda.');
        }

        $filePath = null;
        $fileName = null;
        $fileType = null;

        if ($request->hasFile('slide_file')) {
            $file = $request->file('slide_file');
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            if (empty($ext)) {
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'pptx';
            }
            $storedFilename = \Illuminate\Support\Str::random(40) . '.' . $ext;
            $savedPath = $file->storeAs('materials', $storedFilename, 'public');

            $filePath = $savedPath;
            $fileName = $originalName;
            $fileType = $ext;
        } elseif (!empty($validated['slide_url'])) {
            $fileType = 'slide_url';
        }

        $targetClass = !empty($validated['class_name']) ? trim($validated['class_name']) : null;

        $material = Material::create([
            'level_id' => $validated['level_id'],
            'teacher_id' => $user->id,
            'class_name' => $targetClass,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'slide_url' => $validated['slide_url'] ?? null,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        if ($material->file_path) {
            \App\Services\DocumentConverterService::ensureConverted($material);
        }

        return redirect()->route('admin.materials.index')
            ->with('success', 'Materi presentasi/slide berhasil diunggah.');
    }

    public function show(Material $material): View
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();
        if ($isTeacher && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403, 'Anda tidak berhak melihat materi ini.');
            if ((int)$user->subject_id === 1 && $material->teacher_id && $material->teacher_id !== $user->id) {
                abort(403, 'Anda tidak berhak melihat materi milik guru lain.');
            }
        }

        $material->load('level.subject');
        $level = $material->level;

        $presentationData = \App\Services\DocumentConverterService::ensureConverted($material);

        return view('admin.materials.show', compact('material', 'level', 'presentationData'));
    }

    public function edit(Material $material): View
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403, 'Anda tidak berhak mengedit materi ini.');
            if ((int)$user->subject_id === 1 && $material->teacher_id && $material->teacher_id !== $user->id) {
                abort(403, 'Anda tidak berhak mengedit materi milik guru lain.');
            }
            $subjects = Subject::where('id', $user->subject_id)->with('levels')->get();
        } else {
            $subjects = Subject::with('levels')->get();
        }

        $classesQuery = EnglishClass::where('is_active', true)->orderBy('sort_order');
        if ($isTeacher && $user->subject_id) {
            $classesQuery->where('subject_id', $user->subject_id);
        } elseif ($material->level && $material->level->subject_id) {
            $classesQuery->where('subject_id', $material->level->subject_id);
        }
        $classes = $classesQuery->get();

        return view('admin.materials.edit', compact('material', 'subjects', 'classes', 'isTeacher', 'user'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403);
            if ((int)$user->subject_id === 1 && $material->teacher_id && $material->teacher_id !== $user->id) {
                abort(403, 'Anda tidak berhak memperbarui materi milik guru lain.');
            }
        }

        $validated = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'slide_file' => [
                'nullable',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value->isValid()) {
                        $fail('File gagal diunggah: ' . $value->getErrorMessage());
                        return;
                    }
                    $allowed = ['ppt', 'pptx', 'pdf', 'pps', 'ppsx', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'webp'];
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowed)) {
                        $fail('Format file materi harus berupa PPT, PPTX, PDF, DOC, DOCX, TXT, atau gambar (ekstensi file Anda: .' . ($ext ?: 'tidak terdeteksi') . ').');
                    }
                },
                'max:51200',
            ],
            'slide_url' => ['nullable', 'url', 'max:500'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'level_id.required' => 'Tingkatan/Level wajib dipilih.',
            'title.required' => 'Judul materi wajib diisi.',
            'slide_file.max' => 'Ukuran file materi maksimal 50 MB.',
        ]);

        if ($isTeacher && $user->subject_id) {
            $level = Level::findOrFail($validated['level_id']);
            abort_if($level->subject_id !== $user->subject_id, 403);
        }

        $filePath = $material->file_path;
        $fileName = $material->file_name;
        $fileType = $material->file_type;

        if ($request->hasFile('slide_file')) {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('slide_file');
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            if (empty($ext)) {
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'pptx';
            }
            $storedFilename = \Illuminate\Support\Str::random(40) . '.' . $ext;
            $savedPath = $file->storeAs('materials', $storedFilename, 'public');

            $filePath = $savedPath;
            $fileName = $originalName;
            $fileType = $ext;
        }

        $targetClass = !empty($validated['class_name']) ? trim($validated['class_name']) : null;

        $material->update([
            'level_id' => $validated['level_id'],
            'class_name' => $targetClass,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType ?? ($validated['slide_url'] ? 'slide_url' : null),
            'slide_url' => $validated['slide_url'] ?? null,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $material->is_active,
        ]);

        if ($request->hasFile('slide_file')) {
            // Delete old slides
            Storage::disk('public')->deleteDirectory("materials/slides_{$material->id}");
            Storage::disk('public')->delete("materials/converted_{$material->id}.pdf");
            \App\Services\DocumentConverterService::ensureConverted($material);
        }

        return redirect()->route('admin.materials.index')
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function toggleActive(Material $material): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403, 'Anda tidak berhak mengubah status materi mata pelajaran lain.');
            if ((int)$user->subject_id === 1 && $material->teacher_id && $material->teacher_id !== $user->id) {
                abort(403, 'Anda tidak berhak mengubah status materi milik guru lain.');
            }
        }

        $material->is_active = !$material->is_active;
        $material->save();

        $statusText = $material->is_active
            ? 'DIAKTIFKAN (siswa kelas/grup terkait sekarang dapat melihat materi ini)'
            : 'DINONAKTIFKAN (materi sekarang disembunyikan dari siswa)';

        return back()->with('success', "Materi '{$material->title}' berhasil {$statusText}.");
    }

    public function destroy(Material $material): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();
        if ($isTeacher && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403);
            if ((int)$user->subject_id === 1 && $material->teacher_id && $material->teacher_id !== $user->id) {
                abort(403, 'Anda tidak berhak menghapus materi milik guru lain.');
            }
        }

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        Storage::disk('public')->deleteDirectory("materials/slides_{$material->id}");
        Storage::disk('public')->delete("materials/converted_{$material->id}.pdf");

        $material->delete();

        return redirect()->route('admin.materials.index')
            ->with('success', 'Materi berhasil dihapus.');
    }

    public function download(Material $material): BinaryFileResponse|RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();
        if ($isTeacher && (int)$user->subject_id === 1 && $material->teacher_id && $material->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengunduh materi milik guru lain.');
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            return back()->with('error', 'File materi tidak ditemukan di penyimpanan server.');
        }

        return response()->download(
            Storage::disk('public')->path($material->file_path),
            $material->file_name ?? 'materi_musashi.' . ($material->file_type ?? 'pptx')
        );
    }

    /**
     * Preview material file safely inline in the browser without dumping raw binary codes.
     */
    public function preview(Material $material): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403);
        }

        if (!$material->file_path) {
            return redirect()->route('admin.materials.show', $material);
        }

        // 1. PDF File: Stream inline as application/pdf
        if ($material->isPdf()) {
            $absPath = Storage::disk('public')->path($material->file_path);
            if (file_exists($absPath)) {
                return response()->file($absPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . addslashes($material->file_name ?? 'materi.pdf') . '"',
                ]);
            }
        }

        // 2. PPT File: Serve converted PDF inline, or redirect to slide show
        if ($material->isPpt()) {
            $pdfPath = \App\Services\DocumentConverterService::getPdfPath($material);
            if ($pdfPath && file_exists($pdfPath)) {
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . addslashes(pathinfo($material->file_name ?? 'slide.pdf', PATHINFO_FILENAME)) . '.pdf"',
                ]);
            }

            // Fallback: direct to interactive slide show view
            return redirect()->route('admin.materials.show', $material);
        }

        // 3. Image File: Stream image
        if ($material->isImage()) {
            $absPath = Storage::disk('public')->path($material->file_path);
            if (file_exists($absPath)) {
                return response()->file($absPath);
            }
        }

        return redirect()->route('admin.materials.show', $material);
    }

    /**
     * Re-convert slides for a material.
     */
    public function reconvert(Material $material): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && !$user->isSuperAdmin() && $user->subject_id) {
            abort_if($material->level->subject_id !== $user->subject_id, 403);
        }

        \App\Services\DocumentConverterService::ensureConverted($material, true);

        return back()->with('success', 'Slide materi dan dokumen presentasi berhasil dikonversi ulang.');
    }
}

