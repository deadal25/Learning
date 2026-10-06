<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MeetingComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingCommentController extends Controller
{
    /**
     * Store student comment or review for a meeting material.
     */
    public function storeStudent(Request $request, Material $material): RedirectResponse
    {
        $user = Auth::user();
        $material->loadMissing('level.subject');
        $level = $material->level;

        // Ensure student is enrolled in the subject
        if (!$user->isEnrolledIn($level->subject_id)) {
            abort(403, 'Anda belum terdaftar pada mata pelajaran materi ini.');
        }

        // Validate content
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'parent_id' => ['nullable', 'integer', 'exists:meeting_comments,id'],
        ], [
            'content.required' => 'Komentar atau ulasan tidak boleh kosong.',
            'content.min' => 'Komentar minimal 2 karakter.',
            'content.max' => 'Komentar maksimal 2000 karakter.',
            'rating.between' => 'Rating harus antara 1 sampai 5 bintang.',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = MeetingComment::findOrFail($parentId);
            
            // Check that reply is on the same meeting level
            if ($parent->level_id !== $level->id) {
                abort(400, 'Komentar induk tidak sesuai dengan pertemuan ini.');
            }

            MeetingComment::create([
                'level_id' => $level->id,
                'material_id' => $material->id,
                'user_id' => $user->id,
                'subject_id' => $level->subject_id,
                'class_name' => $user->class_name ?: $parent->class_name,
                'class_group' => $parent->class_group,
                'parent_id' => $parent->id,
                'content' => trim($validated['content']),
                'rating' => null,
            ]);

            return redirect()->back()->with('success', '💬 Balasan komentar Anda berhasil dikirim!');
        }

        // New top-level meeting comment/review
        $studentClass = $user->class_name ?: ($material->class_name ?: 'Umum');
        $classGroup = MeetingComment::resolveClassGroup($user->class_name ?: $material->class_name, $level->subject_id);

        MeetingComment::create([
            'level_id' => $level->id,
            'material_id' => $material->id,
            'user_id' => $user->id,
            'subject_id' => $level->subject_id,
            'class_name' => $studentClass,
            'class_group' => $classGroup,
            'parent_id' => null,
            'content' => trim($validated['content']),
            'rating' => $validated['rating'] ?? null,
        ]);

        return redirect()->back()->with('success', '🎉 Ulasan pembelajaran Anda untuk ' . $level->name . ' berhasil dikirim!');
    }

    /**
     * Delete student's own comment.
     */
    public function destroyStudent(MeetingComment $comment): RedirectResponse
    {
        $user = Auth::user();

        if ((int)$comment->user_id !== (int)$user->id) {
            abort(403, 'Anda hanya dapat menghapus komentar Anda sendiri.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Komentar berhasil dihapus.');
    }

    /**
     * Store teacher's reply or comment.
     */
    public function storeTeacher(Request $request, Material $material): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        $material->loadMissing('level.subject');
        $level = $material->level;

        if (!$user->isSuperAdmin() && $user->subject_id && (int)$level->subject_id !== (int)$user->subject_id) {
            abort(403, 'Anda hanya dapat membalas komentar untuk mata pelajaran yang Anda ajar.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:meeting_comments,id'],
            'target_class_group' => ['nullable', 'string', 'max:50'],
        ], [
            'content.required' => 'Balasan tidak boleh kosong.',
            'content.max' => 'Balasan maksimal 2000 karakter.',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = MeetingComment::findOrFail($parentId);

            MeetingComment::create([
                'level_id' => $level->id,
                'material_id' => $material->id,
                'user_id' => $user->id,
                'subject_id' => $level->subject_id,
                'class_name' => $parent->class_name,
                'class_group' => $parent->class_group,
                'parent_id' => $parent->id,
                'content' => trim($validated['content']),
                'rating' => null,
            ]);

            return redirect()->back()->with('success', '👨‍🏫 Balasan guru berhasil dikirim!');
        }

        $classGroup = !empty($validated['target_class_group'])
            ? MeetingComment::resolveClassGroup($validated['target_class_group'], $level->subject_id)
            : MeetingComment::resolveClassGroup($material->class_name, $level->subject_id);

        MeetingComment::create([
            'level_id' => $level->id,
            'material_id' => $material->id,
            'user_id' => $user->id,
            'subject_id' => $level->subject_id,
            'class_name' => $material->class_name ?: 'Semua Kelas',
            'class_group' => $classGroup,
            'parent_id' => null,
            'content' => trim($validated['content']),
            'rating' => null,
        ]);

        return redirect()->back()->with('success', '👨‍🏫 Catatan/ulasan guru untuk ' . $level->name . ' berhasil dipublikasikan!');
    }

    /**
     * Delete comment by teacher / admin (moderation).
     */
    public function destroyTeacher(MeetingComment $comment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if (!$user->isSuperAdmin() && $user->subject_id && (int)$comment->subject_id !== (int)$user->subject_id) {
            abort(403, 'Anda tidak berhak menghapus komentar dari mata pelajaran lain.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Komentar berhasil dihapus.');
    }
}
