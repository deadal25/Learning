<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $englishLevels = DB::table('levels')->where('subject_id', 1)->orderBy('order')->get();

        $sampleBank = [
            [
                'q' => 'Which word is a synonym for "Assist"?',
                'a' => 'Help', 'b' => 'Hinder', 'c' => 'Ignore', 'd' => 'Stop',
                'correct' => 'a',
                'exp' => '"Assist" memiliki arti yang sama dengan "Help" (membantu).',
            ],
            [
                'q' => 'Complete the sentence: "Please wear your safety helmet before ___ the workshop."',
                'a' => 'enter', 'b' => 'entering', 'c' => 'entered', 'd' => 'enters',
                'correct' => 'b',
                'exp' => 'Setelah preposisi "before", kata kerja berbentuk gerund (V-ing).',
            ],
            [
                'q' => 'What is the past tense form of the verb "write"?',
                'a' => 'Written', 'b' => 'Writed', 'c' => 'Wrote', 'd' => 'Writing',
                'correct' => 'c',
                'exp' => 'Bentuk Simple Past (V2) dari "write" adalah kata kerja tak beraturan "wrote".',
            ],
            [
                'q' => 'Choose the correct response: "Could you please pass me the tool kit?"',
                'a' => 'No problem, here you are.', 'b' => 'You are welcome.', 'c' => 'I am fine, thank you.', 'd' => 'Never mind.',
                'correct' => 'a',
                'exp' => '"Here you are" digunakan saat menyerahkan barang yang diminta seseorang.',
            ],
            [
                'q' => 'Which adjective correctly describes something that cannot be broken easily?',
                'a' => 'Fragile', 'b' => 'Durable', 'c' => 'Liquid', 'd' => 'Temporary',
                'correct' => 'b',
                'exp' => '"Durable" berarti tahan lama dan kuat / tidak mudah rusak.',
            ],
            [
                'q' => 'Choose the correct preposition: "The shift handover starts ___ 07:00 AM."',
                'a' => 'at', 'b' => 'in', 'c' => 'on', 'd' => 'for',
                'correct' => 'a',
                'exp' => 'Preposisi waktu "at" digunakan secara spesifik untuk penunjukan jam.',
            ],
            [
                'q' => 'Identify the passive sentence below:',
                'a' => 'The operator inspects the machine.', 'b' => 'The machine was inspected by the operator.', 'c' => 'The operator is inspecting.', 'd' => 'The operator will inspect.',
                'correct' => 'b',
                'exp' => 'Kalimat pasif menggunakan pola Be + Past Participle (V3), yaitu "was inspected".',
            ],
            [
                'q' => 'What does the abbreviation "SOP" stand for in an industrial environment?',
                'a' => 'Standard Operating Procedure', 'b' => 'System Output Protocol', 'c' => 'Safety Operation Plan', 'd' => 'Security Organization Policy',
                'correct' => 'a',
                'exp' => 'SOP adalah singkatan dari Standard Operating Procedure.',
            ],
            [
                'q' => 'Complete the conditional sentence: "If the temperature rises too high, the warning alarm ___."',
                'a' => 'sound', 'b' => 'will sound', 'c' => 'sounded', 'd' => 'would sound',
                'correct' => 'b',
                'exp' => 'Conditional type 1: If + Simple Present, main clause menggunakan will + V1.',
            ],
            [
                'q' => 'Which of the following expressions is used to apologize professionally?',
                'a' => 'I apologize for the delay.', 'b' => 'You must wait.', 'c' => 'It is not my fault.', 'd' => 'Whatever you say.',
                'correct' => 'a',
                'exp' => '"I apologize for the delay" adalah ungkapan permohonan maaf formal dan profesional.',
            ],
        ];

        foreach ($englishLevels as $level) {
            $existingCount = DB::table('exercises')->where('level_id', $level->id)->count();
            if ($existingCount >= 10) {
                continue;
            }

            $needed = 10 - $existingCount;
            for ($k = 0; $k < $needed; $k++) {
                $idx = ($k + $existingCount) % count($sampleBank);
                $qData = $sampleBank[$idx];
                
                DB::table('exercises')->insert([
                    'level_id' => $level->id,
                    'question_number' => $existingCount + $k + 1,
                    'question' => "({$level->name} - Q" . ($existingCount + $k + 1) . ") " . $qData['q'],
                    'option_a' => $qData['a'],
                    'option_b' => $qData['b'],
                    'option_c' => $qData['c'],
                    'option_d' => $qData['d'],
                    'correct_option' => $qData['correct'],
                    'explanation' => $qData['exp'],
                    'points' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No need to reverse
    }
};
