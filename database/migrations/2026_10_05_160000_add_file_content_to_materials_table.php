<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('materials') && !Schema::hasColumn('materials', 'file_content')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->longText('file_content')->nullable()->after('file_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'file_content')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('file_content');
            });
        }
    }
};
