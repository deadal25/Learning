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

// Set environment variables penting agar Laravel tidak menulis ke filesystem read-only
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
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $tmp . '/database.sqlite',
    'VERCEL' => '1',
];

foreach ($envVars as $key => $val) {
    if (!getenv($key)) {
        putenv("{$key}={$val}");
    }
    $_ENV[$key] = $_ENV[$key] ?? $val;
    $_SERVER[$key] = $_SERVER[$key] ?? $val;
}

// Paksa request di Vercel selalu dideteksi sebagai HTTPS agar asset/css/gambar tidak terblokir Mixed Content
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['HTTP_X_FORWARDED_PORT'] = '443';
if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'learning-musashi.vercel.app';
}

// Hubungkan storage/app/public jika ada berkas materi dari bundle
$bundledStoragePublic = __DIR__ . '/../storage/app/public';
$tmpStoragePublic = $tmpStorage . '/app/public';
if (is_dir($bundledStoragePublic) && !file_exists($tmpStoragePublic)) {
    @symlink($bundledStoragePublic, $tmpStoragePublic);
}

// Siapkan database SQLite dari bundle jika belum ada di /tmp
$tmpDb = $tmp . '/database.sqlite';
if (!file_exists($tmpDb)) {
    $bundledDb = __DIR__ . '/../database/database.sqlite';
    if (file_exists($bundledDb)) {
        @copy($bundledDb, $tmpDb);
    } else {
        @touch($tmpDb);
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
