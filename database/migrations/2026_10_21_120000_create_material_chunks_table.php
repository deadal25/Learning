<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('material_chunks')) {
            Schema::create('material_chunks', function (Blueprint $table) {
                $table->id();
                $table->string('upload_id', 100)->index();
                $table->integer('chunk_index')->default(0);
                $table->integer('total_chunks')->default(1);
                $table->string('file_name')->nullable();
                $table->string('file_type', 20)->nullable();
                $table->string('file_path')->nullable()->index();
                $table->longText('chunk_data')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_chunks');
    }
};
