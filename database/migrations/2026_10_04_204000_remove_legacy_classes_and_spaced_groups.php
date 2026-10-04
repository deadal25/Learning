<?php

use App\Models\EnglishClass;
use App\Models\Material;
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
        // 1. If any users, materials, or grades reference 'Grup X' with space, convert to 'GrupX'
        for ($i = 1; $i <= 20; $i++) {
            $withSpace = "Grup {$i}";
            $noSpace = "Grup{$i}";

            User::where('class_name', $withSpace)->update(['class_name' => $noSpace]);
            Material::where('class_name', $withSpace)->update(['class_name' => $noSpace]);
            DB::table('english_grades')->where('class_name', $withSpace)->update(['class_name' => $noSpace]);
        }

        // 2. Delete all legacy Japanese groups with space ('Grup 1', 'Grup 2', etc.)
        EnglishClass::where('name', 'like', 'Grup %')
            ->orWhere('name', 'like', 'grup %')
            ->delete();

        // 3. Delete any classes containing the word 'class' (case-insensitive, e.g. 'classB1', 'Class B1', etc.)
        EnglishClass::whereRaw("LOWER(name) LIKE '%class%'")->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to restore legacy duplicate records
    }
};
