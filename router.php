<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = basename($path);

// Blokir file sensitif
if (
    str_ends_with($path, '.env') ||
    str_contains($path, '/.git') ||
    in_array($base, ['compose.yml', '.gitignore', '.env.example'], true)
) {
    http_response_code(403);
    exit('403 Forbidden');
}

return false;