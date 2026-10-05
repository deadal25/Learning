<?php

use App\Models\Level;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan semua level tingkatan pertemuan aktif agar dapat diakses
        Level::where('is_active', false)->update(['is_active' => true]);

        $enTeacherId = User::where('role', 'admin')->where('subject_id', 1)->value('id') ?? 1116;
        $jpTeacherId = User::where('role', 'admin')->where('subject_id', 2)->value('id') ?? 4;

        // 2. Pastikan Materi Pertemuan 1 Bahasa Inggris (PPT) selalu ada dan aktif
        $enLvl1 = Level::where('subject_id', 1)->where('order', 1)->first();
        if ($enLvl1) {
            $m1 = Material::where('level_id', $enLvl1->id)->where(function ($q) {
                $q->where('file_name', 'like', '%PPT%')
                  ->orWhere('file_path', 'like', '%QrzRqgcPCCSKZkWcKVZ5MVfwjyt1XGr46dndpvgO%')
                  ->orWhere('title', 'like', '%Pertemuan 1%')
                  ->orWhere('title', 'like', '%Materi tes%');
            })->first();

            if ($m1) {
                $m1->update([
                    'title' => 'Materi Pertemuan 1: Bahasa Inggris (PPT Slide)',
                    'file_name' => 'PPT BULAN 2.pptx',
                    'file_path' => 'materials/QrzRqgcPCCSKZkWcKVZ5MVfwjyt1XGr46dndpvgO.pptx',
                    'file_type' => 'pptx',
                    'is_active' => true,
                    'class_name' => null,
                ]);
            } else {
                Material::create([
                    'level_id' => $enLvl1->id,
                    'teacher_id' => $enTeacherId,
                    'class_name' => null,
                    'title' => 'Materi Pertemuan 1: Bahasa Inggris (PPT Slide)',
                    'description' => 'Slide presentasi pengantar konsep, kosakata, dan topik belajar Pertemuan 1.',
                    'file_name' => 'PPT BULAN 2.pptx',
                    'file_path' => 'materials/QrzRqgcPCCSKZkWcKVZ5MVfwjyt1XGr46dndpvgO.pptx',
                    'file_type' => 'pptx',
                    'order' => 1,
                    'is_active' => true,
                ]);
            }
        }

        // 3. Pastikan Materi Pertemuan 1 Bahasa Jepang (PPT) selalu ada dan aktif
        $jpLvl1 = Level::where('subject_id', 2)->where('order', 1)->first();
        if ($jpLvl1) {
            $mJp = Material::where('level_id', $jpLvl1->id)->first();
            if ($mJp) {
                $mJp->update([
                    'title' => 'Materi Pertemuan 1: Bahasa Jepang (PPT Slide)',
                    'file_name' => 'Activity Plan.pptx',
                    'file_path' => 'materials/Uea4T4Esv50W0IZY7ugUGJPwO5UsBqBvLC1qdSVw.pptx',
                    'file_type' => 'pptx',
                    'is_active' => true,
                    'class_name' => null,
                ]);
            } else {
                Material::create([
                    'level_id' => $jpLvl1->id,
                    'teacher_id' => $jpTeacherId,
                    'class_name' => null,
                    'title' => 'Materi Pertemuan 1: Bahasa Jepang (PPT Slide)',
                    'description' => 'Slide presentasi pengantar materi dan topik belajar Pertemuan 1 Bahasa Jepang.',
                    'file_name' => 'Activity Plan.pptx',
                    'file_path' => 'materials/Uea4T4Esv50W0IZY7ugUGJPwO5UsBqBvLC1qdSVw.pptx',
                    'file_type' => 'pptx',
                    'order' => 1,
                    'is_active' => true,
                ]);
            }
        }

        // 4. Pastikan Materi Pertemuan 2 Bahasa Inggris (PDF) selalu ada dan aktif
        $enLvl2 = Level::where('subject_id', 1)->where('order', 2)->first();
        if ($enLvl2) {
            $m2 = Material::where('level_id', $enLvl2->id)->first();
            if (!$m2) {
                Material::create([
                    'level_id' => $enLvl2->id,
                    'teacher_id' => $enTeacherId,
                    'class_name' => null,
                    'title' => 'Modul Dokumen PDF: Pertemuan 2',
                    'description' => 'Dokumen PDF modul bacaan dan materi pendalaman Pertemuan 2.',
                    'file_name' => 'Modul_Pembelajaran_Pertemuan_2.pdf',
                    'file_path' => 'materials/299evNaTJwxWiRSahlyptET5YUqOcDapcHVynmi6.pdf',
                    'file_type' => 'pdf',
                    'order' => 2,
                    'is_active' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tetap simpan data materi agar tidak terhapus
    }
};
