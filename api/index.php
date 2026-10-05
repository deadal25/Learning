<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Direktori storage dan cache sementara di /tmp
$tmp = '/tmp';
$tmpStorage = $tmp . '/storage';
$bootstrapCache = $tmpStorage . '/bootstrap_cache';

$dirs = [
    $tmpStorage,
    $bootstrapCache,
    $tmpStorage . '/framework',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/framework/cache',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/logs',
    $tmpStorage . '/app',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Konfigurasi Database Cloud (Neon PostgreSQL)
$defaultNeonUrl = 'postgresql://neondb_owner:npg_h3QAnBczWKd0@ep-billowing-fire-b8ote5i9.c-14.us-east-1.aws.neon.tech/neondb?sslmode=require';
$envDbUrl = getenv('DATABASE_URL_UNPOOLED')
    ?: (getenv('DATABASE_URL')
    ?: (getenv('POSTGRES_URL')
    ?: (getenv('POSTGRES_URL_NON_POOLING')
    ?: ($_ENV['DATABASE_URL']
    ?? ($_ENV['POSTGRES_URL']
    ?? ($_SERVER['DATABASE_URL']
    ?? ($_SERVER['POSTGRES_URL']
    ?? $defaultNeonUrl)))))));

// Set environment variables penting agar Laravel di Vercel menggunakan PostgreSQL secara persisten
$envVars = [
    'APP_NAME' => 'Learning Musashi',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'true',
    'APP_KEY' => 'base64:KZI03qL48lP/EqiTvXHGNcIxg/gRIAxqn7DqgwTjVlE=',
    'APP_URL' => 'https://learning-musashi.vercel.app',
    'APP_STORAGE' => $tmpStorage,
    'VIEW_COMPILED_PATH' => $tmpStorage . '/framework/views',
    'APP_CONFIG_CACHE' => $bootstrapCache . '/config.php',
    'APP_EVENTS_CACHE' => $bootstrapCache . '/events.php',
    'APP_PACKAGES_CACHE' => $bootstrapCache . '/packages.php',
    'APP_ROUTES_CACHE' => $bootstrapCache . '/routes.php',
    'APP_SERVICES_CACHE' => $bootstrapCache . '/services.php',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'cookie',
    'LOG_CHANNEL' => 'stderr',
    'VERCEL' => '1',
    'DB_CONNECTION' => 'pgsql',
    'DATABASE_URL' => $envDbUrl,
    'POSTGRES_URL' => $envDbUrl,
    'DATABASE_URL_UNPOOLED' => $envDbUrl,
    'PGHOST' => 'ep-billowing-fire-b8ote5i9.c-14.us-east-1.aws.neon.tech',
    'PGUSER' => 'neondb_owner',
    'PGDATABASE' => 'neondb',
    'PGPASSWORD' => 'npg_h3QAnBczWKd0',
    'DB_DATABASE' => $tmp . '/database.sqlite',
];

foreach ($envVars as $key => $val) {
    putenv("{$key}={$val}");
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
}

// Paksa request di Vercel selalu dideteksi sebagai HTTPS agar asset/css/gambar tidak terblokir Mixed Content
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['HTTP_X_FORWARDED_PORT'] = '443';
if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'learning-musashi.vercel.app';
}

// Siapkan direktori storage public yang bisa ditulisi (read-write) untuk upload materi baru di Vercel
$tmpStoragePublic = $tmpStorage . '/app/public';
$tmpStorageMaterials = $tmpStoragePublic . '/materials';
if (is_link($tmpStoragePublic)) {
    @unlink($tmpStoragePublic);
}
if (!is_dir($tmpStoragePublic)) {
    @mkdir($tmpStoragePublic, 0777, true);
}
if (!is_dir($tmpStorageMaterials)) {
    @mkdir($tmpStorageMaterials, 0777, true);
}
ini_set('upload_tmp_dir', $tmp);

// Siapkan database SQLite dari bundle HANYA jika fallback SQLite yang digunakan
if (!$hasExternalDb) {
    $tmpDb = $tmp . '/database.sqlite';
    $bundledDb = __DIR__ . '/../database/database.sqlite';
    if (!file_exists($tmpDb) || (file_exists($bundledDb) && filemtime($bundledDb) > @filemtime($tmpDb))) {
        if (file_exists($bundledDb)) {
            @copy($bundledDb, $tmpDb);
        } else {
            @touch($tmpDb);
        }
    }
}

// Tangkap shutdown fatal error agar tampil di browser
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo "<div style='font-family:sans-serif;padding:24px;background:#fef2f2;border:1px solid #f87171;border-radius:8px;margin:20px;'>";
        echo "<h2 style='color:#991b1b;margin-top:0;'>Fatal Error Terdeteksi di Vercel</h2>";
        echo "<p><strong>Pesan:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . " (Baris: " . $error['line'] . ")</p>";
        echo "</div>";
    }
});

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<div style='font-family:sans-serif;padding:24px;background:#fef2f2;border:1px solid #f87171;border-radius:8px;margin:20px;'>";
    echo "<h2 style='color:#991b1b;margin-top:0;'>Exception Terdeteksi di Vercel</h2>";
    echo "<p><strong>Pesan:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Baris: " . $e->getLine() . ")</p>";
    echo "<pre style='background:#1e293b;color:#f8fafc;padding:16px;border-radius:6px;overflow-x:auto;font-size:12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
