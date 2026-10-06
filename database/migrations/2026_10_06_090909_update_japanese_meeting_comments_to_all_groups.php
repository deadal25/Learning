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
        if (Schema::hasTable('meeting_comments')) {
            \Illuminate\Support\Facades\DB::table('meeting_comments')
                ->where('subject_id', 2)
                ->orWhere('class_group', 'like', 'Grup%')
                ->update(['class_group' => 'Semua Grup']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for group unification
    }
};
