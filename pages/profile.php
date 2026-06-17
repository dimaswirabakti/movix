<?php
require_once __DIR__ . '/../includes/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!is_logged_in()) {
    header('Location: /pages/login.php?next=' . rawurlencode('/pages/profile.php'));
    exit;
}
$uid = (int) $_SESSION['user_id'];

// Data user
$uStmt = db()->prepare('SELECT username, email, avatar_url, created_at FROM users WHERE id = ? LIMIT 1');
$uStmt->execute([$uid]);
$user = $uStmt->fetch();
if (!$user) {
    header('Location: /pages/logout.php');
    exit;
}

// Statistik dari view table database
$sStmt = db()->prepare('SELECT total_reviews, total_watchlist, total_saved, avg_rating_given FROM view_user_activity WHERE id = ?');
$sStmt->execute([$uid]);
$stats = $sStmt->fetch() ?: ['total_reviews' => 0, 'total_watchlist' => 0, 'total_saved' => 0, 'avg_rating_given' => null];

// Tab Ulasan Saya
$rStmt = db()->prepare(
    'SELECT r.rating, m.id AS movie_id, m.title, m.poster_url
     FROM reviews r JOIN movies m ON m.id = r.movie_id
     WHERE r.user_id = ? ORDER BY r.created_at DESC, r.id DESC'
);
$rStmt->execute([$uid]);
$myReviews = $rStmt->fetchAll();

// Tab Watchlist
$wStmt = db()->prepare(
    'SELECT w.status, m.id AS movie_id, m.title, m.poster_url
     FROM watchlist w JOIN movies m ON m.id = w.movie_id
     WHERE w.user_id = ? ORDER BY w.added_at DESC, w.movie_id DESC'
);
$wStmt->execute([$uid]);
$myWatchlist = $wStmt->fetchAll();

// Tab Tersimpan
$svStmt = db()->prepare(
    'SELECT m.id AS movie_id, m.title, m.poster_url, m.release_year, m.avg_rating
     FROM saved_movies s JOIN movies m ON m.id = s.movie_id
     WHERE s.user_id = ? ORDER BY s.saved_at DESC, s.movie_id DESC'
);
$svStmt->execute([$uid]);
$mySaved = $svStmt->fetchAll();

$statusLabels = [
    'plan_to_watch' => 'Mau Ditonton',
    'watching' => 'Sedang Ditonton',
    'watched' => 'Sudah Ditonton',
];

require_once __DIR__ . '/../includes/partials/header.php';
?>

<section class="container py-4 py-lg-5">

  <!-- Header profil -->
  <div class="profile-head">
    <?= render_avatar($user['avatar_url'], $user['username'], 88) ?>
    <div>
      <h1 class="profile-name"><?= e($user['username']) ?></h1>
      <p class="profile-since mb-0">Bergabung sejak <?= e(date('Y-m-d', strtotime((string) $user['created_at']))) ?></p>
    </div>
    <a href="/pages/profile_edit.php" class="btn btn-outline-cream ms-lg-auto"><i class="bi bi-pencil me-1"></i> Edit Profil</a>
  </div>

  <hr class="profile-rule">

  <!-- Statistik -->
  <div class="profile-stats">
    <div class="stat">
      <div class="stat-num"><?= (int) $stats['total_reviews'] ?></div>
      <div class="stat-label">TOTAL ULASAN</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= (int) $stats['total_watchlist'] ?></div>
      <div class="stat-label">WATCHLIST</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= (int) $stats['total_saved'] ?></div>
      <div class="stat-label">TERSIMPAN</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= $stats['avg_rating_given'] !== null ? e(number_format((float) $stats['avg_rating_given'], 1)) : '–' ?></div>
      <div class="stat-label">RATA-RATA RATING</div>
    </div>
  </div>

  <hr class="profile-rule">

  <!-- Tab -->
  <ul class="profile-tabs" role="tablist">
    <li><button class="profile-tab is-active" data-tab="reviews" type="button">Ulasan Saya</button></li>
    <li><button class="profile-tab" data-tab="watchlist" type="button">Watchlist</button></li>
    <li><button class="profile-tab" data-tab="saved" type="button">Tersimpan</button></li>
  </ul>

  <!-- Panel Ulasan Saya -->
  <div class="profile-panel" id="panel-reviews">
    <?php if (!$myReviews): ?>
      <div class="profile-empty">
        <h2 class="mb-2">Belum ada ulasan.</h2>
        <a href="/pages/movies.php" class="text-brass">Mulai menonton →</a>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($myReviews as $rv): ?>
          <a class="profile-row" href="/pages/movie.php?id=<?= (int) $rv['movie_id'] ?>">
            <div class="profile-row__poster">
              <?php if ($rv['poster_url']): ?><img src="<?= e($rv['poster_url']) ?>" alt="Poster <?= e($rv['title']) ?>" loading="lazy"><?php endif; ?>
            </div>
            <div>
              <h3 class="profile-row__title"><?= e($rv['title']) ?></h3>
              <p class="profile-row__meta mb-0"><span class="star">★</span> <?= (int) $rv['rating'] ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Panel Watchlist -->
  <div class="profile-panel d-none" id="panel-watchlist">
    <?php if (!$myWatchlist): ?>
      <div class="profile-empty">
        <h2 class="mb-2">Watchlist masih kosong.</h2>
        <a href="/pages/movies.php" class="text-brass">Jelajahi film →</a>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($myWatchlist as $w): ?>
          <a class="profile-row" href="/pages/movie.php?id=<?= (int) $w['movie_id'] ?>">
            <div class="profile-row__poster">
              <?php if ($w['poster_url']): ?><img src="<?= e($w['poster_url']) ?>" alt="Poster <?= e($w['title']) ?>" loading="lazy"><?php endif; ?>
            </div>
            <div>
              <h3 class="profile-row__title"><?= e($w['title']) ?></h3>
              <p class="profile-row__meta mb-0"><?= e($statusLabels[$w['status']] ?? $w['status']) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Panel Tersimpan -->
  <div class="profile-panel d-none" id="panel-saved">
    <?php if (!$mySaved): ?>
      <div class="profile-empty">
        <h2 class="mb-2">Belum ada film tersimpan.</h2>
        <a href="/pages/movies.php" class="text-brass">Jelajahi film →</a>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($mySaved as $sv): ?>
          <a class="profile-row" href="/pages/movie.php?id=<?= (int) $sv['movie_id'] ?>">
            <div class="profile-row__poster">
              <?php if ($sv['poster_url']): ?><img src="<?= e($sv['poster_url']) ?>" alt="Poster <?= e($sv['title']) ?>" loading="lazy"><?php endif; ?>
            </div>
            <div>
              <h3 class="profile-row__title"><?= e($sv['title']) ?></h3>
              <p class="profile-row__meta mb-0"><?= e((string) $sv['release_year']) ?> · <span class="star">★</span> <?= e(number_format((float) $sv['avg_rating'], 1)) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</section>

<script>
// Pindah tab tanpa reload
document.querySelectorAll('.profile-tab').forEach(function (tab) {
  tab.addEventListener('click', function () {
    document.querySelectorAll('.profile-tab').forEach(function (t) { t.classList.remove('is-active'); });
    tab.classList.add('is-active');
    document.querySelectorAll('.profile-panel').forEach(function (p) { p.classList.add('d-none'); });
    document.getElementById('panel-' + tab.dataset.tab).classList.remove('d-none');
  });
});
</script>

<?php require_once __DIR__ . '/../includes/partials/footer.php'; ?>