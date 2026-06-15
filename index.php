<?php
require_once __DIR__ . '/includes/header.php';

$movies = db()
    ->query("SELECT title, release_year, avg_rating, genres
             FROM view_movie_ratings
             ORDER BY avg_rating DESC, review_count DESC
             LIMIT 6")
    ->fetchAll();
?>

<div class="p-5 mb-4 bg-body-tertiary rounded-3 text-center">
  <h1 class="display-5 fw-bold">Movix</h1>
  <p class="lead mb-0">Your Gateway to the World of Movies</p>
</div>

<h2 class="h4 mb-3">Film Rating Tertinggi</h2>
<div class="row g-3">
  <?php foreach ($movies as $m): ?>
    <div class="col-12 col-sm-6 col-md-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h3 class="card-title h6 mb-1">
            <?= e($m['title']) ?>
            <span class="text-muted fw-normal">(<?= e((string) $m['release_year']) ?>)</span>
          </h3>
          <p class="card-text small text-muted mb-2"><?= e($m['genres'] ?? '-') ?></p>
          <span class="badge text-bg-warning">&#9733; <?= e((string) $m['avg_rating']) ?></span>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="alert alert-success mt-4" role="alert">
  &#10003; Koneksi database berhasil — <?= count($movies) ?> film di atas diambil langsung
  dari MySQL melalui PDO.
</div>

<?php
require_once __DIR__ . '/includes/footer.php';