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
    }

    public function down(): void
    {
        // Tetap simpan data materi agar tidak terhapus
    }
};
