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
        for ($i = 1; $i <= 12; $i++) {
            $oldName = "Kelas {$i}";
            $newName = "Grup {$i}";

            EnglishClass::where('subject_id', 2)
                ->where('name', $oldName)
                ->update([
                    'name' => $newName,
                    'level_name' => $newName,
                    'description' => "Grup Belajar Bahasa Jepang Musashi - {$newName}",
                ]);

            User::where('class_name', $oldName)->update(['class_name' => $newName]);
            Material::where('class_name', $oldName)->update(['class_name' => $newName]);
            DB::table('english_grades')->where('class_name', $oldName)->update(['class_name' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $oldName = "Grup {$i}";
            $newName = "Kelas {$i}";

            EnglishClass::where('subject_id', 2)
                ->where('name', $oldName)
                ->update([
                    'name' => $newName,
                    'level_name' => "Tingkat {$i}",
                    'description' => "Kelas Bahasa Jepang Musashi - {$newName}",
                ]);

            User::where('class_name', $oldName)->update(['class_name' => $newName]);
            Material::where('class_name', $oldName)->update(['class_name' => $newName]);
            DB::table('english_grades')->where('class_name', $oldName)->update(['class_name' => $newName]);
        }
    }
};
