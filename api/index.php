<?php

// Pastikan direktori storage sementara di /tmp tersedia untuk lingkungan serverless
$tmpStorage = '/tmp/storage';
$folders = [
    $tmpStorage,
    $tmpStorage . '/framework',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/framework/cache',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/logs',
    $tmpStorage . '/app',
    $tmpStorage . '/app/public',
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        @mkdir($folder, 0777, true);
    }
}

// Persiapkan database sqlite sementara di /tmp jika menggunakan SQLite
$tmpDb = '/tmp/database.sqlite';
if (!file_exists($tmpDb)) {
    $bundledDb = __DIR__ . '/../database/database.sqlite';
    if (file_exists($bundledDb)) {
        @copy($bundledDb, $tmpDb);
    } else {
        @touch($tmpDb);
    }
}

// Arahkan request Vercel ke front controller Laravel
require __DIR__ . '/../public/index.php';
