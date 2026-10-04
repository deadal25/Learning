<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $romanMap = [
            'Kelas I' => 'Kelas 1',
            'Kelas II' => 'Kelas 2',
            'Kelas III' => 'Kelas 3',
            'Kelas IV' => 'Kelas 4',
            'Kelas V' => 'Kelas 5',
            'Kelas VI' => 'Kelas 6',
            'Kelas VII' => 'Kelas 7',
            'Kelas VIII' => 'Kelas 8',
            'Kelas IX' => 'Kelas 9',
            'Kelas X' => 'Kelas 10',
            'Kelas XI' => 'Kelas 11',
            'Kelas XII' => 'Kelas 12',
        ];

        // 1. Update references in users, materials, grades
        foreach ($romanMap as $roman => $arabic) {
            DB::table('users')
                ->where('class_name', $roman)
                ->update(['class_name' => $arabic]);

            DB::table('materials')
                ->where('class_name', $roman)
                ->update(['class_name' => $arabic]);

            DB::table('english_grades')
                ->where('class_name', $roman)
                ->update(['class_name' => $arabic]);
        }

        // 2. Remove old roman classes from english_classes
        DB::table('english_classes')
            ->where('subject_id', 2)
            ->whereIn('name', array_keys($romanMap))
            ->delete();

        // 3. Upsert clean Arabic numeral classes: Kelas 1 s/d Kelas 12 (Tanpa N3, N4, N5)
        for ($i = 1; $i <= 12; $i++) {
            $className = "Kelas {$i}";
            DB::table('english_classes')->updateOrInsert(
                [
                    'name' => $className,
                    'subject_id' => 2,
                ],
                [
                    'level_name' => "Tingkat {$i}",
                    'description' => "Kelas Bahasa Jepang Musashi - Kelas {$i}",
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 4. Clean any remaining N3, N4, N5 mentions from any Japanese classes
        DB::table('english_classes')
            ->where('subject_id', 2)
            ->where(function ($q) {
                $q->where('level_name', 'like', '%N3%')
                  ->orWhere('level_name', 'like', '%N4%')
                  ->orWhere('level_name', 'like', '%N5%')
                  ->orWhere('description', 'like', '%N3%')
                  ->orWhere('description', 'like', '%N4%')
                  ->orWhere('description', 'like', '%N5%');
            })
            ->update([
                'level_name' => 'Tingkat Standar',
                'description' => 'Kelas Bahasa Jepang Musashi',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};
