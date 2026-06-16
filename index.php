<?php
require_once __DIR__ . '/includes/header.php';

if (!function_exists('format_duration')) {
    function format_duration(?int $min): string {
        if (!$min) return '';
        $h = intdiv($min, 60); $m = $min % 60;
        $out = [];
        if ($h) $out[] = $h . 'j';
        if ($m) $out[] = $m . 'm';
        return implode(' ', $out);
    }
}

/* ---------- Data Beranda ---------- */

// 1) Hero: film rating tertinggi + backdrop + genre
$featured = db()->query(
    "SELECT m.id, m.title, m.release_year, m.duration_min, m.synopsis,
            m.backdrop_url, m.avg_rating,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ' · ') AS genres
     FROM movies m
     LEFT JOIN movie_genres mg ON mg.movie_id = m.id
     LEFT JOIN genres g ON g.id = mg.genre_id
     GROUP BY m.id, m.title, m.release_year, m.duration_min, m.synopsis,
              m.backdrop_url, m.avg_rating
     ORDER BY m.avg_rating DESC, m.review_count DESC, m.id ASC
     LIMIT 1"
)->fetch();

// 2) Rating Tertinggi (7 teratas yang sudah punya ulasan)
$topRated = db()->query(
    "SELECT id, title, release_year, avg_rating, poster_url
     FROM movies WHERE review_count > 0
     ORDER BY avg_rating DESC, review_count DESC, id ASC
     LIMIT 7"
)->fetchAll();

// 3) Genre
$genres = db()->query("SELECT id, name FROM genres ORDER BY id")->fetchAll();

// 4) Baru Diulas (3 ulasan terbaru)
$recentReviews = db()->query(
    "SELECT r.rating, r.review_text, r.created_at,
            u.username, m.id AS movie_id, m.title, m.poster_url
     FROM reviews r
     JOIN users u ON u.id = r.user_id
     JOIN movies m ON m.id = r.movie_id
     ORDER BY r.created_at DESC, r.id DESC
     LIMIT 3"
)->fetchAll();
?>

<!-- ============== HERO ============== -->
<?php if ($featured): ?>
<section class="hero">
  <div class="hero__bg"<?= $featured['backdrop_url'] ? ' style="background-image:url(\'' . e($featured['backdrop_url']) . '\')"' : '' ?>></div>
  <div class="hero__overlay"></div>

  <div class="container">
    <div class="hero__content mx-lg-auto">
      <p class="eyebrow hero__eyebrow mb-2"><i class="bi bi-play-fill"></i> FILM PILIHAN</p>
      <h1 class="hero__title"><?= e($featured['title']) ?></h1>
      <p class="hero__meta mb-3">
        <?= e((string) $featured['release_year']) ?><?php
          if ($featured['duration_min']) echo ' · ' . e(format_duration((int) $featured['duration_min']));
          if ($featured['genres'])       echo ' · ' . e($featured['genres']);
        ?> · <span class="star">★</span> <?= e(number_format((float) $featured['avg_rating'], 1)) ?>
      </p>
      <p class="hero__synopsis mb-4"><?= e($featured['synopsis']) ?></p>
      <div class="d-flex flex-wrap gap-2">
        <a href="/pages/movie.php?id=<?= (int) $featured['id'] ?>" class="btn btn-brass px-4 py-2">Lihat Detail</a>
        <?php if ($loggedIn): ?>
          <a href="/pages/movie.php?id=<?= (int) $featured['id'] ?>" class="btn btn-outline-cream px-4 py-2">
            <i class="bi bi-plus-lg me-1"></i> Watchlist
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="hero__rail"><div class="sprocket-rail"></div></div>
</section>
<?php endif; ?>

<!-- ============== RATING TERTINGGI ============== -->
<section class="section container">
  <div class="section-head">
    <div>
      <p class="eyebrow mb-1">PERINGKAT TERATAS</p>
      <h2 class="section-title">Rating Tertinggi</h2>
    </div>
    <a class="see-all" href="/pages/movies.php?sort=rating">Lihat semua <i class="bi bi-chevron-right small"></i></a>
  </div>

  <div class="top-rated-rail">
    <?php foreach ($topRated as $m): ?>
      <a class="movie-card" href="/pages/movie.php?id=<?= (int) $m['id'] ?>">
        <div class="poster">
          <?php if ($m['poster_url']): ?>
            <img src="<?= e($m['poster_url']) ?>" alt="Poster <?= e($m['title']) ?>" loading="lazy">
          <?php else: ?>
            <div class="poster--ph"><span class="poster__initial"><?= e(mb_substr($m['title'], 0, 1)) ?></span></div>
          <?php endif; ?>
        </div>
        <h3 class="movie-title"><?= e($m['title']) ?></h3>
        <p class="movie-meta mb-0">
          <?= e((string) $m['release_year']) ?> · <span class="star">★</span> <?= e(number_format((float) $m['avg_rating'], 1)) ?>
        </p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============== JELAJAHI GENRE ============== -->
<section class="section--tight container">
  <p class="eyebrow mb-1">KATEGORI</p>
  <h2 class="section-title mb-4">Jelajahi Genre</h2>
  <div class="d-flex flex-wrap gap-2 gap-md-3">
    <?php foreach ($genres as $g): ?>
      <a class="genre-pill" href="/pages/movies.php?genre=<?= (int) $g['id'] ?>"><?= e($g['name']) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============== BARU DIULAS ============== -->
<section class="section container">
  <p class="eyebrow mb-1">ULASAN TERBARU</p>
  <h2 class="section-title mb-4">Baru Diulas</h2>

  <?php if ($recentReviews): ?>
    <div class="row g-3">
      <?php foreach ($recentReviews as $rv): ?>
        <div class="col-12 col-lg-4">
          <a class="review-card" href="/pages/movie.php?id=<?= (int) $rv['movie_id'] ?>">
            <div class="poster-thumb">
              <?php if ($rv['poster_url']): ?>
                <img src="<?= e($rv['poster_url']) ?>" alt="Poster <?= e($rv['title']) ?>" loading="lazy">
              <?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <h3 class="review-title"><?= e($rv['title']) ?></h3>
              <p class="review-by"><span class="star">★</span> <?= (int) $rv['rating'] ?> · <?= e($rv['username']) ?></p>
              <p class="review-text"><?= e($rv['review_text']) ?></p>
              <p class="review-date mb-0"><?= e(date('d M Y', strtotime((string) $rv['created_at']))) ?></p>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-ash">Belum ada ulasan. Jadilah yang pertama memberi ulasan!</p>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>