<?php
declare(strict_types=1);

// Escape output, mencegah XSS
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Cek apakah ada user yang sedang login
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

// Format durasi menit menjadi "2j 55m"
function format_duration(?int $min): string
{
    if (!$min) return '';
    $h = intdiv($min, 60);
    $m = $min % 60;
    $out = [];
    if ($h) $out[] = $h . 'j';
    if ($m) $out[] = $m . 'm';
    return implode(' ', $out);
}