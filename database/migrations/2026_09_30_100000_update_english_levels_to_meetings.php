<?php

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $englishSubject = Subject::where('id', 1)->orWhere('name', 'like', '%Inggris%')->first();

        if ($englishSubject) {
            $meetingNames = [
                1 => 'Pertemuan 1',
                2 => 'Pertemuan 2',
                3 => 'Pertemuan 3',
                4 => 'Pertemuan 4',
                5 => 'Pertemuan 5',
                6 => 'Pertemuan 6',
                7 => 'Pertemuan 7',
                8 => 'Pertemuan 8',
            ];

            for ($i = 1; $i <= 8; $i++) {
                $level = Level::where('subject_id', $englishSubject->id)
                    ->where('order', $i)
                    ->first();

                if ($level) {
                    $level->update([
                        'name' => $meetingNames[$i],
                        'description' => "Materi pembelajaran dan latihan modul {$meetingNames[$i]}",
                    ]);
                } else {
                    Level::create([
                        'subject_id' => $englishSubject->id,
                        'name' => $meetingNames[$i],
                        'order' => $i,
                        'description' => "Materi pembelajaran dan latihan modul {$meetingNames[$i]}",
                        'required_points' => ($i - 1) * 100,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert can be a no-op or restore names if needed
    }
};
