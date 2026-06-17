<?php
require_once __DIR__ . '/../includes/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = (isset($_GET['id']) && ctype_digit((string) $_GET['id'])) ? (int) $_GET['id'] : 0;

// Menangani POST (simpan / hapus ulasan), wajib login.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        header('Location: /pages/login.php?next=' . rawurlencode('/pages/movie.php?id=' . $id));
        exit;
    }
    $movieId = (isset($_POST['movie_id']) && ctype_digit((string) $_POST['movie_id'])) ? (int) $_POST['movie_id'] : 0;
    $action = $_POST['action'] ?? '';
    $uid = (int) $_SESSION['user_id'];

    if ($movieId > 0 && $action === 'review_save') {
        $rating = (isset($_POST['rating']) && ctype_digit((string) $_POST['rating'])) ? (int) $_POST['rating'] : 0;
        $text = trim($_POST['review_text'] ?? '');
        if ($rating >= 1 && $rating <= 10 && $text !== '') {
            $stmt = db()->prepare(
                'INSERT INTO reviews (user_id, movie_id, rating, review_text)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text)'
            );
            $stmt->execute([$uid, $movieId, $rating, $text]);
        }
    } elseif ($movieId > 0 && $action === 'review_delete') {
        $stmt = db()->prepare('DELETE FROM reviews WHERE user_id = ? AND movie_id = ?');
        $stmt->execute([$uid, $movieId]);
    } elseif ($movieId > 0 && $action === 'watchlist_toggle') {
        $chk = db()->prepare('SELECT 1 FROM watchlist WHERE user_id = ? AND movie_id = ?');
        $chk->execute([$uid, $movieId]);
        if ($chk->fetchColumn()) {
            db()->prepare('DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?')->execute([$uid, $movieId]);
        } else {
            db()->prepare('INSERT INTO watchlist (user_id, movie_id, status) VALUES (?, ?, ?)')->execute([$uid, $movieId, 'plan_to_watch']);
        }
    } elseif ($movieId > 0 && $action === 'saved_toggle') {
        $chk = db()->prepare('SELECT 1 FROM saved_movies WHERE user_id = ? AND movie_id = ?');
        $chk->execute([$uid, $movieId]);
        if ($chk->fetchColumn()) {
            db()->prepare('DELETE FROM saved_movies WHERE user_id = ? AND movie_id = ?')->execute([$uid, $movieId]);
        } else {
            db()->prepare('INSERT INTO saved_movies (user_id, movie_id) VALUES (?, ?)')->execute([$uid, $movieId]);
        }
    }

    header('Location: /pages/movie.php?id=' . $movieId);
    exit;
}

// Ambil data film
$stmt = db()->prepare(
    'SELECT id, title, release_year, duration_min, director, synopsis,
            poster_url, backdrop_url, avg_rating, review_count
     FROM movies WHERE id = ? LIMIT 1'
);
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    http_response_code(404);
    require_once __DIR__ . '/../includes/partials/header.php';
    echo '<section class="container py-5 text-center"><h1 class="section-title">Film tidak ditemukan</h1>'
       . '<p class="text-ash">Film yang Anda cari tidak ada. <a href="/pages/movies.php" class="text-brass">Kembali ke daftar film</a>.</p></section>';
    require_once __DIR__ . '/../includes/partials/footer.php';
    exit;
}

// Genre, pemeran, ulasan
$gStmt = db()->prepare('SELECT g.name FROM genres g JOIN movie_genres mg ON mg.genre_id = g.id WHERE mg.movie_id = ? ORDER BY g.name');
$gStmt->execute([$id]);
$genres = $gStmt->fetchAll(PDO::FETCH_COLUMN);

$cStmt = db()->prepare('SELECT a.name, ma.role_name FROM movie_actors ma JOIN actors a ON a.id = ma.actor_id WHERE ma.movie_id = ? ORDER BY a.id');
$cStmt->execute([$id]);
$cast = $cStmt->fetchAll();

$loggedIn = is_logged_in();
$uid = $loggedIn ? (int) $_SESSION['user_id'] : 0;

// Ulasan milik user lain
$rStmt = db()->prepare(
    'SELECT r.id, r.rating, r.review_text, r.created_at, u.username, u.avatar_url
     FROM reviews r JOIN users u ON u.id = r.user_id
     WHERE r.movie_id = ? AND r.user_id <> ?
     ORDER BY r.created_at DESC, r.id DESC'
);
$rStmt->execute([$id, $uid]);
$reviews = $rStmt->fetchAll();

// Ulasan milik user yang sedang login (untuk mode edit)
$ownReview = null;
if ($loggedIn) {
    $oStmt = db()->prepare('SELECT rating, review_text FROM reviews WHERE user_id = ? AND movie_id = ? LIMIT 1');
    $oStmt->execute([$uid, $id]);
    $ownReview = $oStmt->fetch() ?: null;
}

$inWatchlist = false;
$isSaved = false;
if ($loggedIn) {
    $w = db()->prepare('SELECT 1 FROM watchlist WHERE user_id = ? AND movie_id = ?');
    $w->execute([$uid, $id]);
    $inWatchlist = (bool) $w->fetchColumn();

    $s = db()->prepare('SELECT 1 FROM saved_movies WHERE user_id = ? AND movie_id = ?');
    $s->execute([$uid, $id]);
    $isSaved = (bool) $s->fetchColumn();
}

require_once __DIR__ . '/../includes/partials/header.php';
?>

<!-- HERO -->
<section class="detail-hero">
  <div class="detail-hero__bg"<?= $movie['backdrop_url'] ? ' style="background-image:url(\'' . e($movie['backdrop_url']) . '\')"' : '' ?>></div>
  <div class="detail-hero__overlay"></div>
  <div class="container">
    <div class="detail-grid">
      <div class="detail-poster">
        <?php if ($movie['poster_url']): ?>
          <img src="<?= e($movie['poster_url']) ?>" alt="Poster <?= e($movie['title']) ?>">
        <?php endif; ?>
      </div>
      <div>
        <h1 class="detail-title"><?= e($movie['title']) ?></h1>
        <p class="detail-year"><?= e((string) $movie['release_year']) ?></p>
        <p class="detail-meta">
          <?php
            $bits = [];
            if ($movie['duration_min']) $bits[] = format_duration((int) $movie['duration_min']);
            if ($movie['director']) $bits[] = $movie['director'];
            echo e(implode(' · ', $bits));
          ?>
        </p>
        <?php if ($genres): ?>
          <div class="detail-genres">
            <?php foreach ($genres as $gn): ?>
              <span class="detail-chip"><?= e($gn) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="detail-rating">
          <span class="star">★</span>
          <span class="num"><?= e(number_format((float) $movie['avg_rating'], 1)) ?></span>
          <span class="count">(<?= (int) $movie['review_count'] ?> ulasan)</span>
        </div>
        <?php if ($loggedIn): ?>
          <div class="detail-actions">
            <form method="POST" action="/pages/movie.php?id=<?= $id ?>" class="d-inline">
              <input type="hidden" name="action" value="watchlist_toggle">
              <input type="hidden" name="movie_id" value="<?= $id ?>">
              <button type="submit" class="btn <?= $inWatchlist ? 'btn-brass' : 'btn-outline-cream' ?> px-4">
                <i class="bi <?= $inWatchlist ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i>
                <?= $inWatchlist ? 'Di Watchlist' : 'Watchlist' ?>
              </button>
            </form>
            <form method="POST" action="/pages/movie.php?id=<?= $id ?>" class="d-inline">
              <input type="hidden" name="action" value="saved_toggle">
              <input type="hidden" name="movie_id" value="<?= $id ?>">
              <button type="submit" class="btn btn-outline-cream px-4">
                <i class="bi <?= $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' ?> me-1"></i>
                <?= $isSaved ? 'Tersimpan' : 'Simpan' ?>
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- SINOPSIS -->
<section class="detail-section">
  <div class="container">
    <h2 class="section-title mb-3">Sinopsis</h2>
    <p class="lead-text mb-0"><?= e($movie['synopsis'] ?: 'Belum ada sinopsis untuk film ini.') ?></p>
  </div>
</section>

<div class="container"><div class="sprocket-rail"></div></div>

<!-- PEMERAN -->
<?php if ($cast): ?>
<section class="detail-section">
  <div class="container">
    <h2 class="section-title mb-3">Pemeran</h2>
    <div class="cast-grid">
      <?php foreach ($cast as $c): ?>
        <div class="cast-card">
          <div class="name"><?= e($c['name']) ?></div>
          <div class="role"><?= e($c['role_name'] ?? '') ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<div class="container"><div class="sprocket-rail"></div></div>
<?php endif; ?>

<!-- ULASAN -->
<section class="detail-section">
  <div class="container">
    <h2 class="section-title mb-4">Ulasan</h2>

    <?php if (!$loggedIn): ?>
      <div class="review-prompt mb-4">
        <a href="/pages/login.php?next=<?= e(rawurlencode('/pages/movie.php?id=' . $id)) ?>">Masuk</a> untuk menulis ulasan.
      </div>
    <?php else: ?>
      <div class="review-form-card mb-4">
        <h3><?= $ownReview ? 'Edit ulasan Anda' : 'Tulis ulasan Anda' ?></h3>
        <form id="reviewForm" method="POST" action="/pages/movie.php?id=<?= $id ?>" novalidate>
          <input type="hidden" name="action" value="review_save">
          <input type="hidden" name="movie_id" value="<?= $id ?>">
          <input type="hidden" name="rating" id="ratingValue" value="<?= $ownReview ? (int) $ownReview['rating'] : '' ?>">

          <label class="form-label text-ash mb-2">Rating (1-10)</label>
          <div class="rating-picker mb-1" id="ratingPicker">
            <?php for ($n = 1; $n <= 10; $n++): ?>
              <button type="button" class="rating-btn <?= ($ownReview && (int) $ownReview['rating'] === $n) ? 'is-selected' : '' ?>" data-value="<?= $n ?>"><?= $n ?></button>
            <?php endfor; ?>
          </div>
          <div class="review-err mb-2" id="errRating"></div>

          <label for="reviewText" class="form-label text-ash mb-2">Ulasan</label>
          <textarea class="form-control" id="reviewText" name="review_text" rows="4"
                    placeholder="Bagikan pendapat Anda tentang film ini..."><?= $ownReview ? e($ownReview['review_text']) : '' ?></textarea>
          <div class="review-err mb-3" id="errText"></div>

          <div class="d-flex gap-2 align-items-center">
            <button type="submit" class="btn btn-brass px-4"><?= $ownReview ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?></button>
          </div>
        </form>

        <?php if ($ownReview): ?>
          <form id="deleteReviewForm" method="POST" action="/pages/movie.php?id=<?= $id ?>" class="mt-3">
            <input type="hidden" name="action" value="review_delete">
            <input type="hidden" name="movie_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-link p-0 text-rust" style="text-decoration:none;font-size:.85rem">Hapus ulasan saya</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Daftar ulasan -->
    <?php if (!$reviews && !$ownReview): ?>
      <p class="text-ash">Belum ada ulasan. Jadilah yang pertama!</p>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($reviews as $r): ?>
          <div class="review-item">
            <div class="review-head">
              <?= render_avatar($r['avatar_url'], $r['username'], 40) ?>
              <div>
                <div class="username"><?= e($r['username']) ?></div>
                <div class="review-sub"><span class="star">★</span> <?= (int) $r['rating'] ?> · <?= e(date('Y-m-d', strtotime((string) $r['created_at']))) ?></div>
              </div>
            </div>
            <p class="review-body"><?= e($r['review_text']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  var picker = document.getElementById('ratingPicker');
  if (!picker) return; // hanya ada saat login

  var hidden = document.getElementById('ratingValue');
  var form = document.getElementById('reviewForm');
  var textarea = document.getElementById('reviewText');
  var errRating = document.getElementById('errRating');
  var errText = document.getElementById('errText');

  // Pilih rating
  picker.addEventListener('click', function (e) {
    var btn = e.target.closest('.rating-btn');
    if (!btn) return;
    hidden.value = btn.dataset.value;
    picker.querySelectorAll('.rating-btn').forEach(function (b) { b.classList.remove('is-selected'); });
    btn.classList.add('is-selected');
    errRating.textContent = '';
  });

  // Validasi 2 field sebelum submit
  form.addEventListener('submit', function (e) {
    var ok = true;
    if (!hidden.value) { errRating.textContent = 'Pilih rating dulu (1-10).'; ok = false; }
    if (textarea.value.trim() === '') { errText.textContent = 'Ulasan tidak boleh kosong.'; ok = false; }
    if (!ok) e.preventDefault();
  });
  textarea.addEventListener('input', function () {
    if (textarea.value.trim() !== '') errText.textContent = '';
  });

  // Konfirmasi hapus
  var delForm = document.getElementById('deleteReviewForm');
  if (delForm) {
    delForm.addEventListener('submit', function (e) {
      if (!confirm('Hapus ulasan Anda? Tindakan ini tidak bisa dibatalkan.')) e.preventDefault();
    });
  }
})();
</script>

<?php require_once __DIR__ . '/../includes/partials/footer.php'; ?>