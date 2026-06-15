<?php
declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_readable($path)) {
        die('File .env tidak ditemukan. Pastikan ada di root project.');
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key   = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

load_env(__DIR__ . '/../.env');

// Function buat ambil nilai env dengan nilai default yang aman.
function env(string $key, ?string $default = null): ?string
{
    $val = $_ENV[$key] ?? getenv($key);
    return ($val === false || $val === null) ? $default : $val;
}

// Koneksi PDO
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'movix');
    $user = env('DB_USER', 'movix_user');
    $pass = env('DB_PASS', '');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Menggunakan prepared statement asli dari MySQL
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Untuk pengembangan lokal: tampilkan pesan agar mudah diperbaiki.
        die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
    }

    return $pdo;
}

// Beberapa helper function umum

// Escape teks sebelum ditampilkan, mencegah XSS (htmlspecialchars)
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Untuk cek apakah ada user yang sedang login. dipakai di navbar n proteksi halaman
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}