<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeetingManagementController extends Controller
{
    /**
     * Display list of meetings for teacher's or selected subject.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $allSubjects = Subject::orderBy('order')->get();

        if ($isTeacher && $user->subject_id) {
            $selectedSubjectId = (int)$user->subject_id;
        } else {
            $selectedSubjectId = (int)$request->input('subject_id', $allSubjects->first()?->id ?? 1);
        }

        $currentSubject = Subject::findOrFail($selectedSubjectId);

        $meetings = Level::where('subject_id', $currentSubject->id)
            ->withCount([
                'materials' => function ($q) use ($isTeacher, $user) {
                    if ($isTeacher && (int)$user->subject_id === 1) {
                        $q->where('teacher_id', $user->id);
                    }
                },
                'exercises'
            ])
            ->orderBy('order', 'asc')
            ->get();

        $totalMeetings = $meetings->count();
        $totalMaterials = $meetings->sum('materials_count');
        $totalExercises = $meetings->sum('exercises_count');
        $nextOrder = ($meetings->max('order') ?? 0) + 1;
        $suggestedName = "Pertemuan " . $nextOrder;

        return view('admin.meetings.index', compact(
            'user',
            'isTeacher',
            'allSubjects',
            'currentSubject',
            'meetings',
            'totalMeetings',
            'totalMaterials',
            'totalExercises',
            'nextOrder',
            'suggestedName'
        ));
    }

    /**
     * Store a newly created meeting.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        $targetSubjectId = $isTeacher && $user->subject_id ? (int)$user->subject_id : (int)$request->input('subject_id', 1);
        $subject = Subject::findOrFail($targetSubjectId);

        $validated = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:100'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('levels')->where('subject_id', $subject->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'required_points' => ['nullable', 'integer', 'min:10', 'max:500'],
        ], [
            'name.required' => 'Nama pertemuan wajib diisi (contoh: Pertemuan 26).',
            'order.required' => 'Nomor urutan pertemuan wajib diisi.',
            'order.unique' => "Nomor urutan pertemuan {$request->input('order')} sudah ada di mata pelajaran ini.",
        ]);

        $meeting = Level::create([
            'subject_id' => $subject->id,
            'name' => trim($validated['name']),
            'order' => (int)$validated['order'],
            'description' => $validated['description'] ? trim($validated['description']) : "Modul pembelajaran dan latihan soal {$validated['name']}",
            'required_points' => (int)($validated['required_points'] ?? 100),
        ]);

        // If English subject, generate 10 starter exercise questions so student exercise screen is ready
        if ($subject->id === 1) {
            $this->seedStarterEnglishExercises($meeting);
        }

        return redirect()->route('admin.meetings.index', ['subject_id' => $subject->id])
            ->with('success', "🎉 Berhasil menambahkan '{$meeting->name}' pada {$subject->name}.");
    }

    /**
     * Update the specified meeting.
     */
    public function update(Request $request, Level $meeting): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id && (int)$meeting->subject_id !== (int)$user->subject_id) {
            abort(403, 'Anda hanya dapat mengedit pertemuan untuk mata pelajaran Anda.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('levels')->where('subject_id', $meeting->subject_id)->ignore($meeting->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'required_points' => ['nullable', 'integer', 'min:10', 'max:500'],
        ], [
            'name.required' => 'Nama pertemuan wajib diisi.',
            'order.required' => 'Nomor urutan pertemuan wajib diisi.',
            'order.unique' => "Nomor urutan pertemuan {$request->input('order')} sudah digunakan.",
        ]);

        $meeting->update([
            'name' => trim($validated['name']),
            'order' => (int)$validated['order'],
            'description' => $validated['description'] ? trim($validated['description']) : $meeting->description,
            'required_points' => (int)($validated['required_points'] ?? 100),
        ]);

        return redirect()->route('admin.meetings.index', ['subject_id' => $meeting->subject_id])
            ->with('success', "✅ Pertemuan '{$meeting->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified meeting from storage.
     */
    public function destroy(Level $meeting): RedirectResponse
    {
        $user = Auth::user();
        $isTeacher = $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id && (int)$meeting->subject_id !== (int)$user->subject_id) {
            abort(403, 'Anda hanya dapat menghapus pertemuan untuk mata pelajaran Anda.');
        }

        $meetingName = $meeting->name;
        $subjectId = $meeting->subject_id;
        $subjectName = $meeting->subject?->name ?? 'Mata Pelajaran';

        // Delete associated file attachments in storage if any
        foreach ($meeting->materials as $mat) {
            if ($mat->file_path && Storage::disk('public')->exists($mat->file_path)) {
                Storage::disk('public')->delete($mat->file_path);
            }
        }

        // Delete level (database foreign keys will cascade delete related materials, exercises, statuses)
        $meeting->delete();

        return redirect()->route('admin.meetings.index', ['subject_id' => $subjectId])
            ->with('success', "🗑️ '{$meetingName}' berhasil dihapus dari {$subjectName}.");
    }

    /**
     * Helper to create 10 starter exercises for a new English meeting
     */
    private function seedStarterEnglishExercises(Level $level): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Exercise::create([
                'level_id' => $level->id,
                'question_text' => "What is the most appropriate expression for {$level->name} topic question #{$i} in a manufacturing context?",
                'option_a' => "Standard operational procedure for topic #{$i}",
                'option_b' => "Incorrect or irrelevant response option B",
                'option_c' => "Unrelated vocabulary term C",
                'option_d' => "Grammatically incorrect phrase D",
                'correct_option' => 'A',
                'explanation' => "Option A represents the correct usage and workplace communication for {$level->name}.",
                'points' => 10,
                'question_number' => $i,
            ]);
        }
    }
}
