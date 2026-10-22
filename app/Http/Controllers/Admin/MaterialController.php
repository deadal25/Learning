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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\DocumentConverterService;

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
        $classes = $classesQuery->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $subjects = $isTeacher && $user->subject_id
            ? Subject::where('id', $user->subject_id)->with('levels')->get()
            : Subject::with('levels')->get();

        $query = Material::with(['level.subject', 'teacher']);

        if ($isTeacher && $user->subject_id) {
            $query->whereHas('level', function ($q) use ($user) {
                $q->where('subject_id', $user->subject_id);
            });
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
        $classes = $classesQuery->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

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
        $fileContent = null;

        if ($request->filled('preuploaded_file_path')) {
            $filePath = $request->input('preuploaded_file_path');
            $fileName = $request->input('preuploaded_file_name') ?: pathinfo($filePath, PATHINFO_BASENAME);
            $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) ?: 'pptx';
            $uploadId = $request->input('preuploaded_upload_id');

            // Find assembled chunk content from database
            $chunkRecord = DB::table('material_chunks')
                ->where('chunk_index', -1)
                ->where(function ($q) use ($filePath, $uploadId) {
                    $q->where('file_path', $filePath);
                    if ($uploadId) {
                        $q->orWhere('upload_id', $uploadId);
                    }
                })
                ->first();

            if ($chunkRecord && !empty($chunkRecord->chunk_data)) {
                $fileContent = $chunkRecord->chunk_data;
                DB::table('material_chunks')->where('id', $chunkRecord->id)->delete();
            } elseif (Storage::disk('public')->exists($filePath)) {
                $fileContent = base64_encode(Storage::disk('public')->get($filePath));
            }
        } elseif ($request->hasFile('slide_file')) {
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
            $fileContent = base64_encode(file_get_contents($file->getRealPath()));
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
            'file_content' => $fileContent,
            'slide_url' => $validated['slide_url'] ?? null,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        if ($material->file_path) {
            try {
                \App\Services\DocumentConverterService::ensureConverted($material);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Material conversion skipped on upload: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.materials.index')
            ->with('success', 'Materi presentasi/slide berhasil diunggah.');
    }

    /**
     * Chunked upload handler for files exceeding Vercel 4.5MB serverless limit.
     */
    public function uploadChunk(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Tidak memiliki izin upload.'], 403);
        }

        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$request->input('upload_id'));
        $chunkIndex = (int) $request->input('chunk_index', 0);
        $totalChunks = (int) $request->input('total_chunks', 1);
        $originalName = (string) $request->input('file_name', 'presentation.pptx');

        if (empty($uploadId) || !$request->hasFile('chunk_data')) {
            return response()->json(['error' => 'Payload potongan berkas tidak lengkap.'], 422);
        }

        $chunkFile = $request->file('chunk_data');
        $chunkData = base64_encode(file_get_contents($chunkFile->getRealPath()));

        // Insert or update chunk in Neon PostgreSQL database
        DB::table('material_chunks')->updateOrInsert(
            ['upload_id' => $uploadId, 'chunk_index' => $chunkIndex],
            [
                'total_chunks' => $totalChunks,
                'file_name' => $originalName,
                'file_type' => strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'pptx',
                'chunk_data' => $chunkData,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Check if all chunks have arrived in the database
        $receivedCount = DB::table('material_chunks')
            ->where('upload_id', $uploadId)
            ->where('chunk_index', '>=', 0)
            ->count();

        if ($receivedCount >= $totalChunks) {
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'pptx';
            $storedFilename = \Illuminate\Support\Str::random(40) . '.' . $ext;
            $relativeFilePath = "materials/{$storedFilename}";

            // Fetch and assemble all chunks in order
            $chunks = DB::table('material_chunks')
                ->where('upload_id', $uploadId)
                ->where('chunk_index', '>=', 0)
                ->orderBy('chunk_index', 'asc')
                ->get();

            $fullBinary = '';
            foreach ($chunks as $c) {
                $fullBinary .= base64_decode($c->chunk_data);
            }
            $fullBase64 = base64_encode($fullBinary);

            // Write assembled file to local / tmp storage for immediate availability
            try {
                Storage::disk('public')->put($relativeFilePath, $fullBinary);
            } catch (\Throwable $e) {}

            // Save completed marker with assembled fullBase64 in material_chunks (chunk_index = -1)
            DB::table('material_chunks')->insert([
                'upload_id' => $uploadId,
                'chunk_index' => -1,
                'total_chunks' => $totalChunks,
                'file_name' => $originalName,
                'file_type' => $ext,
                'file_path' => $relativeFilePath,
                'chunk_data' => $fullBase64,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Clean up individual chunks to save space
            DB::table('material_chunks')
                ->where('upload_id', $uploadId)
                ->where('chunk_index', '>=', 0)
                ->delete();

            return response()->json([
                'completed' => true,
                'upload_id' => $uploadId,
                'file_path' => $relativeFilePath,
                'file_name' => $originalName,
                'file_type' => $ext,
            ]);
        }

        return response()->json([
            'completed' => false,
            'chunk_index' => $chunkIndex,
        ]);
    }

    /**
     * Authorize that the current teacher can access or modify the material safely.
     */
    protected function authorizeMaterialAccess(Material $material, string $action = 'mengakses'): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();
        if (!$isTeacher || !$user->subject_id) {
            return;
        }

        // The teacher who created the material is always allowed
        if ($material->teacher_id && (int)$material->teacher_id === (int)$user->id) {
            return;
        }

        $material->loadMissing('level.subject');
        if ($material->level && (int)$material->level->subject_id !== (int)$user->subject_id) {
            abort(403, "Anda tidak berhak {$action} materi mata pelajaran lain.");
        }
    }

    public function show(Material $material, Request $request): View
    {
        $this->authorizeMaterialAccess($material, 'melihat');

        $material->loadMissing('level.subject', 'teacher');
        $level = $material->level;

        $presentationData = \App\Services\DocumentConverterService::ensureConverted($material);

        $isJapanese = (int)$level->subject_id === 2;

        $commentsQuery = \App\Models\MeetingComment::where('level_id', $level->id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest();

        if ($isJapanese) {
            $defaultClassGroup = 'Semua Grup';
            $selectedClassGroup = $request->input('class_group', 'all');

            if ($selectedClassGroup !== 'all' && !empty($selectedClassGroup) && $selectedClassGroup !== 'Semua Grup') {
                $commentsQuery->where(function ($q) use ($selectedClassGroup) {
                    $q->where('class_name', $selectedClassGroup)
                        ->orWhere('class_group', $selectedClassGroup);
                });
            }

            $meetingComments = $commentsQuery->get();

            $existingCommentGroups = \App\Models\MeetingComment::where('level_id', $level->id)
                ->distinct()
                ->pluck('class_name')
                ->filter()
                ->values();

            $allClassGroups = collect(['Semua Grup'])->merge($existingCommentGroups)->unique()->values();
        } else {
            $defaultClassGroup = \App\Models\MeetingComment::resolveClassGroup($material->class_name, $level->subject_id);
            $selectedClassGroup = $request->input('class_group', $defaultClassGroup);

            if ($selectedClassGroup !== 'all' && !empty($selectedClassGroup)) {
                $commentsQuery->where('class_group', $selectedClassGroup);
            }

            $meetingComments = $commentsQuery->get();

            $commentedGroups = \App\Models\MeetingComment::where('level_id', $level->id)
                ->distinct()
                ->pluck('class_group')
                ->filter()
                ->values();

            $allClassGroups = collect(['B', 'E', 'I'])->merge($commentedGroups)->unique()->values();
        }

        return view('admin.materials.show', compact(
            'material',
            'level',
            'presentationData',
            'meetingComments',
            'selectedClassGroup',
            'allClassGroups',
            'defaultClassGroup',
            'isJapanese'
        ));
    }

    public function edit(Material $material): View
    {
        $this->authorizeMaterialAccess($material, 'mengedit');

        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();
        $material->loadMissing('level.subject', 'teacher');

        if ($isTeacher && $user->subject_id) {
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
        $classes = $classesQuery->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('admin.materials.edit', compact('material', 'subjects', 'classes', 'isTeacher', 'user'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $this->authorizeMaterialAccess($material, 'mengedit');

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
            abort_if((int)$level->subject_id !== (int)$user->subject_id, 403, 'Tingkatan level yang dipilih bukan untuk mata pelajaran Anda.');
        }

        $filePath = $material->file_path;
        $fileName = $material->file_name;
        $fileType = $material->file_type;
        $fileChanged = false;
        $newFileContent = null;

        if ($request->filled('preuploaded_file_path')) {
            $filePath = $request->input('preuploaded_file_path');
            $fileName = $request->input('preuploaded_file_name') ?: pathinfo($filePath, PATHINFO_BASENAME);
            $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) ?: 'pptx';
            $uploadId = $request->input('preuploaded_upload_id');
            $fileChanged = true;

            $chunkRecord = DB::table('material_chunks')
                ->where('chunk_index', -1)
                ->where(function ($q) use ($filePath, $uploadId) {
                    $q->where('file_path', $filePath);
                    if ($uploadId) {
                        $q->orWhere('upload_id', $uploadId);
                    }
                })
                ->first();

            if ($chunkRecord && !empty($chunkRecord->chunk_data)) {
                $newFileContent = $chunkRecord->chunk_data;
                DB::table('material_chunks')->where('id', $chunkRecord->id)->delete();
            } elseif (Storage::disk('public')->exists($filePath)) {
                $newFileContent = base64_encode(Storage::disk('public')->get($filePath));
            }
        } elseif ($request->hasFile('slide_file')) {
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
            $fileChanged = true;
            $newFileContent = base64_encode(file_get_contents($file->getRealPath()));
        }

        $targetClass = !empty($validated['class_name']) ? trim($validated['class_name']) : null;

        $updateData = [
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
        ];

        if ($fileChanged && $newFileContent !== null) {
            $updateData['file_content'] = $newFileContent;
        }

        $material->update($updateData);

        if ($fileChanged) {
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
        $this->authorizeMaterialAccess($material, 'mengubah status');

        $material->is_active = !$material->is_active;
        $material->save();

        $statusText = $material->is_active
            ? 'DIAKTIFKAN (siswa kelas/grup terkait sekarang dapat melihat materi ini)'
            : 'DINONAKTIFKAN (materi sekarang disembunyikan dari siswa)';

        return back()->with('success', "Materi '{$material->title}' berhasil {$statusText}.");
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->authorizeMaterialAccess($material, 'menghapus');

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
        $this->authorizeMaterialAccess($material, 'mengunduh');

        $absPath = \App\Services\DocumentConverterService::resolveMaterialFile($material);
        if (!$absPath) {
            return back()->with('error', 'File materi tidak ditemukan di penyimpanan server.');
        }

        return response()->download(
            $absPath,
            $material->file_name ?? 'materi_musashi.' . ($material->file_type ?? 'pptx')
        );
    }

    /**
     * Preview material file safely inline in the browser without dumping raw binary codes.
     */
    public function preview(Material $material): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        $this->authorizeMaterialAccess($material, 'melihat pratinjau');

        if (!$material->file_path) {
            return redirect()->route('admin.materials.show', $material);
        }

        $absPath = \App\Services\DocumentConverterService::resolveMaterialFile($material);

        // 1. PDF File: Stream inline as application/pdf
        if ($material->isPdf()) {
            if ($absPath) {
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
            if ($absPath) {
                return response()->file($absPath);
            }
        }

        return redirect()->route('admin.materials.show', $material);
    }

    /**
     * Public stream endpoint allowing Microsoft Office Viewer and Google Docs Viewer
     * to fetch and embed PPT/PDF/DOC files without session cookie barriers.
     */
    public function publicStream(Material $material, ?string $filename = null): \Symfony\Component\HttpFoundation\Response
    {
        if (empty($material->file_path)) {
            abort(404, 'Materi ini tidak memiliki file lampiran.');
        }

        $absPath = \App\Services\DocumentConverterService::resolveMaterialFile($material);
        if (!$absPath || !file_exists($absPath)) {
            abort(404, 'File materi tidak ditemukan di penyimpanan server.');
        }

        $mimeType = match (strtolower($material->file_type ?? '')) {
            'pdf' => 'application/pdf',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt' => 'application/vnd.ms-powerpoint',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pps' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => mime_content_type($absPath) ?: 'application/octet-stream',
        };

        $safeName = addslashes($material->file_name ?? ($material->title . '.' . ($material->file_type ?? 'bin')));

        return response()->file($absPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400, must-revalidate',
        ]);
    }

    /**
     * Re-convert slides for a material.
     */
    public function reconvert(Material $material): RedirectResponse
    {
        $this->authorizeMaterialAccess($material, 'mengonversi');

        \App\Services\DocumentConverterService::ensureConverted($material, true);

        return back()->with('success', 'Slide materi dan dokumen presentasi berhasil dikonversi ulang.');
    }
}

