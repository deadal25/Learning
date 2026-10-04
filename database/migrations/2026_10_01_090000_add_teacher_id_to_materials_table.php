<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('level_id')->constrained('users')->nullOnDelete();
        });

        // Backfill existing materials based on subject
        $sarah = DB::table('users')->where('role', 'admin')->where('subject_id', 1)->orderBy('id')->first();
        $kenji = DB::table('users')->where('role', 'admin')->where('subject_id', 2)->orderBy('id')->first();
        $hendra = DB::table('users')->where('role', 'admin')->where('subject_id', 3)->orderBy('id')->first();

        if ($sarah) {
            $englishLevelIds = DB::table('levels')->where('subject_id', 1)->pluck('id');
            DB::table('materials')->whereIn('level_id', $englishLevelIds)->update(['teacher_id' => $sarah->id]);
        }

        if ($kenji) {
            $japaneseLevelIds = DB::table('levels')->where('subject_id', 2)->pluck('id');
            DB::table('materials')->whereIn('level_id', $japaneseLevelIds)->update(['teacher_id' => $kenji->id]);
        }

        if ($hendra) {
            $mathLevelIds = DB::table('levels')->where('subject_id', 3)->pluck('id');
            DB::table('materials')->whereIn('level_id', $mathLevelIds)->update(['teacher_id' => $hendra->id]);
        }
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
