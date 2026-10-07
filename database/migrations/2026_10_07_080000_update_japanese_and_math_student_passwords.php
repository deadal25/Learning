<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Japanese students (subject_id = 2) to nrp@jpnmusashi
        $japaneseStudents = DB::table('users')
            ->where('role', 'student')
            ->where('subject_id', 2)
            ->get(['id', 'nrp']);

        if ($japaneseStudents->isNotEmpty()) {
            $cases = [];
            $params = [];
            $ids = [];
            foreach ($japaneseStudents as $student) {
                $nrp = $student->nrp;
                $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';
                $plain = !empty($cleanNrp) ? "{$cleanNrp}@jpnmusashi" : 'password';

                $cases[] = "WHEN id = ? THEN ?";
                $params[] = $student->id;
                $params[] = Hash::make($plain);
                $ids[] = $student->id;
            }
            $sql = "UPDATE users SET password = CASE " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")";
            DB::statement($sql, $params);
        }

        // 2. Update Math students (subject_id = 3) to nrp@mtkmusashi
        $mathStudents = DB::table('users')
            ->where('role', 'student')
            ->where('subject_id', 3)
            ->get(['id', 'nrp']);

        if ($mathStudents->isNotEmpty()) {
            $cases = [];
            $params = [];
            $ids = [];
            foreach ($mathStudents as $student) {
                $nrp = $student->nrp;
                $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';
                $plain = !empty($cleanNrp) ? "{$cleanNrp}@mtkmusashi" : 'password';

                $cases[] = "WHEN id = ? THEN ?";
                $params[] = $student->id;
                $params[] = Hash::make($plain);
                $ids[] = $student->id;
            }
            $sql = "UPDATE users SET password = CASE " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")";
            DB::statement($sql, $params);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $students = DB::table('users')
            ->where('role', 'student')
            ->whereIn('subject_id', [2, 3])
            ->get(['id', 'nrp']);

        if ($students->isNotEmpty()) {
            $cases = [];
            $params = [];
            $ids = [];
            foreach ($students as $student) {
                $nrp = $student->nrp;
                $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';
                $plain = !empty($cleanNrp) ? "{$cleanNrp}@musashi" : 'password';

                $cases[] = "WHEN id = ? THEN ?";
                $params[] = $student->id;
                $params[] = Hash::make($plain);
                $ids[] = $student->id;
            }
            $sql = "UPDATE users SET password = CASE " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")";
            DB::statement($sql, $params);
        }
    }
};
