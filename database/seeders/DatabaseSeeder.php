<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Level;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserLevelStatus;
use App\Models\UserProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Administrator Musashi',
            'email' => 'superadmin@musashi.id',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        // 2. Create Admins (Guru/Miss)
        $adminSarah = User::create([
            'name' => 'Miss Sarah Jenkins',
            'email' => 'sarah@musashi.id',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'phone' => '081298765432',
            'status' => 'active',
            'created_by' => $superAdmin->id,
            'subject_id' => 1,
            'class_code' => 'ENG-SARAH',
        ]);

        $adminKenji = User::create([
            'name' => 'Kenji Sato Sensei',
            'email' => 'kenji@musashi.id',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'phone' => '081211223344',
            'status' => 'active',
            'created_by' => $superAdmin->id,
            'subject_id' => 2,
            'class_code' => 'JPN-KENJI',
        ]);

        // 3. Create Students (User/Pelajar)
        $studentBudi = User::create([
            'name' => 'Budi Pratama',
            'email' => 'budi@musashi.id',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'phone' => '085712345678',
            'status' => 'active',
            'created_by' => $adminSarah->id,
            'subject_id' => 1,
            'nrp' => '10001',
        ]);

        $studentSiti = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@musashi.id',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'phone' => '085787654321',
            'status' => 'active',
            'created_by' => $adminKenji->id,
            'subject_id' => 2,
            'nrp' => '10002',
        ]);

        // 4. Create Subjects
        $subjectsData = [
            [
                'name' => 'Bahasa Inggris',
                'slug' => 'bahasa-inggris',
                'description' => 'Pelajari percakapan sehari-hari, grammar praktis, dan kosakata Bahasa Inggris bertaraf internasional.',
                'icon' => 'globe',
                'badge_color' => '#3b82f6',
                'order' => 1,
                'levels' => [
                    ['name' => 'Level 1: Basic (Dasar)', 'order' => 1, 'desc' => 'Fondasi alfabet, salam, kata benda & kata ganti personal.'],
                    ['name' => 'Level 2: Beginner (Pemula)', 'order' => 2, 'desc' => 'Simple present tense, tanya jawab praktis, dan dialog harian.'],
                    ['name' => 'Level 3: Middle (Menengah)', 'order' => 3, 'desc' => 'Past tense, modal auxiliaries, dan pemahaman teks bacaan pendek.'],
                    ['name' => 'Level 4: Advanced (Lanjutan)', 'order' => 4, 'desc' => 'Idiom profesional, presentasi lisan, dan essay penulisan.'],
                ],
            ],
            [
                'name' => 'Bahasa Jepang',
                'slug' => 'bahasa-jepang',
                'description' => 'Kuasai huruf Hiragana, Katakana, Kanji dasar, serta percakapan khas Negeri Sakura bersama Musashi.',
                'icon' => 'translate',
                'badge_color' => '#e11d48',
                'order' => 2,
                'levels' => [
                    ['name' => 'Level 1: Basic (Hiragana & Katakana)', 'order' => 1, 'desc' => 'Penguasaan 46 huruf Hiragana, Katakana, dan salam (Aisatsu).'],
                    ['name' => 'Level 2: Beginner (JLPT N5)', 'order' => 2, 'desc' => 'Pola kalimat dasar (~wa ~desu), partikel (wa, ga, o, ni), angka & jam.'],
                    ['name' => 'Level 3: Middle (JLPT N4)', 'order' => 3, 'desc' => 'Perubahan kata kerja bentuk -Te, bentuk lampau, dan ungkapan izin.'],
                    ['name' => 'Level 4: Advanced (JLPT N3)', 'order' => 4, 'desc' => 'Kanji menengah, percakapan natural bisnis, dan pola sopan Keigo.'],
                ],
            ],
            [
                'name' => 'Matematika',
                'slug' => 'matematika',
                'description' => 'Asah logika berpikir cepat, pemecahan masalah aritmatika, aljabar, dan geometri secara menyenangkan.',
                'icon' => 'calculator',
                'badge_color' => '#10b981',
                'order' => 3,
                'levels' => [
                    ['name' => 'Level 1: Basic (Aritmatika & Aljabar Dasar)', 'order' => 1, 'desc' => 'Operasi hitung campuran bilangan bulat, pecahan, dan variabel dasar.'],
                    ['name' => 'Level 2: Beginner (Geometri & Bangun Datar)', 'order' => 2, 'desc' => 'Keliling, luas poligon, segitiga, sudut, dan teorema Pythagoras.'],
                    ['name' => 'Level 3: Middle (Statistika & Peluang)', 'order' => 3, 'desc' => 'Pemusatan data (mean, median, modus), penyajian diagram, dan peluang kejadian.'],
                    ['name' => 'Level 4: Advanced (Kalkulus & Trigonometri)', 'order' => 4, 'desc' => 'Perbandingan trigonometri, aturan sinus-cosinus, dan konsep turunan.'],
                ],
            ],
        ];

        foreach ($subjectsData as $sData) {
            $levels = $sData['levels'];
            unset($sData['levels']);

            $subject = Subject::create($sData);

            foreach ($levels as $lData) {
                $level = Level::create([
                    'subject_id' => $subject->id,
                    'name' => $lData['name'],
                    'order' => $lData['order'],
                    'description' => $lData['desc'],
                    'required_points' => 100,
                ]);

                // Create sample slide presentation material
                Material::create([
                    'level_id' => $level->id,
                    'title' => 'Modul Slide Materi: ' . $level->name,
                    'description' => 'Slide presentasi pengantar konsep, contoh kalimat/soal, dan latihan mandiri untuk ' . $subject->name . '.',
                    'file_name' => strtolower(str_replace(' ', '_', $subject->slug . '_' . $level->order)) . '_slide.pptx',
                    'file_type' => 'pptx',
                    'slide_url' => 'https://docs.google.com/presentation/d/e/2PACX-1vTMusashiDemo/embed?start=false&loop=false&delayms=3000',
                    'order' => 1,
                ]);

                Material::create([
                    'level_id' => $level->id,
                    'title' => 'Ringkasan Catatan & Lembar Kerja ' . $level->name,
                    'description' => 'Lembar rangkuman rumus cepat dan tips menghafal materi level ini.',
                    'file_name' => strtolower(str_replace(' ', '_', $subject->slug . '_' . $level->order)) . '_summary.pdf',
                    'file_type' => 'pdf',
                    'slide_url' => null,
                    'order' => 2,
                ]);

                // Seed 10 exercise questions for Level 1 of each subject
                if ($subject->slug === 'bahasa-inggris' && $level->order === 1) {
                    $this->seedEnglishLevel1Exercises($level);
                } elseif ($subject->slug === 'bahasa-jepang' && $level->order === 1) {
                    $this->seedJapaneseLevel1Exercises($level);
                } elseif ($subject->slug === 'matematika' && $level->order === 1) {
                    $this->seedMathLevel1Exercises($level);
                } else {
                    // Seed generic 10 questions for higher levels as starter
                    $this->seedGenericExercises($level, $subject->name);
                }
            }
        }

        // 5. Initialize user progress for students
        $allSubjects = Subject::with('levels')->get();
        foreach ([$studentBudi, $studentSiti] as $student) {
            foreach ($allSubjects as $subj) {
                $firstLevel = $subj->levels->firstWhere('order', 1);

                if ($firstLevel) {
                    // Set Level 1 as unlocked
                    UserLevelStatus::create([
                        'user_id' => $student->id,
                        'level_id' => $firstLevel->id,
                        'points' => ($student->id === $studentBudi->id && $subj->slug === 'bahasa-inggris') ? 60 : 0,
                        'is_unlocked' => true,
                        'is_completed' => false,
                    ]);

                    // Set remaining levels as locked initially
                    foreach ($subj->levels->where('order', '>', 1) as $higherLevel) {
                        UserLevelStatus::create([
                            'user_id' => $student->id,
                            'level_id' => $higherLevel->id,
                            'points' => 0,
                            'is_unlocked' => false,
                            'is_completed' => false,
                        ]);
                    }

                    // Progress record
                    UserProgress::create([
                        'user_id' => $student->id,
                        'subject_id' => $subj->id,
                        'current_level_id' => $firstLevel->id,
                        'current_points' => ($student->id === $studentBudi->id && $subj->slug === 'bahasa-inggris') ? 60 : 0,
                        'is_completed' => false,
                    ]);
                }
            }
        }
    }

    private function seedEnglishLevel1Exercises(Level $level): void
    {
        $questions = [
            [
                'q' => 'What is the correct greeting in the morning?',
                'a' => 'Good night', 'b' => 'Good morning', 'c' => 'Good afternoon', 'd' => 'Good evening',
                'correct' => 'b',
                'exp' => 'Good morning digunakan untuk menyapa seseorang di pagi hari hingga siang sebelum pukul 12.00.',
            ],
            [
                'q' => 'Choose the correct pronoun: "___ is my sister. Her name is Clara."',
                'a' => 'He', 'b' => 'She', 'c' => 'They', 'd' => 'It',
                'correct' => 'b',
                'exp' => 'Kata ganti untuk subjek wanita tunggal (sister) adalah "She".',
            ],
            [
                'q' => 'What is the plural form of "book"?',
                'a' => 'Books', 'b' => 'Bookes', 'c' => 'Bookies', 'd' => 'Booken',
                'correct' => 'a',
                'exp' => 'Kata benda beraturan "book" ditambah akhiran -s menjadi "books".',
            ],
            [
                'q' => 'Complete the sentence: "I ___ a student at Musashi Academy."',
                'a' => 'is', 'b' => 'am', 'c' => 'are', 'd' => 'be',
                'correct' => 'b',
                'exp' => 'To be yang tepat untuk subjek "I" dalam Simple Present Tense adalah "am".',
            ],
            [
                'q' => 'What is the opposite of "Big"?',
                'a' => 'Large', 'b' => 'Small', 'c' => 'Tall', 'd' => 'Fast',
                'correct' => 'b',
                'exp' => 'Lawan kata dari "Big" (besar) adalah "Small" (kecil).',
            ],
            [
                'q' => '"Where do you live?" What type of information does this question ask?',
                'a' => 'Time', 'b' => 'Reason', 'c' => 'Location / Place', 'd' => 'Person',
                'correct' => 'c',
                'exp' => 'Kata tanya "Where" digunakan untuk menanyakan lokasi atau tempat.',
            ],
            [
                'q' => 'Choose the correct color: "Grass is naturally ___."',
                'a' => 'Red', 'b' => 'Blue', 'c' => 'Green', 'd' => 'Yellow',
                'correct' => 'c',
                'exp' => 'Rumput (grass) umumnya berwarna hijau (green).',
            ],
            [
                'q' => 'Which word is a verb (kata kerja)?',
                'a' => 'Happy', 'b' => 'Table', 'c' => 'Run', 'd' => 'Quickly',
                'correct' => 'c',
                'exp' => '"Run" (berlari) adalah kata kerja (verb), sedangkan table = kata benda, happy = kata sifat.',
            ],
            [
                'q' => 'How do you say "Terima kasih banyak" in English?',
                'a' => 'You are welcome', 'b' => 'Thank you very much', 'c' => 'See you later', 'd' => 'Excuse me',
                'correct' => 'b',
                'exp' => '"Thank you very much" adalah padanan bahasa Inggris untuk "Terima kasih banyak".',
            ],
            [
                'q' => 'Complete the sentence: "They ___ playing football in the field."',
                'a' => 'am', 'b' => 'is', 'c' => 'are', 'd' => 'was',
                'correct' => 'c',
                'exp' => 'Subjek jamak "They" menggunakan to be "are" dalam Present Continuous Tense.',
            ],
        ];

        foreach ($questions as $i => $q) {
            Exercise::create([
                'level_id' => $level->id,
                'question_number' => $i + 1,
                'question' => $q['q'],
                'option_a' => $q['a'],
                'option_b' => $q['b'],
                'option_c' => $q['c'],
                'option_d' => $q['d'],
                'correct_option' => $q['correct'],
                'explanation' => $q['exp'],
                'points' => 10,
            ]);
        }
    }

    private function seedJapaneseLevel1Exercises(Level $level): void
    {
        $questions = [
            [
                'q' => 'Huruf Hiragana untuk bunyi "A - I - U - E - O" adalah...',
                'a' => 'あ - い - う - え - お',
                'b' => 'か - き - く - け - こ',
                'c' => 'さ - し - す - せ - そ',
                'd' => 'ア - イ - ウ - エ - オ',
                'correct' => 'a',
                'exp' => 'Deret vokal pertama Hiragana adalah あ (a), い (i), う (u), え (e), お (o). Huruf di opsi d adalah Katakana.',
            ],
            [
                'q' => 'Ungkapan salam "Selamat Pagi" dalam bahasa Jepang adalah...',
                'a' => 'Konnichiwa', 'b' => 'Konbanwa', 'c' => 'Ohayou Gozaimasu', 'd' => 'Sayounara',
                'correct' => 'c',
                'exp' => 'Ohayou Gozaimasu (おはようございます) diucapkan pada pagi hari.',
            ],
            [
                'q' => 'Arti dari salam "Arigatou Gozaimasu" adalah...',
                'a' => 'Selamat datang', 'b' => 'Terima kasih', 'c' => 'Permisi', 'd' => 'Sampai jumpa',
                'correct' => 'b',
                'exp' => 'Arigatou Gozaimasu berarti terima kasih secara sopan.',
            ],
            [
                'q' => 'Huruf Katakana biasanya digunakan untuk menulis...',
                'a' => 'Kosakata asli Jepang', 'b' => 'Kata serapan dari bahasa asing / nama orang asing', 'c' => 'Tata bahasa', 'd' => 'Partikel kalimat',
                'correct' => 'b',
                'exp' => 'Katakana digunakan untuk kata serapan asing (gairaigo), nama orang asing, dan onomatope.',
            ],
            [
                'q' => 'Lengkapi kalimat perkenalan diri: "Watashi ___ Budi desu."',
                'a' => 'ga', 'b' => 'wa (ditulis は)', 'c' => 'no', 'd' => 'wo',
                'correct' => 'b',
                'exp' => 'Partikel "wa" (ditulis hiragana は) menandakan topik pembicaraan kalimat.',
            ],
            [
                'q' => 'Berapa angka "Tujuh" dalam bahasa Jepang?',
                'a' => 'Ichi', 'b' => 'San', 'c' => 'Nana / Shichi', 'd' => 'Kyuu',
                'correct' => 'c',
                'exp' => 'Angka 7 dibaca "nana" (なな) atau "shichi" (しち).',
            ],
            [
                'q' => 'Kata "Sensei" (せんせい) memiliki arti...',
                'a' => 'Siswa', 'b' => 'Guru / Pengajar', 'c' => 'Teman', 'd' => 'Dokter saja',
                'correct' => 'b',
                'exp' => 'Sensei adalah panggilan penghormatan untuk guru, instruktur, atau dokter.',
            ],
            [
                'q' => 'Huruf Hiragana "KA" (か) jika diberi tanda tenten (゛) dibaca menjadi...',
                'a' => 'GA (が)', 'b' => 'ZA (ざ)', 'c' => 'DA (だ)', 'd' => 'BA (ば)',
                'correct' => 'a',
                'exp' => 'Deret K jika diberi dakuten (tenten) akan berubah bunyi menjadi deret G: が (ga).',
            ],
            [
                'q' => 'Ungkapan sebelum makan dalam tradisi Jepang adalah...',
                'a' => 'Gochisousama deshita', 'b' => 'Itadakimasu', 'c' => 'Okaerinasai', 'd' => 'Ittekimasu',
                'correct' => 'b',
                'exp' => 'Itadakimasu (いただきます) diucapkan sebelum menikmati hidangan.',
            ],
            [
                'q' => 'Arti dari "Sayounara" adalah...',
                'a' => 'Selamat tinggal / Sampai jumpa lagi', 'b' => 'Selamat malam', 'c' => 'Minta maaf', 'd' => 'Sama-sama',
                'correct' => 'a',
                'exp' => 'Sayounara (さようなら) adalah salam perpisahan.',
            ],
        ];

        foreach ($questions as $i => $q) {
            Exercise::create([
                'level_id' => $level->id,
                'question_number' => $i + 1,
                'question' => $q['q'],
                'option_a' => $q['a'],
                'option_b' => $q['b'],
                'option_c' => $q['c'],
                'option_d' => $q['d'],
                'correct_option' => $q['correct'],
                'explanation' => $q['exp'],
                'points' => 10,
            ]);
        }
    }

    private function seedMathLevel1Exercises(Level $level): void
    {
        $questions = [
            [
                'q' => 'Berapa hasil dari 25 + 17 - 12?',
                'a' => '28', 'b' => '30', 'c' => '32', 'd' => '34',
                'correct' => 'b',
                'exp' => '25 + 17 = 42, lalu 42 - 12 = 30.',
            ],
            [
                'q' => 'Hitung hasil operasi perkalian dan pembagian: 12 × 4 ÷ 6 = ...',
                'a' => '6', 'b' => '8', 'c' => '10', 'd' => '12',
                'correct' => 'b',
                'exp' => '12 × 4 = 48. Kemudian 48 ÷ 6 = 8.',
            ],
            [
                'q' => 'Selesaikan persamaan linear satu variabel: 3x + 5 = 20. Nilai x adalah...',
                'a' => '3', 'b' => '4', 'c' => '5', 'd' => '6',
                'correct' => 'c',
                'exp' => '3x = 20 - 5 -> 3x = 15 -> x = 15 / 3 = 5.',
            ],
            [
                'q' => 'Bentuk pecahan paling sederhana dari 24/36 adalah...',
                'a' => '2/3', 'b' => '3/4', 'c' => '4/6', 'd' => '1/2',
                'correct' => 'a',
                'exp' => 'Bagi pembilang dan penyebut dengan FPB 12: 24÷12 = 2, 36÷12 = 3, maka hasilnya 2/3.',
            ],
            [
                'q' => 'Hasil dari (-8) + (-15) - (-10) adalah...',
                'a' => '-33', 'b' => '-13', 'c' => '-5', 'd' => '13',
                'correct' => 'b',
                'exp' => '(-8) + (-15) = -23. Lalu -23 - (-10) = -23 + 10 = -13.',
            ],
            [
                'q' => 'Nilai dari 2^4 (dua pangkat empat) adalah...',
                'a' => '8', 'b' => '16', 'c' => '32', 'd' => '64',
                'correct' => 'b',
                'exp' => '2 × 2 × 2 × 2 = 16.',
            ],
            [
                'q' => 'Jika sebuah persegi memiliki panjang sisi 8 cm, maka kelilingnya adalah...',
                'a' => '16 cm', 'b' => '24 cm', 'c' => '32 cm', 'd' => '64 cm',
                'correct' => 'c',
                'exp' => 'Keliling persegi = 4 × sisi = 4 × 8 cm = 32 cm.',
            ],
            [
                'q' => 'Faktor Persekutuan Terbesar (FPB) dari 18 dan 24 adalah...',
                'a' => '2', 'b' => '3', 'c' => '6', 'd' => '12',
                'correct' => 'c',
                'exp' => 'Faktor 18 = {1, 2, 3, 6, 9, 18}, Faktor 24 = {1, 2, 3, 4, 6, 8, 12, 24}. FPB = 6.',
            ],
            [
                'q' => 'Berapa 15% dari 200.000?',
                'a' => '20.000', 'b' => '30.000', 'c' => '35.000', 'd' => '40.000',
                'correct' => 'b',
                'exp' => '(15 / 100) × 200.000 = 15 × 2.000 = 30.000.',
            ],
            [
                'q' => 'Jika a = 4 dan b = 3, maka nilai dari 2a^2 - 3b adalah...',
                'a' => '21', 'b' => '23', 'c' => '25', 'd' => '29',
                'correct' => 'b',
                'exp' => '2(4^2) - 3(3) = 2(16) - 9 = 32 - 9 = 23.',
            ],
        ];

        foreach ($questions as $i => $q) {
            Exercise::create([
                'level_id' => $level->id,
                'question_number' => $i + 1,
                'question' => $q['q'],
                'option_a' => $q['a'],
                'option_b' => $q['b'],
                'option_c' => $q['c'],
                'option_d' => $q['d'],
                'correct_option' => $q['correct'],
                'explanation' => $q['exp'],
                'points' => 10,
            ]);
        }
    }

    private function seedGenericExercises(Level $level, string $subjectName): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Exercise::create([
                'level_id' => $level->id,
                'question_number' => $i,
                'question' => "Soal latihan #{$i} untuk materi {$subjectName} - {$level->name}. Manakah jawaban yang paling tepat?",
                'option_a' => "Pilihan A - Konsep dasar {$subjectName} {$i}",
                'option_b' => "Pilihan B - Analisis tingkat lanjut {$subjectName} {$i}",
                'option_c' => "Pilihan C - Contoh aplikasi praktis {$subjectName} {$i}",
                'option_d' => "Pilihan D - Pembuktian formula {$subjectName} {$i}",
                'correct_option' => ['a', 'b', 'c', 'd'][($i % 4)],
                'explanation' => "Penjelasan terperinci untuk soal #{$i} pada {$level->name}. Pemahaman konsep inti sangat penting.",
                'points' => 10,
            ]);
        }
    }
}
