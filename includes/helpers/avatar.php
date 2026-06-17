<?php
// Jika punya foto, tampilkan <img>
// Jika belum, gunakan inisial dengan background brass.

function render_avatar(?string $avatarUrl, string $username, int $size = 40, string $classes = ''): string
{
    $initial = strtoupper(mb_substr(trim($username), 0, 1));
    $fontSize = (int) ($size * 0.4);
    $baseClass = 'mvx-avatar ' . $classes;

    if ($avatarUrl && file_exists(__DIR__ . '/../../' . $avatarUrl)) {
        return sprintf(
            '<img src="%s" alt="Avatar %s" width="%d" height="%d" class="%s" style="object-fit:cover;border-radius:50%%;">',
            e($avatarUrl), e($username), $size, $size, e($baseClass)
        );
    }

    return sprintf(
        '<div class="%s" aria-label="Avatar %s" style="width:%dpx;height:%dpx;border-radius:50%%;background:var(--brass);color:var(--ink);display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-weight:700;font-size:%dpx;flex-shrink:0;">%s</div>',
        e($baseClass), e($username), $size, $size, $fontSize, e($initial)
    );
}