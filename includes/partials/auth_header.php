<?php
require_once __DIR__ . '/../init.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($authTitle ?? 'Movix') ?> · Movix</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
  <link href="/assets/css/tokens.css" rel="stylesheet">
  <link href="/assets/css/components.css" rel="stylesheet">
  <link href="/assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="auth-split">

  <aside class="auth-aside <?= e($asideClass ?? '') ?>">
    <a class="auth-logo" href="/index.php">
      <i class="bi bi-film"></i> MOVIX
    </a>
    <div class="auth-aside__caption">
      <p class="eyebrow mb-2"><?= e($asideEyebrow ?? '') ?></p>
      <h2 class="auth-aside__title"><?= e($asideTitle ?? '') ?></h2>
      <p class="auth-aside__sub"><?= e($asideSubtitle ?? '') ?></p>
    </div>
  </aside>

  <main class="auth-main">
    <div class="auth-card">
      <a class="auth-logo auth-logo--mobile" href="/index.php">
        <i class="bi bi-film"></i> MOVIX
      </a>
      <p class="eyebrow mb-2"><?= e($authEyebrow ?? '') ?></p>
      <h1 class="auth-heading"><?= e($authHeading ?? '') ?></h1>