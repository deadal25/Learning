<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncToRemoteDatabaseCommand extends Command
{
    protected $signature = 'db:sync-to-remote {--target= : Target connection name (mysql, pgsql, or auto from DATABASE_URL)} {--run-migrations : Run migrations on the target connection first}';

    protected $description = 'Sync all SQLite records (users, students, levels, exercises, materials) to a remote MySQL/PostgreSQL database';

    public function handle()
    {
        $target = $this->option('target');

        if (!$target) {
            $dbUrl = env('DATABASE_URL', env('DB_URL', ''));
            if (str_starts_with($dbUrl, 'postgres://') || str_starts_with($dbUrl, 'postgresql://')) {
                $target = 'pgsql';
            } elseif (str_starts_with($dbUrl, 'mysql://')) {
                $target = 'mysql';
            } else {
                $target = config('database.default');
            }
        }

        if ($target === 'sqlite') {
            $this->error("Koneksi target masih 'sqlite'. Silakan tentukan target (misal --target=mysql atau --target=pgsql) atau atur DATABASE_URL di .env.");
            return 1;
        }

        $this->info("Menghubungkan ke database target: [{$target}]...");

        try {
            DB::connection($target)->getPdo();
            $this->info("Koneksi ke target [{$target}] BERHASIL.");
        } catch (\Throwable $e) {
            $this->error("Gagal terhubung ke database target [{$target}]: " . $e->getMessage());
            return 1;
        }

        // Run migrations on target connection
        $this->info("Menjalankan migrasi pada database target...");
        $this->call('migrate', [
            '--database' => $target,
            '--force' => true,
        ]);

        $tables = [
            'users',
            'subjects',
            'english_classes',
            'levels',
            'materials',
            'exercises',
            'student_enrollments',
            'attendances',
            'user_level_statuses',
            'user_progress',
            'exercise_attempts',
            'exercise_answers',
            'japanese_evaluations',
            'japanese_test_results',
        ];

        $sourceConn = DB::connection('sqlite');
        $targetConn = DB::connection($target);

        // Temporarily disable foreign keys on target if supported
        if ($target === 'mysql') {
            $targetConn->statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($tables as $table) {
            if (!Schema::connection('sqlite')->hasTable($table) || !Schema::connection($target)->hasTable($table)) {
                continue;
            }

            $sourceCount = $sourceConn->table($table)->count();
            if ($sourceCount === 0) {
                continue;
            }

            $this->line("Menyinkronkan tabel <comment>{$table}</comment> ({$sourceCount} baris)...");

            $rows = $sourceConn->table($table)->get();
            $chunks = $rows->chunk(200);

            foreach ($chunks as $chunk) {
                $records = json_decode(json_encode($chunk), true);
                if ($target === 'pgsql') {
                    foreach ($records as &$record) {
                        foreach ($record as $key => $val) {
                            if (in_array($key, ['is_active', 'is_unlocked', 'is_completed'])) {
                                $record[$key] = (bool) $val;
                            }
                        }
                    }
                    unset($record);
                }

                try {
                    $targetConn->table($table)->upsert($records, ['id']);
                } catch (\Throwable $e) {
                    foreach ($records as $record) {
                        $targetConn->table($table)->updateOrInsert(
                            ['id' => $record['id']],
                            $record
                        );
                    }
                }
            }

            // Sync PostgreSQL serial sequences if needed
            if ($target === 'pgsql') {
                try {
                    $targetConn->statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE(MAX(id), 1)) FROM {$table};");
                } catch (\Throwable $e) {}
            }
        }

        if ($target === 'mysql') {
            $targetConn->statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info("Sinkronisasi data ke database target [{$target}] SELESAI DENGAN SUKSES!");
        return 0;
    }
}
