<?php

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $englishSubject = Subject::where('id', 1)->orWhere('name', 'like', '%Inggris%')->first();

        if ($englishSubject) {
            for ($i = 1; $i <= 25; $i++) {
                Level::updateOrCreate([
                    'subject_id' => $englishSubject->id,
                    'order' => $i,
                ], [
                    'name' => "Pertemuan {$i}",
                    'description' => "Materi modul dan pembelajaran Pertemuan {$i}",
                    'required_points' => ($i - 1) * 100,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
