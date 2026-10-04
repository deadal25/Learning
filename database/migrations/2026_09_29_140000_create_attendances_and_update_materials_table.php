<?php

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
        // 1. Create Attendances Table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 30); // hadir, izin_keterangan, izin_tanpa_keterangan
            $table->text('notes')->nullable(); // Alasan/keterangan jika izin
            $table->time('check_in_time')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['date', 'status']);
        });

        // 2. Add content field to materials for rich text explanations
        if (Schema::hasTable('materials') && !Schema::hasColumn('materials', 'content')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->longText('content')->nullable()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'content')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('content');
            });
        }

        Schema::dropIfExists('attendances');
    }
};
