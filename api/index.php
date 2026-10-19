<?php

// Nonaktifkan output warning/notice PHP langsung ke browser agar tidak merusak tampilan HTML di Vercel
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

// Tangkap shutdown fatal error agar selalu tampil jelas bila terjadi crash
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

// Siapkan database SQLite dari bundle proyek ke direktori /tmp/database.sqlite (read-write fallback)
$tmpDb = $tmp . '/database.sqlite';
$bundledDb = __DIR__ . '/../database/database.sqlite';
$versionFile = $tmp . '/.db_version';
$bundledVersion = @filemtime($bundledDb) . '_' . @filesize($bundledDb);
$installedVersion = file_exists($versionFile) ? @file_get_contents($versionFile) : '';

if (!file_exists($tmpDb) || filesize($tmpDb) === 0 || $installedVersion !== $bundledVersion) {
    if (file_exists($bundledDb) && filesize($bundledDb) > 0) {
        @copy($bundledDb, $tmpDb);
        @chmod($tmpDb, 0666);
        @file_put_contents($versionFile, $bundledVersion);
    } else {
        @touch($tmpDb);
        @chmod($tmpDb, 0666);
    }
}

// Helper untuk membaca env tanpa bergantung pada helper Laravel env() yang belum termuat
$getEnvVal = function ($key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    return $default;
};

// Default Neon PostgreSQL connection configuration
$defaultNeonUrl = 'postgresql://neondb_owner:npg_0zyigPE3WdbN@ep-restless-dust-b57sfv5z-pooler.c-7.us-east-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require';
$defaultNeonUnpooled = 'postgresql://neondb_owner:npg_0zyigPE3WdbN@ep-restless-dust-b57sfv5z.c-7.us-east-2.aws.neon.tech/neondb?sslmode=require';
$defaultNeonHost = 'ep-restless-dust-b57sfv5z.c-7.us-east-2.aws.neon.tech';

// Set environment variables penting untuk Vercel Serverless
$envVars = [
    'APP_NAME' => 'Learning Musashi',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
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
    'DATABASE_URL' => $getEnvVal('DATABASE_URL', $defaultNeonUrl),
    'POSTGRES_URL' => $getEnvVal('POSTGRES_URL', $defaultNeonUrl),
    'DATABASE_URL_UNPOOLED' => $getEnvVal('DATABASE_URL_UNPOOLED', $defaultNeonUnpooled),
    'POSTGRES_URL_NON_POOLING' => $getEnvVal('POSTGRES_URL_NON_POOLING', $defaultNeonUnpooled),
    'PGHOST' => $getEnvVal('PGHOST', $defaultNeonHost),
    'PGUSER' => $getEnvVal('PGUSER', 'neondb_owner'),
    'PGDATABASE' => $getEnvVal('PGDATABASE', 'neondb'),
    'PGPASSWORD' => $getEnvVal('PGPASSWORD', 'npg_0zyigPE3WdbN'),
    'DB_HOST' => $getEnvVal('DB_HOST', $defaultNeonHost),
    'DB_PORT' => '5432',
    'DB_DATABASE' => $getEnvVal('DB_DATABASE', 'neondb'),
    'DB_USERNAME' => $getEnvVal('DB_USERNAME', 'neondb_owner'),
    'DB_PASSWORD' => $getEnvVal('DB_PASSWORD', 'npg_0zyigPE3WdbN'),
    'DB_DATABASE_SQLITE_FALLBACK' => $tmpDb,
];

foreach ($envVars as $key => $val) {
    if ($val !== null && $val !== '') {
        putenv("{$key}={$val}");
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }
}

// Paksa request di Vercel selalu dideteksi sebagai HTTPS agar asset/css/gambar tidak terblokir Mixed Content
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['HTTP_X_FORWARDED_PORT'] = '443';
if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'learning-musashi.vercel.app';
}
if (empty($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
if (empty($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}
if (empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
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
