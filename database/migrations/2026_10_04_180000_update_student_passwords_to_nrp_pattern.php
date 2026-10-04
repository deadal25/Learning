<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $students = User::where('role', User::ROLE_STUDENT)->get();

        foreach ($students as $student) {
            $nrp = $student->nrp;
            $cleanNrp = $nrp ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$nrp) : '';

            if (!empty($cleanNrp)) {
                $newPassword = "{$cleanNrp}@musashi";
                $student->password = Hash::make($newPassword);
                $student->save();
            } else {
                $student->password = Hash::make('password');
                $student->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $students = User::where('role', User::ROLE_STUDENT)->get();
        foreach ($students as $student) {
            $student->password = Hash::make('password');
            $student->save();
        }
    }
};
