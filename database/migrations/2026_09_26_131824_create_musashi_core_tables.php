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
        // 1. Subjects table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable()->default('book');
            $table->string('badge_color')->default('#4f46e5');
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Levels table
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('name'); // e.g. Basic, Beginner, Middle, Advanced
            $table->integer('order')->default(1); // 1, 2, 3...
            $table->text('description')->nullable();
            $table->integer('required_points')->default(100);
            $table->timestamps();

            $table->unique(['subject_id', 'order']);
        });

        // 3. Materials table
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // ppt, pptx, pdf, slide_url
            $table->string('slide_url')->nullable();
            $table->integer('order')->default(1);
            $table->timestamps();
        });

        // 4. Exercises table (10 questions per level)
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->integer('question_number')->default(1); // 1 - 10
            $table->text('question');
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->enum('correct_option', ['a', 'b', 'c', 'd']);
            $table->text('explanation')->nullable();
            $table->integer('points')->default(10);
            $table->timestamps();
        });

        // 5. User progress tracking (Current active level and points per subject)
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('current_level_id')->constrained('levels')->cascadeOnDelete();
            $table->integer('current_points')->default(0); // 0 - 100
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'subject_id']);
        });

        // 6. User Level Status (Per-level unlock and score status)
        Schema::create('user_level_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->integer('points')->default(0);
            $table->boolean('is_unlocked')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'level_id']);
        });

        // 7. Exercise Attempts (Historical log of answers)
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->string('selected_option', 5);
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
        Schema::dropIfExists('user_level_statuses');
        Schema::dropIfExists('user_progress');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('subjects');
    }
};
