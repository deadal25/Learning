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
        Schema::create('meeting_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('class_name', 100);
            $table->string('class_group', 50)->index();
            $table->foreignId('parent_id')->nullable()->constrained('meeting_comments')->cascadeOnDelete();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();

            $table->index(['level_id', 'class_group']);
            $table->index(['subject_id', 'class_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_comments');
    }
};
