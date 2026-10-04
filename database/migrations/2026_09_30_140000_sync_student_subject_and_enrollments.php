<?php

use App\Models\EnglishClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sarah = User::where('email', 'sarah@musashi.id')->first();
        $kenji = User::where('email', 'kenji@musashi.id')->first();

        $students = User::where('role', User::ROLE_STUDENT)->get();

        foreach ($students as $student) {
            $determinedSubjectId = null;

            if ($student->class_name) {
                $matchedClass = EnglishClass::where('name', $student->class_name)->first();
                if ($matchedClass) {
                    $determinedSubjectId = $matchedClass->subject_id;
                }
            }

            if (!$determinedSubjectId && $student->subject_id) {
                $determinedSubjectId = $student->subject_id;
            }

            // If still null, check email/name clues or default to English
            if (!$determinedSubjectId) {
                if (str_contains(strtolower($student->name), 'jepang') || str_contains(strtolower($student->email), 'jp_')) {
                    $determinedSubjectId = 2;
                } else {
                    $determinedSubjectId = 1;
                }
            }

            // Update user's direct subject_id
            $student->update(['subject_id' => $determinedSubjectId]);

            // Sync student enrollments to match this subject
            if ($determinedSubjectId == 2) {
                // Japanese student
                DB::table('student_enrollments')->updateOrInsert(
                    ['student_id' => $student->id, 'subject_id' => 2],
                    [
                        'teacher_id' => $kenji?->id,
                        'class_code' => 'JPN-KENJI',
                        'enrolled_at' => now(),
                        'status' => 'active',
                        'updated_at' => now(),
                    ]
                );

                // Remove legacy enrollment for English so it never leaks
                DB::table('student_enrollments')
                    ->where('student_id', $student->id)
                    ->where('subject_id', 1)
                    ->delete();
            } elseif ($determinedSubjectId == 1) {
                // English student
                DB::table('student_enrollments')->updateOrInsert(
                    ['student_id' => $student->id, 'subject_id' => 1],
                    [
                        'teacher_id' => $sarah?->id,
                        'class_code' => 'ENG-SARAH',
                        'enrolled_at' => now(),
                        'status' => 'active',
                        'updated_at' => now(),
                    ]
                );

                // Remove legacy enrollment for Japanese so it never leaks
                DB::table('student_enrollments')
                    ->where('student_id', $student->id)
                    ->where('subject_id', 2)
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
