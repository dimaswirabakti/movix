<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = is_logged_in();
$username = $_SESSION['username'] ?? '';
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Movix — Your Gateway to the World of Movies</title>
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">🎬 Movix</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"
            aria-controls="navMain" aria-expanded="false" aria-label="Buka menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/movies.php">Film</a></li>
        <?php if ($loggedIn): ?>
          <li class="nav-item"><a class="nav-link" href="/pages/watchlist.php">Watchlist</a></li>
          <li class="nav-item"><a class="nav-link" href="/pages/saved.php">Tersimpan</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav align-items-lg-center">
        <?php if ($loggedIn): ?>
          <li class="nav-item"><span class="navbar-text me-lg-3">Halo, <?= e($username) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/pages/logout.php">Keluar</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/pages/login.php">Masuk</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm" href="/pages/register.php">Daftar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Konten tiap halaman dimulai dari sini -->
<main class="container py-4 flex-grow-1">