<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Level;
use App\Models\Subject;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExercisePlayController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Japanese students use periodic evaluation tests
        if ($user->subject_id == 2) {
            return redirect()->route('student.japanese.tests.index');
        }

        $subjectId = $user->subject_id ?: 1;
        $subject = Subject::find($subjectId) ?? Subject::first();

        // Fetch all 25 levels for English (or levels for current subject)
        $levels = Level::where('subject_id', $subjectId)
            ->orderBy('order', 'asc')
            ->withCount('exercises')
            ->get();

        if ($levels->isEmpty()) {
            return redirect()->route('student.dashboard')->with('error', 'Belum ada level pertemuan latihan.');
        }

        // Ensure Meeting 1 is always initialized as unlocked ONLY IF it is active
        $firstLevel = $levels->firstWhere('order', 1) ?? $levels->first();
        if ($firstLevel && $firstLevel->is_active) {
            UserLevelStatus::firstOrCreate([
                'user_id' => $user->id,
                'level_id' => $firstLevel->id,
            ], [
                'is_unlocked' => true,
                'is_completed' => false,
                'points' => 0,
            ]);
        }

        // Load existing statuses for this user
        $statuses = UserLevelStatus::where('user_id', $user->id)->get()->keyBy('level_id');

        // Sequential progression verification:
        // Meeting 1 is unlocked IF active.
        // Meeting N (N > 1) is unlocked IF active AND Meeting N-1 is completed!
        $completedOrders = [];
        foreach ($levels as $l) {
            $st = $statuses->get($l->id);
            if ($st && ($st->is_completed || $st->points >= 70)) {
                $completedOrders[$l->order] = true;
            }
        }

        // Propagate unlock status
        foreach ($levels as $l) {
            $prevOrder = $l->order - 1;
            $shouldUnlock = $l->is_active && (($l->order === 1) || isset($completedOrders[$prevOrder]));
            $st = $statuses->get($l->id);

            if ($shouldUnlock) {
                if (!$st || !$st->is_unlocked) {
                    $st = UserLevelStatus::updateOrCreate([
                        'user_id' => $user->id,
                        'level_id' => $l->id,
                    ], [
                        'is_unlocked' => true,
                    ]);
                    $statuses->put($l->id, $st);
                }
            } else {
                // If previous meeting not completed OR level is inactive, ensure this meeting is locked
                if ($st && $st->is_unlocked && (!$l->is_active || (!$st->is_completed && $st->points == 0))) {
                    $st->is_unlocked = false;
                    $st->save();
                }
            }
        }

        $completedMeetingsCount = count($completedOrders);
        $totalPoints = $statuses->sum('points');
        $activeLevel = $levels->first(fn($l) => $l->is_active && !isset($completedOrders[$l->order]))
            ?? $levels->where('is_active', true)->last();

        return view('student.exercises.index', compact(
            'subject',
            'levels',
            'statuses',
            'completedOrders',
            'completedMeetingsCount',
            'totalPoints',
            'activeLevel'
        ));
    }

    public function show(Level $level): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$level->is_active) {
            return redirect()->route('student.exercises.index')
                ->with('error', "Mohon maaf, latihan soal {$level->name} belum diaktifkan oleh Super Admin. Anda baru dapat mengakses setelah Super Admin mengaktifkannya.");
        }

        if (!$user->isEnrolledIn($level->subject_id)) {
            return redirect()->route('student.enroll.index')
                ->with('error', 'Anda belum mengaktifkan mata pelajaran ini. Silakan masukkan Kode Kelas dari guru terlebih dahulu.');
        }

        // Japanese students: redirect to the meeting test for this level
        if ((int)$level->subject_id === 2) {
            $jpTest = \App\Models\JapaneseTest::where('category', 'per_pertemuan')
                ->where('start_meeting', $level->order)
                ->first();
            if ($jpTest) {
                return redirect()->route('student.japanese.tests.show', $jpTest);
            }
        }

        // Check if previous meeting is completed for English student (Sequential progression rule)
        if ($level->subject_id == 1 && $level->order > 1) {
            $prevLevel = Level::where('subject_id', 1)->where('order', $level->order - 1)->first();
            $prevStatus = $prevLevel ? UserLevelStatus::where('user_id', $user->id)->where('level_id', $prevLevel->id)->first() : null;
            $isPrevCompleted = $prevStatus && ($prevStatus->is_completed || $prevStatus->points >= 70);

            if (!$isPrevCompleted) {
                return redirect()->route('student.exercises.index')
                    ->with('error', "Soal {$level->name} masih terkunci. Anda harus menyelesaikan latihan " . ($prevLevel?->name ?? 'pertemuan sebelumnya') . " terlebih dahulu.");
            }
        }

        // Check if level status exists, otherwise initialize it
        $status = UserLevelStatus::firstOrCreate([
            'user_id' => $user->id,
            'level_id' => $level->id,
        ], [
            'is_unlocked' => true,
            'is_completed' => false,
            'points' => 0,
        ]);

        if (!$status->is_unlocked) {
            $status->is_unlocked = true;
            $status->save();
        }

        $level->load(['subject', 'exercises' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }]);

        $currentPoints = $status->points ?? 0;
        $exercises = $level->exercises;

        return view('student.exercises.show', compact('level', 'exercises', 'currentPoints', 'status'));
    }

    public function submit(Request $request, Level $level): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$level->is_active) {
            return redirect()->route('student.exercises.index')
                ->with('error', "Mohon maaf, latihan soal {$level->name} sedang dinonaktifkan oleh Super Admin.");
        }

        // Sequential rule check
        if ($level->subject_id == 1 && $level->order > 1) {
            $prevLevel = Level::where('subject_id', 1)->where('order', $level->order - 1)->first();
            $prevStatus = $prevLevel ? UserLevelStatus::where('user_id', $user->id)->where('level_id', $prevLevel->id)->first() : null;
            $isPrevCompleted = $prevStatus && ($prevStatus->is_completed || $prevStatus->points >= 70);

            if (!$isPrevCompleted) {
                return redirect()->route('student.exercises.index')
                    ->with('error', 'Akses latihan terkunci.');
            }
        }

        $submittedAnswers = $request->input('answers', []);
        $exercises = $level->exercises()->orderBy('question_number', 'asc')->get();

        $totalScore = 0;
        $results = [];

        $status = UserLevelStatus::firstOrCreate([
            'user_id' => $user->id,
            'level_id' => $level->id,
        ], [
            'is_unlocked' => true,
            'is_completed' => false,
            'points' => 0,
        ]);

        DB::transaction(function () use ($user, $level, $exercises, $submittedAnswers, &$totalScore, &$results, $status) {
            foreach ($exercises as $exercise) {
                $userChoice = $submittedAnswers[$exercise->id] ?? null;
                $isCorrect = ($userChoice !== null && strtolower($userChoice) === strtolower($exercise->correct_option));

                if ($isCorrect) {
                    $totalScore += $exercise->points ?: 10;
                }

                if ($userChoice !== null) {
                    ExerciseAttempt::create([
                        'user_id' => $user->id,
                        'exercise_id' => $exercise->id,
                        'level_id' => $level->id,
                        'selected_option' => $userChoice,
                        'is_correct' => $isCorrect,
                    ]);
                }

                $results[] = [
                    'exercise' => $exercise,
                    'user_choice' => $userChoice,
                    'is_correct' => $isCorrect,
                    'correct_option' => $exercise->correct_option,
                ];
            }

            $totalScore = min($totalScore, 100);

            // Mark this meeting completed and update highest points
            $status->points = max($status->points ?? 0, $totalScore);
            $status->is_completed = true;
            $status->completed_at = now();
            $status->save();

            // Unlock next meeting automatically!
            $nextLevel = Level::where('subject_id', $level->subject_id)->where('order', $level->order + 1)->first();
            if ($nextLevel) {
                UserLevelStatus::updateOrCreate([
                    'user_id' => $user->id,
                    'level_id' => $nextLevel->id,
                ], [
                    'is_unlocked' => true,
                ]);
            }

            // Update user progress
            $userProgress = UserProgress::firstOrCreate([
                'user_id' => $user->id,
                'subject_id' => $level->subject_id,
            ], [
                'current_level_id' => $level->id,
                'current_points' => 0,
            ]);

            if ($nextLevel) {
                $userProgress->current_level_id = $nextLevel->id;
            }
            $userProgress->current_points = max($userProgress->current_points, $totalScore);
            $userProgress->save();
        });

        $nextLevel = Level::where('subject_id', $level->subject_id)->where('order', $level->order + 1)->first();
        $canUnlockNext = true;

        return view('student.exercises.result', compact(
            'level',
            'exercises',
            'results',
            'totalScore',
            'canUnlockNext',
            'nextLevel'
        ));
    }

    public function unlockNextLevel(Request $request, Level $level): RedirectResponse
    {
        $user = Auth::user();
        $nextLevel = Level::where('subject_id', $level->subject_id)->where('order', $level->order + 1)->first();

        if ($nextLevel) {
            if (!$nextLevel->is_active) {
                return redirect()->route('student.exercises.index')
                    ->with('info', "Latihan {$nextLevel->name} belum diaktifkan oleh Super Admin. Anda dapat mengerjakannya setelah Super Admin mengaktifkannya.");
            }

            UserLevelStatus::updateOrCreate([
                'user_id' => $user->id,
                'level_id' => $nextLevel->id,
            ], [
                'is_unlocked' => true,
            ]);

            return redirect()->route('student.exercises.show', $nextLevel)
                ->with('success', "🎉 Selamat! Soal {$nextLevel->name} telah berhasil dibuka!");
        }

        return redirect()->route('student.exercises.index')
            ->with('success', "🏆 Luar biasa! Anda telah menuntaskan seluruh pertemuan latihan!");
    }
}
