<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add subject_id to english_classes if not present
        if (!Schema::hasColumn('english_classes', 'subject_id')) {
            Schema::table('english_classes', function (Blueprint $table) {
                $table->foreignId('subject_id')->nullable()->after('id')->constrained('subjects')->onDelete('cascade');
            });
        }

        // Set all existing classes to Bahasa Inggris (subject_id = 1)
        DB::table('english_classes')->whereNull('subject_id')->update(['subject_id' => 1]);

        // 2. Seed Japanese Classes (Kelas I sampai Kelas XII)
        $romanNumerals = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        foreach ($romanNumerals as $num => $roman) {
            $className = "Kelas {$roman}";
            $levelName = $num <= 4 ? 'Tingkat Dasar (N5)' : ($num <= 8 ? 'Tingkat Menengah (N4)' : 'Tingkat Lanjutan (N3)');
            
            DB::table('english_classes')->updateOrInsert(
                ['name' => $className, 'subject_id' => 2],
                [
                    'level_name' => $levelName,
                    'description' => "Kelas Bahasa Jepang Musashi - Tingkat {$roman}",
                    'sort_order' => $num,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Seed Matematika Classes (subject_id = 3)
        $mathClasses = [
            ['name' => 'Kelas Matematika Dasar', 'level_name' => 'Dasar', 'desc' => 'Operasi hitung dasar dan kalkulasi industri', 'order' => 1],
            ['name' => 'Kelas Matematika Terapan', 'level_name' => 'Terapan', 'desc' => 'Kalkulasi presisi manufaktur dan geometri', 'order' => 2],
        ];

        foreach ($mathClasses as $mc) {
            DB::table('english_classes')->updateOrInsert(
                ['name' => $mc['name'], 'subject_id' => 3],
                [
                    'level_name' => $mc['level_name'],
                    'description' => $mc['desc'],
                    'sort_order' => $mc['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 4. Update Japanese levels (Subject 2) to 12 meetings (Pertemuan 1 s/d 12)
        $existingJapaneseLevels = DB::table('levels')->where('subject_id', 2)->get();
        for ($i = 1; $i <= 12; $i++) {
            $meetingName = "Pertemuan {$i}";
            $existing = $existingJapaneseLevels->firstWhere('order', $i);
            if ($existing) {
                DB::table('levels')->where('id', $existing->id)->update([
                    'name' => $meetingName,
                    'description' => "Materi modul dan pembelajaran Bahasa Jepang {$meetingName}",
                    'required_points' => 100,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('levels')->insert([
                    'subject_id' => 2,
                    'name' => $meetingName,
                    'order' => $i,
                    'description' => "Materi modul dan pembelajaran Bahasa Jepang {$meetingName}",
                    'required_points' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // Remove any level for subject 2 beyond order 12
        DB::table('levels')->where('subject_id', 2)->where('order', '>', 12)->delete();

        // 5. Create Japanese Periodic Tests table
        if (!Schema::hasTable('japanese_tests')) {
            Schema::create('japanese_tests', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->integer('start_meeting')->default(1);
                $table->integer('end_meeting')->default(4);
                $table->text('description')->nullable();
                $table->integer('duration_minutes')->default(30);
                $table->integer('pass_score')->default(75);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 6. Create Japanese Test Questions table
        if (!Schema::hasTable('japanese_test_questions')) {
            Schema::create('japanese_test_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('test_id')->constrained('japanese_tests')->onDelete('cascade');
                $table->integer('question_number')->default(1);
                $table->text('question');
                $table->text('option_a');
                $table->text('option_b');
                $table->text('option_c');
                $table->text('option_d');
                $table->string('correct_option', 5); // a, b, c, or d
                $table->text('explanation')->nullable();
                $table->integer('points')->default(25);
                $table->timestamps();
            });
        }

        // 7. Create Japanese Test Submissions table
        if (!Schema::hasTable('japanese_test_submissions')) {
            Schema::create('japanese_test_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('test_id')->constrained('japanese_tests')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->integer('score')->default(0);
                $table->integer('total_questions')->default(0);
                $table->integer('correct_count')->default(0);
                $table->boolean('is_passed')->default(false);
                $table->json('submitted_answers')->nullable();
                $table->text('teacher_feedback')->nullable();
                $table->timestamps();
            });
        }

        // Seed 3 standard periodic tests for Japanese (1-4, 5-8, 9-12)
        $test1Id = DB::table('japanese_tests')->insertGetId([
            'title' => 'Tes Evaluasi Materi Pertemuan 1 - 4 (Dasar Hiragana & Percakapan)',
            'start_meeting' => 1,
            'end_meeting' => 4,
            'description' => 'Ujian evaluasi gabungan materi pembelajaran Bahasa Jepang dari Pertemuan 1 sampai 4.',
            'duration_minutes' => 30,
            'pass_score' => 75,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('japanese_tests')->insert([
            'title' => 'Tes Evaluasi Materi Pertemuan 5 - 8 (Tata Bahasa & Partikel)',
            'start_meeting' => 5,
            'end_meeting' => 8,
            'description' => 'Ujian evaluasi gabungan materi pembelajaran Bahasa Jepang dari Pertemuan 5 sampai 8.',
            'duration_minutes' => 30,
            'pass_score' => 75,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('japanese_tests')->insert([
            'title' => 'Tes Evaluasi Akhir Materi Pertemuan 9 - 12 (Katakana & Aplikasi Industri)',
            'start_meeting' => 9,
            'end_meeting' => 12,
            'description' => 'Ujian evaluasi akhir materi pembelajaran Bahasa Jepang dari Pertemuan 9 sampai 12.',
            'duration_minutes' => 45,
            'pass_score' => 75,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed sample questions for Test 1 (Pertemuan 1-4)
        $questions = [
            [
                'test_id' => $test1Id,
                'question_number' => 1,
                'question' => 'Manakah huruf Hiragana yang tepat untuk bunyi "A" (あ)?',
                'option_a' => 'あ',
                'option_b' => 'い',
                'option_c' => 'う',
                'option_d' => 'え',
                'correct_option' => 'a',
                'explanation' => 'Huruf Hiragana "あ" dibaca "A".',
                'points' => 25,
            ],
            [
                'test_id' => $test1Id,
                'question_number' => 2,
                'question' => 'Ungkapan salam yang digunakan saat menyapa di pagi hari dalam bahasa Jepang adalah...',
                'option_a' => 'Konnichiwa',
                'option_b' => 'Ohayou Gozaimasu',
                'option_c' => 'Konbanwa',
                'option_d' => 'Oyasuminasai',
                'correct_option' => 'b',
                'explanation' => 'Ohayou Gozaimasu (おはようございます) berarti Selamat Pagi.',
                'points' => 25,
            ],
            [
                'test_id' => $test1Id,
                'question_number' => 3,
                'question' => 'Arti kata "Arigatou Gozaimasu" (ありがとうございます) adalah...',
                'option_a' => 'Sama-sama',
                'option_b' => 'Sampai jumpa',
                'option_c' => 'Terima kasih banyak',
                'option_d' => 'Permisi',
                'correct_option' => 'c',
                'explanation' => 'Arigatou Gozaimasu berarti terima kasih banyak secara sopan.',
                'points' => 25,
            ],
            [
                'test_id' => $test1Id,
                'question_number' => 4,
                'question' => 'Angka 7 (tujuh) dalam bahasa Jepang dapat diucapkan sebagai...',
                'option_a' => 'Nana / Shichi',
                'option_b' => 'Roku',
                'option_c' => 'Hachi',
                'option_d' => 'Kyuu',
                'correct_option' => 'a',
                'explanation' => 'Angka 7 dibaca Nana (なな) atau Shichi (しち).',
                'points' => 25,
            ],
        ];

        foreach ($questions as $q) {
            $q['created_at'] = now();
            $q['updated_at'] = now();
            DB::table('japanese_test_questions')->insert($q);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('japanese_test_submissions');
        Schema::dropIfExists('japanese_test_questions');
        Schema::dropIfExists('japanese_tests');

        if (Schema::hasColumn('english_classes', 'subject_id')) {
            Schema::table('english_classes', function (Blueprint $table) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            });
        }
    }
};
