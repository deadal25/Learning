<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->whereNotNull('email')->update([
            'email' => DB::raw('LOWER(TRIM(email))')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
