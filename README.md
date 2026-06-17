# Movix: Your Gateway to the World of Movies

Platform katalog film sederhana. Proyek gabungan Praktikum Basis Data & Pemrograman Web 1.

> Dokumentasi lengkap menyusul.

## Struktur

assets/css/ (tokens, components, pages, auth + bootstrap lokal)
includes/config.php (db, env) | includes/init.php (pintu masuk)
includes/helpers/ (functions: e, is_logged_in, format_duration; avatar: render_avatar)
includes/partials/ (header, footer, auth_header, auth_footer)
pages/ | database/ | index.php | router.php

Setiap halaman cukup: require_once includes/init.php (untuk halaman mandiri)
atau require includes/partials/header.php (yang sudah memuat init).
