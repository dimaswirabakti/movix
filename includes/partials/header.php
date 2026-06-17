<?php
require_once __DIR__ . '/../init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = is_logged_in();
$username = $_SESSION['username'] ?? '';

// Sinkronkan avatar_url ke session jika belum ada (misal user yang sudah login sebelum fitur ini)
if ($loggedIn && !isset($_SESSION['avatar_url'])) {
    $stmtAvt = db()->prepare('SELECT avatar_url FROM users WHERE id = ?');
    $stmtAvt->execute([$_SESSION['user_id']]);
    $_SESSION['avatar_url'] = $stmtAvt->fetchColumn();
}

// Tandai menu aktif berdasarkan file yang sedang dibuka.
$current = basename($_SERVER['PHP_SELF'] ?? '');
function nav_active(string $file, string $current): string
{
    return $file === $current ? ' active' : '';
}
?>
<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Movix</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
  <link href="/assets/css/tokens.css" rel="stylesheet">
  <link href="/assets/css/components.css" rel="stylesheet">
  <link href="/assets/css/pages.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-movix sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/index.php"><i class="bi bi-film"></i> MOVIX</a>

    <div class="d-flex align-items-center gap-2 d-lg-none">
      <button class="btn-icon" type="button" data-bs-toggle="collapse" data-bs-target="#navSearchMobile" aria-controls="navSearchMobile" aria-expanded="false" aria-label="Cari">
        <i class="bi bi-search"></i>
      </button>
      <button class="btn-icon border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Menu">
        <i class="bi bi-list fs-4"></i>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav mx-lg-auto mb-2 mb-lg-0 gap-lg-3">
        <li class="nav-item"><a class="nav-link<?= nav_active('index.php', $current) ?>" href="/index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_active('movies.php', $current) ?>" href="/pages/movies.php">Film</a></li>
        <?php if ($loggedIn): ?>
          <li class="nav-item"><a class="nav-link<?= nav_active('watchlist.php', $current) ?>" href="/pages/watchlist.php">Watchlist</a></li>
          <li class="nav-item"><a class="nav-link<?= nav_active('saved.php', $current) ?>" href="/pages/saved.php">Tersimpan</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2">
        <button class="btn-icon d-none d-lg-inline-flex" type="button" id="searchToggle" aria-label="Cari">
          <i class="bi bi-search"></i>
        </button>
        <form class="navbar-search d-none" id="searchForm" role="search" action="/pages/movies.php" method="get">
          <input class="form-control search-input" type="search" id="searchInput" name="q" placeholder="Cari film..." aria-label="Cari film">
          <button class="btn-icon" type="button" id="searchClose" aria-label="Tutup pencarian">
            <i class="bi bi-x-lg"></i>
          </button>
        </form>

        <?php if ($loggedIn): ?>
          <hr class="d-lg-none border-secondary my-2">
          <a class="nav-link d-lg-none" href="/pages/profile.php">Profil · <?= e($username) ?></a>
          <a class="nav-link d-lg-none text-rust" href="/pages/logout.php">Keluar</a>
          <div class="dropdown d-none d-lg-block">
            <button class="btn p-0 border-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?= render_avatar($_SESSION['avatar_url'] ?? null, $username, 38) ?>
              <i class="bi bi-chevron-down small text-ash"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-movix">
              <li><a class="dropdown-item" href="/pages/profile.php"><i class="bi bi-person"></i> Profil</a></li>
              <li><a class="dropdown-item" href="/pages/watchlist.php"><i class="bi bi-list-ul"></i> Watchlist</a></li>
              <li><a class="dropdown-item" href="/pages/saved.php"><i class="bi bi-bookmark"></i> Tersimpan</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-rust" href="/pages/logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
            </ul>
          </div>
        <?php else: ?>
          <hr class="d-lg-none border-secondary my-2">
          <a class="btn btn-outline-cream d-lg-none" href="/pages/login.php">Masuk</a>
          <a class="nav-link d-none d-lg-inline px-2" href="/pages/login.php">Masuk</a>
          <a class="btn btn-brass px-3" href="/pages/register.php">Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="collapse d-lg-none" id="navSearchMobile">
    <div class="container pb-3">
      <form class="d-flex gap-2" role="search" action="/pages/movies.php" method="get">
        <input class="form-control search-input" type="search" name="q" placeholder="Cari judul film..." aria-label="Cari film">
        <button class="btn btn-brass" type="submit"><i class="bi bi-search"></i></button>
      </form>
    </div>
  </div>
</nav>

<!-- Konten tiap halaman dimulai dari sini -->
<main class="flex-grow-1">
