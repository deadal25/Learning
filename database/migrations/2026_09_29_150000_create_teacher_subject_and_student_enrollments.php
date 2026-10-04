<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add subject_id and class_code to users table for teachers
        if (!Schema::hasColumn('users', 'subject_id') || !Schema::hasColumn('users', 'class_code')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'subject_id')) {
                    $table->foreignId('subject_id')->nullable()->after('role')->constrained('subjects')->nullOnDelete();
                }
                if (!Schema::hasColumn('users', 'class_code')) {
                    $table->string('class_code', 50)->nullable()->unique()->after('subject_id');
                }
            });
        }

        // 2. Create student_enrollments table
        if (!Schema::hasTable('student_enrollments')) {
            Schema::create('student_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('class_code', 50)->nullable();
                $table->timestamp('enrolled_at')->useCurrent();
                $table->string('status', 20)->default('active');
                $table->timestamps();

                $table->unique(['student_id', 'subject_id']);
            });
        }

        // Ensure subjects 1, 2, 3 exist so foreign key constraint passes
        if (!DB::table('subjects')->where('id', 1)->exists()) {
            DB::table('subjects')->insertOrIgnore([
                ['id' => 1, 'name' => 'Bahasa Inggris', 'slug' => 'bahasa-inggris', 'order' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Bahasa Jepang', 'slug' => 'bahasa-jepang', 'order' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Matematika', 'slug' => 'matematika', 'order' => 3, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Setup default teacher subjects and codes
        $sarah = User::where('email', 'sarah@musashi.id')->first();
        if ($sarah) {
            $sarah->update([
                'subject_id' => 1, // Bahasa Inggris
                'class_code' => 'ENG-SARAH',
            ]);
        }

        $kenji = User::where('email', 'kenji@musashi.id')->first();
        if ($kenji) {
            $kenji->update([
                'subject_id' => 2, // Bahasa Jepang
                'class_code' => 'JPN-KENJI',
            ]);
        }

        // Create or update Guru Matematika
        $superAdmin = User::where('role', User::ROLE_SUPER_ADMIN)->first();
        $hendra = User::firstOrCreate(
            ['email' => 'hendra@musashi.id'],
            [
                'name' => 'Pak Hendra Wijaya, M.Pd',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '081233445566',
                'status' => 'active',
                'created_by' => $superAdmin?->id,
                'subject_id' => 3, // Matematika
                'class_code' => 'MATH-HENDRA',
            ]
        );
        if ($hendra && !$hendra->class_code) {
            $hendra->update([
                'subject_id' => 3,
                'class_code' => 'MATH-HENDRA',
            ]);
        }

        // 4. Enroll initial existing students so their demo progress remains accessible
        $budi = User::where('email', 'budi@musashi.id')->first();
        if ($budi) {
            // Enroll Budi in Bahasa Inggris & Bahasa Jepang
            DB::table('student_enrollments')->updateOrInsert(
                ['student_id' => $budi->id, 'subject_id' => 1],
                [
                    'teacher_id' => $sarah?->id,
                    'class_code' => 'ENG-SARAH',
                    'enrolled_at' => now(),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            DB::table('student_enrollments')->updateOrInsert(
                ['student_id' => $budi->id, 'subject_id' => 2],
                [
                    'teacher_id' => $kenji?->id,
                    'class_code' => 'JPN-KENJI',
                    'enrolled_at' => now(),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $siti = User::where('email', 'siti@musashi.id')->first();
        if ($siti) {
            // Enroll Siti in Bahasa Jepang
            DB::table('student_enrollments')->updateOrInsert(
                ['student_id' => $siti->id, 'subject_id' => 2],
                [
                    'teacher_id' => $kenji?->id,
                    'class_code' => 'JPN-KENJI',
                    'enrolled_at' => now(),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['subject_id', 'class_code']);
        });
    }
};
