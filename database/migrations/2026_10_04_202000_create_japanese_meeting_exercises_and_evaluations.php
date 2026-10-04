<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add category and target_questions columns to japanese_tests
        Schema::table('japanese_tests', function (Blueprint $table) {
            if (!Schema::hasColumn('japanese_tests', 'category')) {
                $table->string('category', 50)->default('per_pertemuan')->after('title');
            }
            if (!Schema::hasColumn('japanese_tests', 'target_questions')) {
                $table->integer('target_questions')->default(10)->after('pass_score');
            }
        });

        // 2. Update existing 3 periodic tests to category per_4_pertemuan
        DB::table('japanese_tests')->whereIn('start_meeting', [1, 5, 9])->whereIn('end_meeting', [4, 8, 12])->update([
            'category' => 'per_4_pertemuan',
            'target_questions' => 15,
        ]);

        // Define 12 Meeting Tests (Latihan Soal Per Pertemuan - 10 Nomor)
        $meetingTestsData = [
            1 => [
                'title' => 'Latihan Soal Pertemuan 1: Hiragana Vokal (A-I-U-E-O) & Ka-Ki-Ku-Ke-Ko + Aisatsu',
                'desc' => 'Latihan pengenalan huruf Hiragana dasar dan salam sapaan kerja (Aisatsu) harian pabrik.',
                'questions' => [
                    [
                        'q' => 'Manakah huruf Hiragana yang tepat untuk bunyi vokal "A"?',
                        'a' => 'あ', 'b' => 'い', 'c' => 'う', 'd' => 'え', 'cor' => 'a',
                        'exp' => 'Huruf "あ" dibaca "A".'
                    ],
                    [
                        'q' => 'Salam sapaan yang diucapkan saat pagi hari kepada atasan atau rekan kerja adalah...',
                        'a' => 'Konnichiwa', 'b' => 'Ohayou gozaimasu', 'c' => 'Konbanwa', 'd' => 'Sayounara', 'cor' => 'b',
                        'exp' => 'Ohayou gozaimasu diucapkan pada pagi hari.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "か" dibaca sebagai...',
                        'a' => 'Ka', 'b' => 'Ki', 'c' => 'Ku', 'd' => 'Ke', 'cor' => 'a',
                        'exp' => 'Huruf "か" berbunyi "Ka".'
                    ],
                    [
                        'q' => 'Manakah penulisan kata "Ie" (rumah) dalam huruf Hiragana yang benar?',
                        'a' => 'いえ', 'b' => 'あえ', 'c' => 'うえ', 'd' => 'おえ', 'cor' => 'a',
                        'exp' => 'Kata "Ie" (rumah) ditulis "いえ".'
                    ],
                    [
                        'q' => 'Salam perpisahan yang umum diucapkan saat selesai jam kerja atau berpisah adalah...',
                        'a' => 'Arigatou', 'b' => 'Sayounara', 'c' => 'Sumimasen', 'd' => 'Douzo', 'cor' => 'b',
                        'exp' => 'Sayounara berarti selamat tinggal / sampai jumpa.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "き" dibaca sebagai...',
                        'a' => 'Ka', 'b' => 'Ki', 'c' => 'Ko', 'd' => 'Ku', 'cor' => 'b',
                        'exp' => 'Huruf "き" berbunyi "Ki".'
                    ],
                    [
                        'q' => 'Ucapan terima kasih formal dalam bahasa Jepang yang sopan adalah...',
                        'a' => 'Arigatou gozaimasu', 'b' => 'Douitashimashite', 'c' => 'Gomen nasai', 'd' => 'Konnichiwa', 'cor' => 'a',
                        'exp' => 'Arigatou gozaimasu adalah ucapan terima kasih standar formal.'
                    ],
                    [
                        'q' => 'Kata "Kaki" (buah kesemek / tiram) ditulis dalam Hiragana sebagai...',
                        'a' => 'かき', 'b' => 'きか', 'c' => 'こく', 'd' => 'けこ', 'cor' => 'a',
                        'exp' => 'Ka (か) + Ki (き) = かき (Kaki).'
                    ],
                    [
                        'q' => 'Salam sapaan yang tepat saat berpapasan di siang atau sore hari adalah...',
                        'a' => 'Ohayou', 'b' => 'Konnichiwa', 'c' => 'Oyasumi', 'd' => 'Itadakimasu', 'cor' => 'b',
                        'exp' => 'Konnichiwa adalah sapaan selamat siang / halo.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "こ" dibaca sebagai...',
                        'a' => 'Ke', 'b' => 'Ku', 'c' => 'Ki', 'd' => 'Ko', 'cor' => 'd',
                        'exp' => 'Huruf "こ" dibaca "Ko".'
                    ],
                ]
            ],
            2 => [
                'title' => 'Latihan Soal Pertemuan 2: Hiragana Baris Sa-Ta-Na & Perkenalan Diri (Jikoshoukai)',
                'desc' => 'Latihan huruf baris Sa, Ta, Na dan pola dasar perkenalan diri dalam lingkungan kerja.',
                'questions' => [
                    [
                        'q' => 'Ungkapan "Senang bertemu dengan Anda" saat pertama kali berkenalan adalah...',
                        'a' => 'Hajimemashite', 'b' => 'Sayounara', 'c' => 'Konbanwa', 'd' => 'Oyasuminasai', 'cor' => 'a',
                        'exp' => 'Hajimemashite diucapkan pertama kali saat berkenalan.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "さ" dibaca sebagai...',
                        'a' => 'Sa', 'b' => 'Shi', 'c' => 'Su', 'd' => 'Se', 'cor' => 'a',
                        'exp' => 'Huruf "さ" berbunyi "Sa".'
                    ],
                    [
                        'q' => 'Kalimat permohonan kerja sama di akhir sesi perkenalan diri adalah...',
                        'a' => 'Douzo yoroshiku onegaishimasu', 'b' => 'Arigatou gozaimasu', 'c' => 'Wakarimashita', 'd' => 'Itadakimasu', 'cor' => 'a',
                        'exp' => 'Douzo yoroshiku onegaishimasu berarti mohon bimbingan dan kerja samanya.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "た" dibaca sebagai...',
                        'a' => 'Ta', 'b' => 'Chi', 'c' => 'Tsu', 'd' => 'Te', 'cor' => 'a',
                        'exp' => 'Huruf "た" dibaca "Ta".'
                    ],
                    [
                        'q' => 'Penulisan kata "Neko" (kucing) dalam Hiragana yang benar adalah...',
                        'a' => 'ねこ', 'b' => 'なこ', 'c' => 'にこ', 'd' => 'のこ', 'cor' => 'a',
                        'exp' => 'Ne (ね) + Ko (こ) = ねこ (Neko).'
                    ],
                    [
                        'q' => 'Huruf Hiragana "し" dibaca sebagai...',
                        'a' => 'Sa', 'b' => 'Shi', 'c' => 'Su', 'd' => 'Se', 'cor' => 'b',
                        'exp' => 'Huruf "し" dibaca "Shi".'
                    ],
                    [
                        'q' => 'Kata "Sakana" (ikan) ditulis dalam Hiragana sebagai...',
                        'a' => 'さかな', 'b' => 'たかな', 'c' => 'なさか', 'd' => 'さたな', 'cor' => 'a',
                        'exp' => 'Sa (さ) + Ka (か) + Na (な) = さかな.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "ち" dibaca sebagai...',
                        'a' => 'Ta', 'b' => 'Chi', 'c' => 'Tsu', 'd' => 'Te', 'cor' => 'b',
                        'exp' => 'Huruf "ち" dibaca "Chi".'
                    ],
                    [
                        'q' => 'Kosakata bahasa Jepang untuk menyebut "Nama" adalah...',
                        'a' => 'Namae', 'b' => 'Sensei', 'c' => 'Gakusei', 'd' => 'Kaisha', 'cor' => 'a',
                        'exp' => 'Namae (名前) berarti Nama.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "な" dibaca sebagai...',
                        'a' => 'Na', 'b' => 'Ni', 'c' => 'Nu', 'd' => 'Ne', 'cor' => 'a',
                        'exp' => 'Huruf "な" dibaca "Na".'
                    ],
                ]
            ],
            3 => [
                'title' => 'Latihan Soal Pertemuan 3: Hiragana Ha-Ma-Ya-Ra-Wa-N & Angka 1-10',
                'desc' => 'Latihan kelengkapan huruf Hiragana serta penguasaan penyebutan angka 1 sampai 10.',
                'questions' => [
                    [
                        'q' => 'Angka 1 dalam bahasa Jepang disebut...',
                        'a' => 'Ichi', 'b' => 'Ni', 'c' => 'San', 'd' => 'Shi', 'cor' => 'a',
                        'exp' => 'Ichi (一) berarti 1.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "は" dalam kosa kata dibaca sebagai...',
                        'a' => 'Ha', 'b' => 'Hi', 'c' => 'Fu', 'd' => 'He', 'cor' => 'a',
                        'exp' => 'Huruf "は" berbunyi "Ha" (sebagai partikel dibaca Wa).'
                    ],
                    [
                        'q' => 'Angka 3 dalam bahasa Jepang disebut...',
                        'a' => 'Ichi', 'b' => 'Ni', 'c' => 'San', 'd' => 'Go', 'cor' => 'c',
                        'exp' => 'San (三) berarti 3.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "ま" dibaca sebagai...',
                        'a' => 'Ma', 'b' => 'Mi', 'c' => 'Mu', 'd' => 'Me', 'cor' => 'a',
                        'exp' => 'Huruf "ま" dibaca "Ma".'
                    ],
                    [
                        'q' => 'Angka 5 dalam bahasa Jepang disebut...',
                        'a' => 'Shi', 'b' => 'Go', 'c' => 'Roku', 'd' => 'Nana', 'cor' => 'b',
                        'exp' => 'Go (五) berarti 5.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "ら" dibaca sebagai...',
                        'a' => 'Ra', 'b' => 'Ri', 'c' => 'Ru', 'd' => 'Re', 'cor' => 'a',
                        'exp' => 'Huruf "ら" dibaca "Ra".'
                    ],
                    [
                        'q' => 'Angka 7 dalam bahasa Jepang dapat diucapkan sebagai...',
                        'a' => 'Nana / Shichi', 'b' => 'Hachi', 'c' => 'Kyuu', 'd' => 'Juu', 'cor' => 'a',
                        'exp' => 'Angka 7 diucapkan Nana atau Shichi.'
                    ],
                    [
                        'q' => 'Huruf Hiragana "ん" adalah satu-satunya konsonan tunggal yang berbunyi...',
                        'a' => 'N / M / Ng', 'b' => 'S', 'c' => 'T', 'd' => 'K', 'cor' => 'a',
                        'exp' => 'Huruf "ん" berbunyi nasal N.'
                    ],
                    [
                        'q' => 'Angka 10 dalam bahasa Jepang disebut...',
                        'a' => 'Juu', 'b' => 'Hachi', 'c' => 'Roku', 'd' => 'San', 'cor' => 'a',
                        'exp' => 'Juu (十) berarti 10.'
                    ],
                    [
                        'q' => 'Penulisan kata "Yama" (gunung) dalam Hiragana adalah...',
                        'a' => 'やま', 'b' => 'ゆま', 'c' => 'よま', 'd' => 'らま', 'cor' => 'a',
                        'exp' => 'Ya (や) + Ma (ま) = やま.'
                    ],
                ]
            ],
            4 => [
                'title' => 'Latihan Soal Pertemuan 4: Dakuon, Handakuon, Youon & Benda Industri',
                'desc' => 'Latihan bunyi khusus (tenten & maru), kombinasi huruf Youon, dan istilah pabrik.',
                'questions' => [
                    [
                        'q' => 'Huruf "か" jika diberi tanda tenten (ﾞ) berbunyi...',
                        'a' => 'Ga', 'b' => 'Za', 'c' => 'Da', 'd' => 'Ba', 'cor' => 'a',
                        'exp' => 'か (Ka) + tenten = が (Ga).'
                    ],
                    [
                        'q' => 'Huruf "は" jika diberi tanda maru (ﾟ) berbunyi...',
                        'a' => 'Pa', 'b' => 'Ba', 'c' => 'Ma', 'd' => 'Da', 'cor' => 'a',
                        'exp' => 'は (Ha) + maru = ぱ (Pa).'
                    ],
                    [
                        'q' => 'Istilah bahasa Jepang untuk "Pabrik / Manufaktur" adalah...',
                        'a' => 'Koujou', 'b' => 'Gakkou', 'c' => 'Byouin', 'd' => 'Eki', 'cor' => 'a',
                        'exp' => 'Koujou (工場) berarti pabrik.'
                    ],
                    [
                        'q' => 'Gabungan huruf Hiragana "き" dengan huruf kecil "ゃ" (きゃ) dibaca...',
                        'a' => 'Kya', 'b' => 'Kyu', 'c' => 'Kyo', 'd' => 'Ka', 'cor' => 'a',
                        'exp' => 'Kombinasi き + ゃ dibaca Kya.'
                    ],
                    [
                        'q' => 'Kata "Denki" dalam bahasa Jepang berarti...',
                        'a' => 'Listrik / Lampu', 'b' => 'Air', 'c' => 'Api', 'd' => 'Angin', 'cor' => 'a',
                        'exp' => 'Denki (電気) berarti listrik atau lampu penerangan.'
                    ],
                    [
                        'q' => 'Huruf "さ" dengan tanda tenten (ざ) dibaca sebagai...',
                        'a' => 'Za', 'b' => 'Ga', 'c' => 'Da', 'd' => 'Ba', 'cor' => 'a',
                        'exp' => 'さ (Sa) + tenten = ざ (Za).'
                    ],
                    [
                        'q' => 'Gabungan huruf "し" dengan huruf kecil "ゃ" (しゃ) dibaca sebagai...',
                        'a' => 'Sha', 'b' => 'Shu', 'c' => 'Sho', 'd' => 'Sa', 'cor' => 'a',
                        'exp' => 'Kombinasi し + ゃ dibaca Sha.'
                    ],
                    [
                        'q' => 'Kata "Jidousha" (mobil / kendaraan bermotor) mengandung bunyi Youon yaitu...',
                        'a' => 'Sha (しゃ)', 'b' => 'Nya (にゃ)', 'c' => 'Mya (みゃ)', 'd' => 'Hya (ひゃ)', 'cor' => 'a',
                        'exp' => 'Ji-dou-sha menggunakan sha (しゃ).'
                    ],
                    [
                        'q' => 'Istilah bahasa Jepang untuk "Ruang kerja / Kantor" adalah...',
                        'a' => 'Jimusho', 'b' => 'Koujou', 'c' => 'Shokudou', 'd' => 'Toire', 'cor' => 'a',
                        'exp' => 'Jimusho (事務所) berarti kantor.'
                    ],
                    [
                        'q' => 'Huruf "た" jika diberi tanda tenten (だ) dibaca sebagai...',
                        'a' => 'Da', 'b' => 'Ga', 'c' => 'Za', 'd' => 'Ba', 'cor' => 'a',
                        'exp' => 'た (Ta) + tenten = だ (Da).'
                    ],
                ]
            ],
            5 => [
                'title' => 'Latihan Soal Pertemuan 5: Katakana Dasar (A-I-U-E-O s/d Ka-Sa-Ta-Na) & Kata Serapan',
                'desc' => 'Latihan huruf Katakana baris vokal sampai Na serta istilah serapan bahasa asing.',
                'questions' => [
                    [
                        'q' => 'Huruf Katakana biasanya digunakan khusus untuk menuliskan...',
                        'a' => 'Kata serapan dari bahasa asing dan nama asing', 'b' => 'Tata bahasa asli Jepang', 'c' => 'Partikel kalimat', 'd' => 'Kata kerja asli', 'cor' => 'a',
                        'exp' => 'Katakana digunakan untuk kata pinjaman/serapan asing (Gairaigo).'
                    ],
                    [
                        'q' => 'Huruf Katakana untuk bunyi vokal "A" adalah...',
                        'a' => 'ア', 'b' => 'イ', 'c' => 'ウ', 'd' => 'エ', 'cor' => 'a',
                        'exp' => 'Huruf "ア" adalah Katakana untuk A.'
                    ],
                    [
                        'q' => 'Huruf Katakana "カ" dibaca sebagai...',
                        'a' => 'Ka', 'b' => 'Ki', 'c' => 'Ku', 'd' => 'Ke', 'cor' => 'a',
                        'exp' => 'Huruf "カ" dibaca Ka.'
                    ],
                    [
                        'q' => 'Kata serapan "Botoru" (botol) ditulis dalam Katakana sebagai...',
                        'a' => 'ボトル', 'b' => 'コトル', 'c' => 'ポトル', 'd' => 'ロトル', 'cor' => 'a',
                        'exp' => 'Botoru ditulis ボトル.'
                    ],
                    [
                        'q' => 'Huruf Katakana "サ" dibaca sebagai...',
                        'a' => 'Sa', 'b' => 'Shi', 'c' => 'Su', 'd' => 'Se', 'cor' => 'a',
                        'exp' => 'Huruf "サ" dibaca Sa.'
                    ],
                    [
                        'q' => 'Kata serapan "Beruto" (sabuk konveyor / belt) ditulis dalam Katakana sebagai...',
                        'a' => 'ベルト', 'b' => 'ヘルト', 'c' => 'セルト', 'd' => 'ポルト', 'cor' => 'a',
                        'exp' => 'Beruto ditulis ベルト.'
                    ],
                    [
                        'q' => 'Huruf Katakana "タ" dibaca sebagai...',
                        'a' => 'Ta', 'b' => 'Chi', 'c' => 'Tsu', 'd' => 'Te', 'cor' => 'a',
                        'exp' => 'Huruf "タ" dibaca Ta.'
                    ],
                    [
                        'q' => 'Nama negara "Indonesia" ditulis dalam Katakana sebagai...',
                        'a' => 'インドネシア', 'b' => 'インドニシア', 'c' => 'アンドネシア', 'd' => 'ウンドネシア', 'cor' => 'a',
                        'exp' => 'Indonesia ditulis インドネシア.'
                    ],
                    [
                        'q' => 'Huruf Katakana "ナ" dibaca sebagai...',
                        'a' => 'Na', 'b' => 'Ni', 'c' => 'Nu', 'd' => 'Ne', 'cor' => 'a',
                        'exp' => 'Huruf "ナ" dibaca Na.'
                    ],
                    [
                        'q' => 'Tanda vokal panjang dalam Katakana dilambangkan dengan simbol garis mendatar...',
                        'a' => 'ー (Chouon)', 'b' => '～', 'c' => '・', 'd' => '＝', 'cor' => 'a',
                        'exp' => 'Simbol garis mendatar ー menandakan bunyi vokal dipanjangkan.'
                    ],
                ]
            ],
            6 => [
                'title' => 'Latihan Soal Pertemuan 6: Katakana Lanjutan & Peralatan Manufaktur',
                'desc' => 'Latihan penguasaan Katakana lanjutan dan istilah perkakas kerja bengkel industri.',
                'questions' => [
                    [
                        'q' => 'Kata Katakana "スパナ" (Supana) berarti alat bengkel apa?',
                        'a' => 'Kunci pas (Spanner)', 'b' => 'Obeng', 'c' => 'Tang', 'd' => 'Palu', 'cor' => 'a',
                        'exp' => 'Supana adalah kunci pas (Spanner).'
                    ],
                    [
                        'q' => 'Kata Katakana "ドライバー" (Doraibaa) dalam pabrik bermakna...',
                        'a' => 'Obeng (Screwdriver)', 'b' => 'Sopir kendaraan', 'c' => 'Mesin las', 'd' => 'Gunting plat', 'cor' => 'a',
                        'exp' => 'Doraibaa berarti obeng.'
                    ],
                    [
                        'q' => 'Huruf Katakana "マ" dibaca sebagai...',
                        'a' => 'Ma', 'b' => 'Mi', 'c' => 'Mu', 'd' => 'Me', 'cor' => 'a',
                        'exp' => 'Huruf "マ" dibaca Ma.'
                    ],
                    [
                        'q' => 'Istilah "Hogo-megane" atau dalam Katakana "ゴーグル" (Googuru) adalah perlengkapan K3 apa?',
                        'a' => 'Kacamata pelindung (Safety goggles)', 'b' => 'Sarung tangan', 'c' => 'Sepatu safety', 'd' => 'Helm kerja', 'cor' => 'a',
                        'exp' => 'Googuru / Hogo-megane adalah kacamata pelindung K3.'
                    ],
                    [
                        'q' => 'Kata Katakana "ヘルメット" (Herumetto) berarti perlengkapan K3...',
                        'a' => 'Helm keselamatan (Safety helmet)', 'b' => 'Rompi kerja', 'c' => 'Topi santai', 'd' => 'Masker debu', 'cor' => 'a',
                        'exp' => 'Herumetto berarti helm keselamatan kerja.'
                    ],
                    [
                        'q' => 'Huruf Katakana "ラ" dibaca sebagai...',
                        'a' => 'Ra', 'b' => 'Ri', 'c' => 'Ru', 'd' => 'Re', 'cor' => 'a',
                        'exp' => 'Huruf "ラ" dibaca Ra.'
                    ],
                    [
                        'q' => 'Alat ukur presisi "Nogiisu" (ノギス) dalam bahasa teknik manufaktur adalah...',
                        'a' => 'Jangka sorong (Vernier caliper)', 'b' => 'Meteran kain', 'c' => 'Termometer suhu', 'd' => 'Stopwatch', 'cor' => 'a',
                        'exp' => 'Nogisu adalah jangka sorong presisi (Vernier caliper).'
                    ],
                    [
                        'q' => 'Kata Katakana "マシン" (Mashin) bermakna...',
                        'a' => 'Mesin pabrik (Machine)', 'b' => 'Mobil pribadi', 'c' => 'Sepeda motor', 'd' => 'Pesawat terbang', 'cor' => 'a',
                        'exp' => 'Mashin berarti mesin.'
                    ],
                    [
                        'q' => 'Kata Katakana "スイッチ" (Suicchi) berarti...',
                        'a' => 'Sakelar / Tombol switch mesin', 'b' => 'Kabel listrik', 'c' => 'Sekring pengaman', 'd' => 'Baut mesin', 'cor' => 'a',
                        'exp' => 'Suicchi berarti sakelar tombol on/off mesin.'
                    ],
                    [
                        'q' => 'Huruf Katakana "ワ" dibaca sebagai...',
                        'a' => 'Wa', 'b' => 'Wo', 'c' => 'N', 'd' => 'Fu', 'cor' => 'a',
                        'exp' => 'Huruf "ワ" dibaca Wa.'
                    ],
                ]
            ],
            7 => [
                'title' => 'Latihan Soal Pertemuan 7: Pola Kalimat (~ wa ~ desu / dewa arimasen) & Partikel Ka',
                'desc' => 'Latihan tata bahasa pembentukan kalimat positif, negatif, dan interogatif formal.',
                'questions' => [
                    [
                        'q' => 'Partikel yang berfungsi sebagai penanda subjek / topik kalimat utama adalah...',
                        'a' => 'は (dibaca: Wa)', 'b' => 'を (dibaca: O)', 'c' => 'に (Ni)', 'd' => 'で (De)', 'cor' => 'a',
                        'exp' => 'Partikel は (wa) menandai topik pembicaraan.'
                    ],
                    [
                        'q' => 'Arti kalimat "Watashi wa Musashi no gakusei desu" adalah...',
                        'a' => 'Saya adalah peserta didik Musashi', 'b' => 'Saya bekerja di bank', 'c' => 'Anda adalah guru Musashi', 'd' => 'Dia adalah tamu pabrik', 'cor' => 'a',
                        'exp' => 'Watashi (saya), gakusei (murid/siswa).'
                    ],
                    [
                        'q' => 'Fungsi kata "Desu" di akhir kalimat predikat kata benda adalah...',
                        'a' => 'Menunjukkan kesopanan (predikat formal)', 'b' => 'Menunjukkan larangan keras', 'c' => 'Menunjukkan pertanyaan negatif', 'd' => 'Menghubungkan dua kata sifat', 'cor' => 'a',
                        'exp' => 'Desu berfungsi menegaskan predikat secara sopan dan formal.'
                    ],
                    [
                        'q' => 'Bentuk penyangkalan formal dari "Desu" adalah...',
                        'a' => 'Dewa arimasen / Ja arimasen', 'b' => 'Deshita', 'c' => 'Masen', 'd' => 'Nai desu', 'cor' => 'a',
                        'exp' => 'Dewa arimasen adalah bentuk negatif formal dari desu.'
                    ],
                    [
                        'q' => 'Partikel penanya yang diletakkan di paling akhir kalimat adalah...',
                        'a' => 'か (Ka)', 'b' => 'ね (Ne)', 'c' => 'よ (Yo)', 'd' => 'の (No)', 'cor' => 'a',
                        'exp' => 'Partikel か (ka) berfungsi seperti tanda tanya verbal.'
                    ],
                    [
                        'q' => 'Arti kalimat "Anata wa enjinia desu ka?" adalah...',
                        'a' => 'Apakah Anda seorang insinyur / teknisi?', 'b' => 'Saya adalah insinyur', 'c' => 'Siapakah teknisi itu?', 'd' => 'Anda bukan teknisi', 'cor' => 'a',
                        'exp' => 'Anata (Anda), enjinia (teknisi/engineer), ka (apakah).'
                    ],
                    [
                        'q' => 'Jawaban "Ya, benar" dalam bahasa Jepang formal adalah...',
                        'a' => 'Hai, sou desu', 'b' => 'Iie, chigaimasu', 'c' => 'Sou desu ka', 'd' => 'Arigatou', 'cor' => 'a',
                        'exp' => 'Hai, sou desu berarti Ya, benar begitu.'
                    ],
                    [
                        'q' => 'Jawaban "Bukan / Tidak benar" dalam bahasa Jepang formal adalah...',
                        'a' => 'Iie, chigaimasu / Iie, sou dewa arimasen', 'b' => 'Hai, sou desu', 'c' => 'Wakarimashita', 'd' => 'Douzo', 'cor' => 'a',
                        'exp' => 'Iie, chigaimasu berarti Bukan, tidak begitu.'
                    ],
                    [
                        'q' => 'Jika memperkenalkan rekan kerja "Kochira wa Tanaka-san desu", artinya...',
                        'a' => 'Ini adalah Tuan Tanaka', 'b' => 'Saya adalah Tanaka', 'c' => 'Di mana Tuan Tanaka?', 'd' => 'Tuan Tanaka sedang pergi', 'cor' => 'a',
                        'exp' => 'Kochira digunakan untuk memperkenalkan orang lain secara sopan.'
                    ],
                    [
                        'q' => 'Dalam kalimat "Watashi wa seito desu", kata "seito" memiliki arti...',
                        'a' => 'Siswa / Murid peserta pelatihan', 'b' => 'Guru pembimbing', 'c' => 'Manajer pabrik', 'd' => 'Direktur perusahaan', 'cor' => 'a',
                        'exp' => 'Seito (生徒) berarti siswa / murid.'
                    ],
                ]
            ],
            8 => [
                'title' => 'Latihan Soal Pertemuan 8: Kata Tunjuk Benda (Kore, Sore, Are, Dore) & Lokasi',
                'desc' => 'Latihan penunjukan posisi benda dan tempat dalam lingkungan kerja dan pabrik.',
                'questions' => [
                    [
                        'q' => 'Kata tunjuk benda yang berada dekat dengan pembicara adalah...',
                        'a' => 'これ (Kore)', 'b' => 'それ (Sore)', 'c' => 'あれ (Are)', 'd' => 'どれ (Dore)', 'cor' => 'a',
                        'exp' => 'Kore (ini) digunakan untuk benda dekat pembicara.'
                    ],
                    [
                        'q' => 'Kata tunjuk benda yang berada dekat dengan lawan bicara adalah...',
                        'a' => 'それ (Sore)', 'b' => 'これ (Kore)', 'c' => 'あれ (Are)', 'd' => 'どれ (Dore)', 'cor' => 'a',
                        'exp' => 'Sore (itu) digunakan untuk benda dekat lawan bicara.'
                    ],
                    [
                        'q' => 'Kata tunjuk benda yang jauh dari pembicara maupun lawan bicara adalah...',
                        'a' => 'あれ (Are)', 'b' => 'これ (Kore)', 'c' => 'それ (Sore)', 'd' => 'どれ (Dore)', 'cor' => 'a',
                        'exp' => 'Are (itu jauh) digunakan untuk benda yang jauh dari keduanya.'
                    ],
                    [
                        'q' => 'Kata tanya untuk menanyakan "Yang mana" di antara benda-benda adalah...',
                        'a' => 'どれ (Dore)', 'b' => 'なに (Nani)', 'c' => 'だれ (Dare)', 'd' => 'どこ (Doko)', 'cor' => 'a',
                        'exp' => 'Dore berarti yang mana.'
                    ],
                    [
                        'q' => 'Kata penunjuk tempat "Di sini" (dekat pembicara) adalah...',
                        'a' => 'ここ (Koko)', 'b' => 'そこ (Soko)', 'c' => 'あそこ (Asoko)', 'd' => 'どこ (Doko)', 'cor' => 'a',
                        'exp' => 'Koko berarti di sini.'
                    ],
                    [
                        'q' => 'Kata penunjuk tempat "Di sana" (jauh dari keduanya) adalah...',
                        'a' => 'あそこ (Asoko)', 'b' => 'ここ (Koko)', 'c' => 'そこ (Soko)', 'd' => 'どこ (Doko)', 'cor' => 'a',
                        'exp' => 'Asoko berarti di sana (jauh).'
                    ],
                    [
                        'q' => 'Kata tanya untuk menanyakan tempat "Di mana" adalah...',
                        'a' => 'どこ (Doko)', 'b' => 'だれ (Dare)', 'c' => 'いつ (Itsu)', 'd' => 'なに (Nani)', 'cor' => 'a',
                        'exp' => 'Doko berarti di mana.'
                    ],
                    [
                        'q' => 'Kalimat "Kore wa nan desu ka?" artinya adalah...',
                        'a' => 'Apakah benda ini?', 'b' => 'Siapakah orang ini?', 'c' => 'Di manakah tempat ini?', 'd' => 'Kapan ini terjadi?', 'cor' => 'a',
                        'exp' => 'Nan desu ka berarti apa ini.'
                    ],
                    [
                        'q' => 'Kalimat "Toire wa doko desu ka?" digunakan untuk menanyakan...',
                        'a' => 'Di mana lokasi toilet?', 'b' => 'Siapa di dalam toilet?', 'c' => 'Apakah toilet bersih?', 'd' => 'Kapan toilet dibuka?', 'cor' => 'a',
                        'exp' => 'Toire wa doko desu ka menanyakan letak toilet.'
                    ],
                    [
                        'q' => 'Bentuk sopan dari Koko, Soko, Asoko, Doko untuk menunjukkan arah ruangan adalah...',
                        'a' => 'Kochira, Sochira, Achira, Dochira', 'b' => 'Kore, Sore, Are, Dore', 'c' => 'Kono, Sono, Ano, Dono', 'd' => 'Konna, Sonna, Anna, Donna', 'cor' => 'a',
                        'exp' => 'Kochira, Sochira, Achira, Dochira adalah ragam sopan arah/tempat.'
                    ],
                ]
            ],
            9 => [
                'title' => 'Latihan Soal Pertemuan 9: Kata Kerja Bentuk Masu & Partikel Objek (o) dan Arah',
                'desc' => 'Latihan kata kerja tindakan sehari-hari, partikel penanda objek kerja, dan arah tujuan.',
                'questions' => [
                    [
                        'q' => 'Akhiran kata kerja bentuk positif sekarang / akan datang yang sopan adalah...',
                        'a' => '~masu', 'b' => '~mashita', 'c' => '~masen', 'd' => '~masen deshita', 'cor' => 'a',
                        'exp' => 'Bentuk ~masu menyatakan kebiasaan atau perbuatan sopan saat ini/masa depan.'
                    ],
                    [
                        'q' => 'Bentuk negatif sopan dari kata kerja berakhiran "~masu" adalah...',
                        'a' => '~masen', 'b' => '~mashita', 'c' => '~te kudasai', 'd' => '~nai', 'cor' => 'a',
                        'exp' => '~masen adalah bentuk ingkar sopan (tidak melakukan).'
                    ],
                    [
                        'q' => 'Kata kerja "Ikimasu" memiliki arti...',
                        'a' => 'Pergi', 'b' => 'Datang', 'c' => 'Pulang', 'd' => 'Makan', 'cor' => 'a',
                        'exp' => 'Ikimasu (行きます) berarti pergi.'
                    ],
                    [
                        'q' => 'Kata kerja "Kimasu" memiliki arti...',
                        'a' => 'Datang', 'b' => 'Pergi', 'c' => 'Pulang', 'd' => 'Tidur', 'cor' => 'a',
                        'exp' => 'Kimasu (来ます) berarti datang.'
                    ],
                    [
                        'q' => 'Partikel yang digunakan untuk menandai objek langsung dari kata kerja adalah...',
                        'a' => 'を (dibaca: O)', 'b' => 'は (Wa)', 'c' => 'が (Ga)', 'd' => 'に (Ni)', 'cor' => 'a',
                        'exp' => 'Partikel を menandai objek sasaran tindakan.'
                    ],
                    [
                        'q' => 'Kalimat "Koujou e ikimasu" artinya...',
                        'a' => 'Pergi ke pabrik', 'b' => 'Pulang dari pabrik', 'c' => 'Bekerja di pabrik', 'd' => 'Melihat pabrik', 'cor' => 'a',
                        'exp' => 'Koujou (pabrik) + e (arah ke) + ikimasu (pergi).'
                    ],
                    [
                        'q' => 'Partikel huruf Hiragana "へ" jika berfungsi sebagai penunjuk arah tujuan dibaca...',
                        'a' => 'E', 'b' => 'He', 'c' => 'Ha', 'd' => 'Wa', 'cor' => 'a',
                        'exp' => 'Sebagai partikel arah, huruf へ dibaca "E".'
                    ],
                    [
                        'q' => 'Kata kerja "Tabemasu" memiliki arti...',
                        'a' => 'Makan', 'b' => 'Minum', 'c' => 'Melihat', 'd' => 'Mendengar', 'cor' => 'a',
                        'exp' => 'Tabemasu (食べます) berarti makan.'
                    ],
                    [
                        'q' => 'Kalimat "Mizu o nomimasu" artinya...',
                        'a' => 'Minum air', 'b' => 'Membeli air', 'c' => 'Membuang air', 'd' => 'Merebus air', 'cor' => 'a',
                        'exp' => 'Mizu (air) + o nomimasu (minum).'
                    ],
                    [
                        'q' => 'Bentuk lampau positif (sudah dilakukan) dari kata kerja "~masu" adalah...',
                        'a' => '~mashita', 'b' => '~masen', 'c' => '~masen deshita', 'd' => '~mashou', 'cor' => 'a',
                        'exp' => '~mashita menyatakan tindakan yang telah selesai terjadi.'
                    ],
                ]
            ],
            10 => [
                'title' => 'Latihan Soal Pertemuan 10: Waktu, Jam, Menit, Hari & Partikel (kara - made)',
                'desc' => 'Latihan pengucapan jam kerja pabrik, menit, nama hari, dan batas waktu kerja.',
                'questions' => [
                    [
                        'q' => 'Kata tanya untuk menanyakan jam berapa adalah...',
                        'a' => 'Nan-ji', 'b' => 'Nan-pun', 'c' => 'Nan-nichi', 'd' => 'Nan-youbi', 'cor' => 'a',
                        'exp' => 'Nan-ji (何時) berarti jam berapa.'
                    ],
                    [
                        'q' => 'Akhiran untuk menyebutkan satuan jam dalam bahasa Jepang adalah...',
                        'a' => '~ji (時)', 'b' => '~fun (分)', 'c' => '~gatsu (月)', 'd' => '~nichi (日)', 'cor' => 'a',
                        'exp' => 'Satuan jam menggunakan ~ji (contoh: 1-ji, 2-ji).'
                    ],
                    [
                        'q' => 'Akhiran untuk menyebutkan satuan menit dalam bahasa Jepang adalah...',
                        'a' => '~fun / ~pun (分)', 'b' => '~ji', 'c' => '~byou', 'd' => '~sai', 'cor' => 'a',
                        'exp' => 'Satuan menit menggunakan ~fun atau ~pun.'
                    ],
                    [
                        'q' => 'Pasangan partikel yang bermakna "dari ... sampai ..." adalah...',
                        'a' => '~ kara ~ made', 'b' => '~ to ~ mo', 'c' => '~ ni ~ de', 'd' => '~ e ~ o', 'cor' => 'a',
                        'exp' => 'Kara (dari) dan made (sampai).'
                    ],
                    [
                        'q' => 'Hari Senin dalam bahasa Jepang disebut...',
                        'a' => 'Getsuyoubi', 'b' => 'Kayoubi', 'c' => 'Suiyoubi', 'd' => 'Mokuyoubi', 'cor' => 'a',
                        'exp' => 'Getsuyoubi (月曜日) adalah hari Senin.'
                    ],
                    [
                        'q' => 'Hari Minggu dalam bahasa Jepang disebut...',
                        'a' => 'Nichiyoubi', 'b' => 'Doyoubi', 'c' => 'Kinyoubi', 'd' => 'Getsuyoubi', 'cor' => 'a',
                        'exp' => 'Nichiyoubi (日曜日) adalah hari Minggu.'
                    ],
                    [
                        'q' => 'Kata keterangan waktu "Kinou" memiliki arti...',
                        'a' => 'Kemarin', 'b' => 'Hari ini', 'c' => 'Besok', 'd' => 'Lusa', 'cor' => 'a',
                        'exp' => 'Kinou (昨日) berarti kemarin.'
                    ],
                    [
                        'q' => 'Kata keterangan waktu "Ashita" memiliki arti...',
                        'a' => 'Besok', 'b' => 'Kemarin', 'c' => 'Hari ini', 'd' => 'Minggu lalu', 'cor' => 'a',
                        'exp' => 'Ashita (明日) berarti besok.'
                    ],
                    [
                        'q' => 'Kalimat "Shigoto wa 8-ji kara 17-ji made desu" artinya...',
                        'a' => 'Jam kerja mulai pukul 08.00 sampai 17.00', 'b' => 'Istirahat pukul 08.00 sampai 17.00', 'c' => 'Lembur pukul 08.00 sampai 17.00', 'd' => 'Rapat pukul 08.00 sampai 17.00', 'cor' => 'a',
                        'exp' => 'Shigoto (pekerjaan/kerja) kara... made (dari... sampai).'
                    ],
                    [
                        'q' => 'Kata keterangan waktu "Ima" dalam bahasa Jepang berarti...',
                        'a' => 'Sekarang', 'b' => 'Nanti', 'c' => 'Tadi', 'd' => 'Dahulu', 'cor' => 'a',
                        'exp' => 'Ima (今) berarti sekarang.'
                    ],
                ]
            ],
            11 => [
                'title' => 'Latihan Soal Pertemuan 11: Budaya Kerja 5S Musashi & Keselamatan Kerja K3',
                'desc' => 'Latihan prinsip 5S manufaktur Jepang (Seiri, Seiton, Seiso, Seiketsu, Shitsuke) & K3.',
                'questions' => [
                    [
                        'q' => 'Prinsip "5S" yang pertama adalah "Seiri" (整理), yang bermakna...',
                        'a' => 'Ringkas (Memilah barang yang perlu dan menyingkirkan yang tidak perlu)', 'b' => 'Rapi', 'c' => 'Resik', 'd' => 'Rajin', 'cor' => 'a',
                        'exp' => 'Seiri (Ringkas) adalah pemilahan barang penting dan tak penting.'
                    ],
                    [
                        'q' => 'Prinsip "Seiton" (整頓) dalam 5S memiliki arti...',
                        'a' => 'Rapi (Menata barang pada tempat yang jelas dan mudah dijangkau)', 'b' => 'Ringkas', 'c' => 'Resik', 'd' => 'Rawat', 'cor' => 'a',
                        'exp' => 'Seiton (Rapi) adalah penataan tata letak barang.'
                    ],
                    [
                        'q' => 'Prinsip "Seiso" (清掃) dalam 5S memiliki arti...',
                        'a' => 'Resik (Membersihkan area kerja dan mesin dari kotoran/debu)', 'b' => 'Rajin', 'c' => 'Ringkas', 'd' => 'Rapi', 'cor' => 'a',
                        'exp' => 'Seiso (Resik) berarti pembersihan rutin area kerja.'
                    ],
                    [
                        'q' => 'Prinsip "Seiketsu" (清潔) dalam 5S memiliki arti...',
                        'a' => 'Rawat (Mempertahankan standar kebersihan dan keteraturan 3S sebelumnya)', 'b' => 'Rajin', 'c' => 'Rapi', 'd' => 'Resik', 'cor' => 'a',
                        'exp' => 'Seiketsu (Rawat) adalah standardisasi dan pemeliharaan.'
                    ],
                    [
                        'q' => 'Prinsip "Shitsuke" (躾) dalam 5S memiliki arti...',
                        'a' => 'Rajin / Disiplin (Membiasakan diri mematuhi aturan dan standar kerja)', 'b' => 'Ringkas', 'c' => 'Rapi', 'd' => 'Resik', 'cor' => 'a',
                        'exp' => 'Shitsuke (Rajin/Disiplin) adalah pembiasaan taat SOP.'
                    ],
                    [
                        'q' => 'Semboyan keselamatan kerja utama di industri manufaktur Jepang "Anzen Daiichi" (安全第一) artinya...',
                        'a' => 'Utamakan Keselamatan (Safety First)', 'b' => 'Cepat Selesai', 'c' => 'Kualitas Nomor Satu', 'd' => 'Hemat Biaya Operasional', 'cor' => 'a',
                        'exp' => 'Anzen Daiichi berarti Keselamatan adalah yang nomor satu (Safety First).'
                    ],
                    [
                        'q' => 'Alat Pelindung Diri (APD) sarung tangan kerja dalam bahasa Jepang disebut...',
                        'a' => 'Gunte / Tebukuro', 'b' => 'Kutsushita', 'c' => 'Megane', 'd' => 'Booshi', 'cor' => 'a',
                        'exp' => 'Gunte / Tebukuro adalah sarung tangan pelindung kerja.'
                    ],
                    [
                        'q' => 'Rambu peringatan "Kiken" (危険) pada area mesin pabrik berarti...',
                        'a' => 'Bahaya (Danger)', 'b' => 'Aman', 'c' => 'Pintu Keluar', 'd' => 'Tempat Istirahat', 'cor' => 'a',
                        'exp' => 'Kiken berarti Bahaya.'
                    ],
                    [
                        'q' => 'Istilah "Hiyari-hatto" (ヒヤリ・ハット) dalam manajemen K3 Jepang mengacu pada kondisi...',
                        'a' => 'Insiden nyaris celaka (Near-miss incident)', 'b' => 'Kecelakaan fatal', 'c' => 'Mesin rusak total', 'd' => 'Kebakaran besar', 'cor' => 'a',
                        'exp' => 'Hiyari-hatto adalah kondisi nyaris celaka yang harus dilaporkan.'
                    ],
                    [
                        'q' => 'Alat pelindung pernapasan (Masker) dalam istilah pabrik disebut...',
                        'a' => 'Masuku (マスク)', 'b' => 'Beruto', 'c' => 'Googuru', 'd' => 'Kabaa', 'cor' => 'a',
                        'exp' => 'Masuku berarti masker penutup hidung dan mulut.'
                    ],
                ]
            ],
            12 => [
                'title' => 'Latihan Soal Pertemuan 12: Komunikasi Industri, Respon Kerja & Budaya Hou-Ren-So',
                'desc' => 'Latihan instruksi kerja, etika komunikasi Hou-Ren-So, dan respon terhadap atasan.',
                'questions' => [
                    [
                        'q' => 'Respon tegas saat menerima instruksi dari atasan "Saya mengerti / Siap laksanakan" adalah...',
                        'a' => 'Hai, wakarimashita (はい、分かりました)', 'b' => 'Iie, wakarimasen', 'c' => 'Sumimasen', 'd' => 'Douzo', 'cor' => 'a',
                        'exp' => 'Hai, wakarimashita adalah respon standar memahami tugas.'
                    ],
                    [
                        'q' => 'Ucapan salam terima kasih dan apresiasi kepada rekan kerja setelah jam kerja selesai adalah...',
                        'a' => 'Otsukaresamadeshita (お疲れ様でした)', 'b' => 'Ohayou', 'c' => 'Konnichiwa', 'd' => 'Itadakimasu', 'cor' => 'a',
                        'exp' => 'Otsukaresamadeshita diucapkan mengapresiasi kerja keras rekan.'
                    ],
                    [
                        'q' => 'Ungkapan "Permisi / Maaf" saat memotong pembicaraan atau meminta izin lewat adalah...',
                        'a' => 'Sumimasen (すみません)', 'b' => 'Arigatou', 'c' => 'Douzo', 'd' => 'Sayounara', 'cor' => 'a',
                        'exp' => 'Sumimasen digunakan untuk permisi atau meminta maaf santun.'
                    ],
                    [
                        'q' => 'Ungkapan "Mohon tunggu sebentar" dalam bahasa Jepang yang sopan adalah...',
                        'a' => 'Shoushou omachi kudasai', 'b' => 'Hayaku shite kudasai', 'c' => 'Mou ii desu', 'd' => 'Dame desu', 'cor' => 'a',
                        'exp' => 'Shoushou omachi kudasai berarti mohon tunggu sebentar.'
                    ],
                    [
                        'q' => 'Ungkapan peringatan keselamatan "Hati-hati!" adalah...',
                        'a' => 'Ki o tsukete kudasai', 'b' => 'Ganbatte kudasai', 'c' => 'Yame kudasai', 'd' => 'Suwatte kudasai', 'cor' => 'a',
                        'exp' => 'Ki o tsukete kudasai berarti berhati-hatilah.'
                    ],
                    [
                        'q' => 'Ungkapan "Silakan duluan" saat memberikan jalan atau mempersilakan orang lain adalah...',
                        'a' => 'Douzo', 'b' => 'Domo', 'c' => 'Hai', 'd' => 'Iie', 'cor' => 'a',
                        'exp' => 'Douzo berarti silakan.'
                    ],
                    [
                        'q' => 'Jika Anda belum memahami instruksi yang disampaikan, jawaban yang jujur dan sopan adalah...',
                        'a' => 'Sumimasen, yoku wakarimasen deshita (Maaf, saya belum paham)', 'b' => 'Hai, wakarimashita', 'c' => 'Daijoubu desu', 'd' => 'Shiranai', 'cor' => 'a',
                        'exp' => 'Wajib menyampaikan terus terang jika belum paham demi keselamatan kerja.'
                    ],
                    [
                        'q' => 'Budaya komunikasi kerja Jepang "Hou-Ren-So" adalah singkatan dari...',
                        'a' => 'Houkoku (Lapor), Renraku (Informasi), Soudan (Konsultasi)', 'b' => 'Housou, Renshuu, Souji', 'c' => 'Hayai, Rikai, Seikaku', 'd' => 'Hogo, Rench, Souchi', 'cor' => 'a',
                        'exp' => 'Hou-Ren-So: Houkoku (Lapor), Renraku (Hubungi), Soudan (Konsultasi).'
                    ],
                    [
                        'q' => 'Salam saat meninggalkan tempat kerja mendahului rekan lain adalah...',
                        'a' => 'Osaki ni shitsurei shimasu (Saya permisi mendahului)', 'b' => 'Konnichiwa', 'c' => 'Ohayou', 'd' => 'Itadakimasu', 'cor' => 'a',
                        'exp' => 'Osaki ni shitsurei shimasu diucapkan saat pulang duluan.'
                    ],
                    [
                        'q' => 'Jawaban rekan kerja kepada orang yang pamit pulang duluan ("Osaki ni shitsurei shimasu") adalah...',
                        'a' => 'Otsukaresamadeshita (Terima kasih atas kerja kerasnya)', 'b' => 'Hajimemashite', 'c' => 'Gomen nasai', 'd' => 'Douitashimashite', 'cor' => 'a',
                        'exp' => 'Dibalas dengan Otsukaresamadeshita.'
                    ],
                ]
            ],
        ];

        // Seed 12 Meeting Tests into japanese_tests & japanese_test_questions
        foreach ($meetingTestsData as $meetingNum => $data) {
            $testId = DB::table('japanese_tests')->insertGetId([
                'title' => $data['title'],
                'category' => 'per_pertemuan',
                'target_questions' => 10,
                'start_meeting' => $meetingNum,
                'end_meeting' => $meetingNum,
                'description' => $data['desc'],
                'duration_minutes' => 20,
                'pass_score' => 75,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($data['questions'] as $qIdx => $qItem) {
                DB::table('japanese_test_questions')->insert([
                    'test_id' => $testId,
                    'question_number' => $qIdx + 1,
                    'question' => $qItem['q'],
                    'option_a' => $qItem['a'],
                    'option_b' => $qItem['b'],
                    'option_c' => $qItem['c'],
                    'option_d' => $qItem['d'],
                    'correct_option' => $qItem['cor'],
                    'explanation' => $qItem['exp'],
                    'points' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Define 3 Periodic Evaluations (Per 4 Pertemuan - 15 Nomor)
        $periodicEvaluations = [
            1 => [
                'title' => 'Tes Evaluasi Gabungan Pertemuan 1 - 4 (Dasar Hiragana, Aisatsu & Industri)',
                'start' => 1, 'end' => 4,
                'desc' => 'Ujian evaluasi gabungan 4 pertemuan pertama menguji Hiragana dasar, salam kerja pabrik, angka, dan kata benda industri.',
                'questions' => [
                    ['q' => 'Salam "Ohayou gozaimasu" digunakan pada waktu...', 'a' => 'Pagi hari', 'b' => 'Siang hari', 'c' => 'Malam hari', 'd' => 'Menjelang tidur', 'cor' => 'a', 'exp' => 'Ohayou gozaimasu diucapkan pada pagi hari.'],
                    ['q' => 'Manakah huruf Hiragana untuk vokal "E" dan "O"?', 'a' => 'え dan お', 'b' => 'あ dan い', 'c' => 'う dan え', 'd' => 'お dan あ', 'cor' => 'a', 'exp' => 'Huruf "え" adalah E dan "お" adalah O.'],
                    ['q' => 'Kata "Konnichiwa" adalah sapaan umum yang digunakan pada waktu...', 'a' => 'Siang sampai sore hari', 'b' => 'Pagi buta', 'c' => 'Subuh', 'd' => 'Malam larut', 'cor' => 'a', 'exp' => 'Konnichiwa adalah sapaan siang hari.'],
                    ['q' => 'Urutan angka 1, 2, 3, 4 dalam bahasa Jepang adalah...', 'a' => 'Ichi, Ni, San, Shi/Yon', 'b' => 'Ichi, San, Ni, Shi', 'c' => 'Ni, Ichi, Go, Roku', 'd' => 'Juu, Kyuu, Hachi, Nana', 'cor' => 'a', 'exp' => 'Urutan yang benar: Ichi (1), Ni (2), San (3), Shi/Yon (4).'],
                    ['q' => 'Huruf "が" (Ga) berasal dari huruf apa yang ditambahkan tanda tenten?', 'a' => 'か (Ka)', 'b' => 'さ (Sa)', 'c' => 'た (Ta)', 'd' => 'は (Ha)', 'cor' => 'a', 'exp' => 'か (Ka) + tenten = が (Ga).'],
                    ['q' => 'Ungkapan terima kasih "Arigatou gozaimasu" dibalas dengan ucapan sopan...', 'a' => 'Douitashimashite', 'b' => 'Sayounara', 'c' => 'Hajimemashite', 'd' => 'Oyasumi', 'cor' => 'a', 'exp' => 'Douitashimashite berarti sama-sama.'],
                    ['q' => 'Kata "Koujou" dalam bahasa industri memiliki arti...', 'a' => 'Pabrik / Manufaktur', 'b' => 'Sekolah', 'c' => 'Rumah sakit', 'd' => 'Stasiun kereta', 'cor' => 'a', 'exp' => 'Koujou (工場) berarti pabrik.'],
                    ['q' => 'Penulisan Hiragana untuk kata "Nihon" (Jepang) adalah...', 'a' => 'にほん', 'b' => 'みほん', 'c' => 'りほん', 'd' => 'ひほん', 'cor' => 'a', 'exp' => 'Ni (に) + Ho (ほ) + N (ん) = にほん.'],
                    ['q' => 'Huruf "ぴ" (Pi) merupakan contoh dari bunyi khusus...', 'a' => 'Handakuon (tanda lingkaran maru)', 'b' => 'Dakuon', 'c' => 'Youon', 'd' => 'Choushi', 'cor' => 'a', 'exp' => 'Bunyi P dengan tanda maru disebut Handakuon.'],
                    ['q' => 'Sebelum menikmati makanan bersama, orang Jepang mengucapkan salam...', 'a' => 'Itadakimasu', 'b' => 'Gochisousama', 'c' => 'Tadaima', 'd' => 'Okaeri', 'cor' => 'a', 'exp' => 'Itadakimasu diucapkan sebelum makan.'],
                    ['q' => 'Setelah selesai makan, ungkapan rasa syukur yang diucapkan adalah...', 'a' => 'Gochisousama deshita', 'b' => 'Itadakimasu', 'c' => 'Sumimasen', 'd' => 'Konnichiwa', 'cor' => 'a', 'exp' => 'Gochisousama deshita diucapkan setelah selesai makan.'],
                    ['q' => 'Angka "8" dalam bahasa Jepang diucapkan sebagai...', 'a' => 'Hachi', 'b' => 'Nana', 'c' => 'Roku', 'd' => 'Kyuu', 'cor' => 'a', 'exp' => 'Hachi (八) berarti 8.'],
                    ['q' => 'Kata "Sensei" dalam lingkungan belajar bermakna...', 'a' => 'Guru / Instruktur', 'b' => 'Murid', 'c' => 'Karyawan', 'd' => 'Tamu', 'cor' => 'a', 'exp' => 'Sensei berarti guru / pengajar.'],
                    ['q' => 'Ungkapan perpisahan santai antar rekan kerja akrab adalah...', 'a' => 'Mata ashita (Sampai jumpa besok)', 'b' => 'Hajimemashite', 'c' => 'Ohayou', 'd' => 'Konbanwa', 'cor' => 'a', 'exp' => 'Mata ashita berarti sampai jumpa besok.'],
                    ['q' => 'Manakah yang merupakan baris vokal Hiragana lengkap dan berurutan?', 'a' => 'あ - い - う - え - お', 'b' => 'か - き - く - け - こ', 'c' => 'さ - し - す - せ - そ', 'd' => 'た - ち - つ - て - と', 'cor' => 'a', 'exp' => 'A-I-U-E-O adalah baris vokal dasar bahasa Jepang.'],
                ]
            ],
            2 => [
                'title' => 'Tes Evaluasi Gabungan Pertemuan 5 - 8 (Katakana, Istilah Pabrik, Pola Desu & Penunjuk)',
                'start' => 5, 'end' => 8,
                'desc' => 'Ujian evaluasi 4 pertemuan kedua menguji Katakana peralatan kerja, pola kalimat desu, kata tunjuk kore/sore/are, dan lokasi.',
                'questions' => [
                    ['q' => 'Kata "Torukurenchi" (kunci torsi) ditulis dalam Katakana sebagai...', 'a' => 'トルクレンチ', 'b' => 'トルクレンチー', 'c' => 'タルクレンチ', 'd' => 'テルクレンチ', 'cor' => 'a', 'exp' => 'Torukurenchi ditulis トルクレンチ.'],
                    ['q' => 'Fungsi utama huruf Katakana dalam dunia manufaktur industri adalah...', 'a' => 'Menuliskan istilah teknis mesin, komponen, dan serapan asing', 'b' => 'Menulis puisi tradisional', 'c' => 'Menulis surat dinas kementerian', 'd' => 'Menuliskan kanji kuno', 'cor' => 'a', 'exp' => 'Katakana digunakan untuk istilah serapan teknik dan mesin asing.'],
                    ['q' => 'Kata penunjuk "Kono hon" berbeda dengan "Kore", karena "Kono" wajib langsung diikuti oleh...', 'a' => 'Kata benda (Noun)', 'b' => 'Kata kerja', 'c' => 'Kata sifat', 'd' => 'Partikel wa', 'cor' => 'a', 'exp' => 'Kono, Sono, Ano harus menempel pada kata benda.'],
                    ['q' => 'Arti dari kalimat "Ano hito wa dare desu ka?" adalah...', 'a' => 'Siapakah orang di sebelah sana itu?', 'b' => 'Apakah orang itu sakit?', 'c' => 'Di mana orang itu?', 'd' => 'Dari mana orang itu datang?', 'cor' => 'a', 'exp' => 'Ano hito (orang itu), dare (siapa).'],
                    ['q' => 'Bentuk negatif formal dari kalimat "Tanaka-san wa nihonjin desu" adalah...', 'a' => 'Tanaka-san wa nihonjin dewa arimasen', 'b' => 'Tanaka-san wa nihonjin deshita', 'c' => 'Tanaka-san wa nihonjin ka', 'd' => 'Tanaka-san wa nihonjin masen', 'cor' => 'a', 'exp' => 'Dewa arimasen adalah bentuk negatif formal.'],
                    ['q' => 'Perlengkapan APD "Anzen-gutsu" berarti sepatu apa di area produksi?', 'a' => 'Sepatu keselamatan (Safety shoes)', 'b' => 'Sepatu olahraga', 'c' => 'Sandal jepit santai', 'd' => 'Sepatu bot air', 'cor' => 'a', 'exp' => 'Anzen (selamat/safety) + kutsu (sepatu) = Anzen-gutsu.'],
                    ['q' => 'Penulisan Katakana untuk kata "Boruto" (Baut pengencang / Bolt) adalah...', 'a' => 'ボルト', 'b' => 'ポルト', 'c' => 'ダルト', 'd' => 'ロルト', 'cor' => 'a', 'exp' => 'Boruto ditulis ボルト.'],
                    ['q' => 'Kata penunjuk arah sopan "Sochira" menunjukkan arah ke mana?', 'a' => 'Ke arah pihak lawan bicara', 'b' => 'Ke arah pembicara', 'c' => 'Ke arah yang sangat jauh', 'd' => 'Ke arah atas lantai', 'cor' => 'a', 'exp' => 'Sochira menunjuk arah lawan bicara.'],
                    ['q' => 'Partikel kepemilikan dalam kalimat "Buku milik saya" (Watashi ... hon) adalah...', 'a' => 'の (No)', 'b' => 'は (Wa)', 'c' => 'か (Ka)', 'd' => 'も (Mo)', 'cor' => 'a', 'exp' => 'Partikel の (no) menyatakan kepemilikan.'],
                    ['q' => 'Kalimat "Are wa watashi no kaban desu" memiliki arti...', 'a' => 'Itu (jauh) adalah tas milik saya', 'b' => 'Ini adalah tas Anda', 'c' => 'Di mana tas saya?', 'd' => 'Itu bukan tas saya', 'cor' => 'a', 'exp' => 'Are (itu jauh), watashi no (milik saya), kaban (tas).'],
                    ['q' => 'Kata tanya "Dare" atau bentuk lebih sopannya "Donata" digunakan menanyakan...', 'a' => 'Siapa (orang)', 'b' => 'Kapan waktu', 'c' => 'Alasan kenapa', 'd' => 'Berapa harga', 'cor' => 'a', 'exp' => 'Dare / Donata digunakan untuk menanyakan orang (siapa).'],
                    ['q' => 'Istilah kunci pas "Supana" dalam Katakana ditulis sebagai...', 'a' => 'スパナ', 'b' => 'スバナ', 'c' => 'スパニャ', 'd' => 'ズパナ', 'cor' => 'a', 'exp' => 'Supana ditulis スパナ.'],
                    ['q' => 'Arti kalimat "Jimusho wa achira desu" adalah...', 'a' => 'Kantor berada di sebelah sana', 'b' => 'Kantor berada di sini', 'c' => 'Kantor sudah tutup', 'd' => 'Di mana kantor?', 'cor' => 'a', 'exp' => 'Achira adalah bentuk sopan dari asoko (sebelah sana).'],
                    ['q' => 'Kacamata pelindung kerja di pabrik disebut dengan istilah...', 'a' => 'Hogo-megane / Googuru', 'b' => 'Masuku', 'c' => 'Tebukuro', 'd' => 'Mae-kake', 'cor' => 'a', 'exp' => 'Hogo-megane atau Googuru adalah kacamata pelindung K3.'],
                    ['q' => 'Partikel "Mo" dalam kalimat "Watashi mo kenshuusei desu" bermakna...', 'a' => 'Juga / Pun (Saya juga peserta magang/training)', 'b' => 'Hanya', 'c' => 'Bukan', 'd' => 'Mungkin', 'cor' => 'a', 'exp' => 'Partikel も (mo) menyatakan kesamaan (juga/pun).'],
                ]
            ],
            3 => [
                'title' => 'Tes Evaluasi Gabungan Pertemuan 9 - 12 (Kata Kerja, Jam/Waktu, Budaya 5S & K3 Industri)',
                'start' => 9, 'end' => 12,
                'desc' => 'Ujian evaluasi komprehensif menguji kata kerja masu, jam kerja pabrik, budaya 5S, keselamatan kerja K3, dan komunikasi Hou-Ren-So.',
                'questions' => [
                    ['q' => 'Bentuk lampau dari kata kerja "Koujou e ikimasu" (Pergi ke pabrik) adalah...', 'a' => 'Koujou e ikimashita', 'b' => 'Koujou e ikimasen', 'c' => 'Koujou e iku', 'd' => 'Koujou e itta desu', 'cor' => 'a', 'exp' => '~mashita menyatakan bentuk lampau positif.'],
                    ['q' => 'Prinsip 5S yang menekankan pemilahan barang yang diperlukan dan membuang yang tidak dipakai adalah...', 'a' => 'Seiri (Ringkas)', 'b' => 'Seiton (Rapi)', 'c' => 'Seiso (Resik)', 'd' => 'Seiketsu (Rawat)', 'cor' => 'a', 'exp' => 'Seiri adalah memilah dan menyingkirkan barang tak perlu.'],
                    ['q' => 'Kalimat "Kinou 8-ji made zangyou shimashita" memiliki arti...', 'a' => 'Kemarin saya lembur sampai pukul 8', 'b' => 'Hari ini saya lembur pukul 8', 'c' => 'Besok tidak ada lembur', 'd' => 'Kemarin libur pukul 8', 'cor' => 'a', 'exp' => 'Kinou (kemarin), made (sampai), zangyou shimashita (telah lembur).'],
                    ['q' => 'Semboyan keselamatan "Anzen Daiichi" selalu dipasang di area manufaktur karena...', 'a' => 'Keselamatan jiwa pekerja adalah prioritas paling utama di atas segalanya', 'b' => 'Supaya pabrik terlihat rapi saja', 'c' => 'Syarat formalitas dokumen', 'd' => 'Untuk menarik kunjungan tamu', 'cor' => 'a', 'exp' => 'Anzen Daiichi berarti Keselamatan Pertama / Utama.'],
                    ['q' => 'Respon yang tepat saat atasan berkata: "Tanaka-san, kono boruto o kensa shite kudasai" (Tolong periksa baut ini) adalah...', 'a' => 'Hai, wakarimashita (Baik, siap laksanakan)', 'b' => 'Iie, iya desu', 'c' => 'Doumo', 'd' => 'Sayounara', 'cor' => 'a', 'exp' => 'Hai, wakarimashita adalah jawaban sigap dan patuh terhadap SOP.'],
                    ['q' => 'Kata "Zangyou" dalam istilah ketenagakerjaan pabrik bermakna...', 'a' => 'Kerja lembur (Overtime)', 'b' => 'Jam istirahat makan', 'c' => 'Cuti tahunan', 'd' => 'Gaji bulanan', 'cor' => 'a', 'exp' => 'Zangyou (残業) berarti kerja lembur.'],
                    ['q' => 'Hari Jumat dalam bahasa Jepang disebut...', 'a' => 'Kinyoubi', 'b' => 'Mokuyoubi', 'c' => 'Suiyoubi', 'd' => 'Kayoubi', 'cor' => 'a', 'exp' => 'Kinyoubi (金曜日) adalah hari Jumat.'],
                    ['q' => 'Waktu pukul "15.30" dalam bahasa Jepang diucapkan sebagai...', 'a' => 'San-ji sanjuppun / San-ji han', 'b' => 'Yo-ji sanjuppun', 'c' => 'Ni-ji juppun', 'd' => 'Go-ji han', 'cor' => 'a', 'exp' => 'Pukul 3.30 adalah San-ji sanjuppun (atau San-ji han).'],
                    ['q' => 'Komunikasi kerja efektif Jepang "Hou-Ren-So" terdiri dari tiga pilar yaitu...', 'a' => 'Houkoku (Lapor), Renraku (Informasi), Soudan (Konsultasi)', 'b' => 'Hon, Ringo, Sora', 'c' => 'Hyouji, Rikai, Sokutei', 'd' => 'Hogo, Rench, Souchi', 'cor' => 'a', 'exp' => 'Houkoku, Renraku, Soudan adalah pilar komunikasi Jepang.'],
                    ['q' => 'Ketika jam kerja berakhir dan berpamitan pulang kepada rekan kerja, ucapan yang benar adalah...', 'a' => 'Otsukaresamadeshita. Osaki ni shitsurei shimasu', 'b' => 'Konbanwa, sayounara', 'c' => 'Ohayou gozaimasu', 'd' => 'Douzo yoroshiku', 'cor' => 'a', 'exp' => 'Ucapan pamit pulang: Osaki ni shitsurei shimasu.'],
                    ['q' => 'Alat Pelindung Diri (APD) telinga dari kebisingan mesin ("Earplug") disebut...', 'a' => 'Mimi-sen (耳栓)', 'b' => 'Gunte', 'c' => 'Googuru', 'd' => 'Masuku', 'cor' => 'a', 'exp' => 'Mimi-sen adalah penutup telinga peredam bising.'],
                    ['q' => 'Prinsip "Shitsuke" dalam 5S adalah pembiasaan diri untuk...', 'a' => 'Disiplin mematuhi standar kerja, SOP, dan etika kerja', 'b' => 'Menyapu lantai setiap menit', 'c' => 'Menghafal nomor mesin', 'd' => 'Mengecat dinding pabrik', 'cor' => 'a', 'exp' => 'Shitsuke adalah kedisiplinan dan kepatuhan aturan.'],
                    ['q' => 'Kalimat "Maiasa 7-ji ni okimasu" artinya...', 'a' => 'Setiap pagi bangun pukul 07.00', 'b' => 'Tadi pagi bangun pukul 07.00', 'c' => 'Besok pagi bangun pukul 07.00', 'd' => 'Tidak pernah bangun pukul 07.00', 'cor' => 'a', 'exp' => 'Maiasa (setiap pagi), okimasu (bangun tidur).'],
                    ['q' => 'Partikel "de" dalam kalimat "Basu de kaisha e ikimasu" menyatakan...', 'a' => 'Alat / Sarana transportasi yang dipakai (Naik bus)', 'b' => 'Waktu tiba di kantor', 'c' => 'Tujuan akhir perjalanan', 'd' => 'Orang yang menemani', 'cor' => 'a', 'exp' => 'Partikel で menyatakan sarana atau alat.'],
                    ['q' => 'Respon sopan saat pengawas kerja mengingatkan "Ki o tsukete kudasai" (Hati-hati) adalah...', 'a' => 'Hai, arigatou gozaimasu (Baik, terima kasih atas perhatiannya)', 'b' => 'Iie, chigaimasu', 'c' => 'Wakarimashita deshita', 'd' => 'Sayounara', 'cor' => 'a', 'exp' => 'Hai, arigatou gozaimasu adalah respon sopan menerima peringatan K3.'],
                ]
            ],
        ];

        // Replace questions in existing 3 periodic tests or re-seed them with exact 15 questions
        foreach ($periodicEvaluations as $evalIdx => $evalData) {
            $existingTest = DB::table('japanese_tests')
                ->where('start_meeting', $evalData['start'])
                ->where('end_meeting', $evalData['end'])
                ->first();

            $evalTestId = null;
            if ($existingTest) {
                DB::table('japanese_tests')->where('id', $existingTest->id)->update([
                    'title' => $evalData['title'],
                    'category' => 'per_4_pertemuan',
                    'target_questions' => 15,
                    'description' => $evalData['desc'],
                    'duration_minutes' => 35,
                    'pass_score' => 75,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
                $evalTestId = $existingTest->id;
                // Delete old sample questions for this test to replace with full 15 questions
                DB::table('japanese_test_questions')->where('test_id', $evalTestId)->delete();
            } else {
                $evalTestId = DB::table('japanese_tests')->insertGetId([
                    'title' => $evalData['title'],
                    'category' => 'per_4_pertemuan',
                    'target_questions' => 15,
                    'start_meeting' => $evalData['start'],
                    'end_meeting' => $evalData['end'],
                    'description' => $evalData['desc'],
                    'duration_minutes' => 35,
                    'pass_score' => 75,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($evalData['questions'] as $qIdx => $qItem) {
                DB::table('japanese_test_questions')->insert([
                    'test_id' => $evalTestId,
                    'question_number' => $qIdx + 1,
                    'question' => $qItem['q'],
                    'option_a' => $qItem['a'],
                    'option_b' => $qItem['b'],
                    'option_c' => $qItem['c'],
                    'option_d' => $qItem['d'],
                    'correct_option' => $qItem['cor'],
                    'explanation' => $qItem['exp'],
                    'points' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Delete meeting category tests
        $meetingTestIds = DB::table('japanese_tests')->where('category', 'per_pertemuan')->pluck('id');
        DB::table('japanese_test_questions')->whereIn('test_id', $meetingTestIds)->delete();
        DB::table('japanese_tests')->whereIn('id', $meetingTestIds)->delete();

        if (Schema::hasColumn('japanese_tests', 'category')) {
            Schema::table('japanese_tests', function (Blueprint $table) {
                $table->dropColumn(['category', 'target_questions']);
            });
        }
    }
};
