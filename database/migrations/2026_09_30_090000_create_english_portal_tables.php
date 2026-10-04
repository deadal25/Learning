<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add division and class_name to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'division')) {
                $table->string('division')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'class_name')) {
                $table->string('class_name')->nullable()->after('division');
            }
        });

        // 2. Add class_name to materials
        Schema::table('materials', function (Blueprint $table) {
            if (!Schema::hasColumn('materials', 'class_name')) {
                $table->string('class_name')->nullable()->after('level_id');
            }
        });

        // 3. Create english_classes table
        if (!Schema::hasTable('english_classes')) {
            Schema::create('english_classes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('level_name')->default('Beginner'); // Beginner / Intermediate / Advanced
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed initial classes from Absensi.xlsx
            $initialClasses = [
                ['name' => 'Class B1', 'level_name' => 'Beginner', 'description' => 'Kelas Pemula 1 (Level Dasar)', 'sort_order' => 1],
                ['name' => 'Class B2', 'level_name' => 'Beginner', 'description' => 'Kelas Pemula 2 (Level Dasar)', 'sort_order' => 2],
                ['name' => 'Class B3', 'level_name' => 'Beginner', 'description' => 'Kelas Pemula 3 (Level Dasar)', 'sort_order' => 3],
                ['name' => 'Class B4', 'level_name' => 'Beginner', 'description' => 'Kelas Pemula 4 (Level Dasar)', 'sort_order' => 4],
                ['name' => 'Class B5', 'level_name' => 'Beginner', 'description' => 'Kelas Pemula 5 (Level Dasar)', 'sort_order' => 5],
                ['name' => 'Class B6', 'level_name' => 'Beginner', 'description' => 'Kelas Pemula 6 (Level Dasar)', 'sort_order' => 6],
                ['name' => 'Class G',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah G (Level Menengah)', 'sort_order' => 7],
                ['name' => 'Class H',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah H (Level Menengah)', 'sort_order' => 8],
                ['name' => 'Class I',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah I (Level Menengah)', 'sort_order' => 9],
                ['name' => 'Class J',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah J (Level Menengah)', 'sort_order' => 10],
                ['name' => 'Class K',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah K (Level Menengah)', 'sort_order' => 11],
                ['name' => 'Class L',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah L (Level Menengah)', 'sort_order' => 12],
                ['name' => 'Class M',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah M (Level Menengah)', 'sort_order' => 13],
                ['name' => 'Class N',  'level_name' => 'Intermediate', 'description' => 'Kelas Menengah N (Level Menengah)', 'sort_order' => 14],
            ];

            foreach ($initialClasses as $cls) {
                DB::table('english_classes')->insert(array_merge($cls, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 4. Create english_grades table
        if (!Schema::hasTable('english_grades')) {
            Schema::create('english_grades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('class_name');
                $table->integer('week')->default(1);
                $table->decimal('meeting_1', 5, 2)->default(0);
                $table->decimal('meeting_2', 5, 2)->default(0);
                $table->decimal('meeting_3', 5, 2)->default(0);
                $table->decimal('meeting_4', 5, 2)->default(0);
                $table->decimal('attendance_score', 5, 2)->default(0);
                $table->decimal('fluency', 5, 2)->default(0);
                $table->decimal('grammar', 5, 2)->default(0);
                $table->decimal('pronunciation', 5, 2)->default(0);
                $table->decimal('vocabulary', 5, 2)->default(0);
                $table->decimal('total_exam', 5, 2)->default(0);
                $table->decimal('final_score', 5, 2)->default(0);
                $table->text('feedback')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'class_name', 'week'], 'english_grades_student_class_week_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('english_grades');
        Schema::dropIfExists('english_classes');

        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'class_name')) {
                $table->dropColumn('class_name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'division')) {
                $table->dropColumn('division');
            }
            if (Schema::hasColumn('users', 'class_name')) {
                $table->dropColumn('class_name');
            }
        });
    }
};
