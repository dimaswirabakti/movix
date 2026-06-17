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