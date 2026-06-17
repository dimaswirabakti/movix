<?php
require_once __DIR__ . '/../includes/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!is_logged_in()) {
    header('Location: /pages/login.php?next=' . rawurlencode('/pages/saved.php'));
    exit;
}
$uid = (int) $_SESSION['user_id'];

// Handle POST (hapus dari film yang disimpan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieId = (isset($_POST['movie_id']) && ctype_digit((string) $_POST['movie_id'])) ? (int) $_POST['movie_id'] : 0;
    if ($movieId > 0 && ($_POST['action'] ?? '') === 'remove') {
        $stmt = db()->prepare('DELETE FROM saved_movies WHERE user_id = ? AND movie_id = ?');
        $stmt->execute([$uid, $movieId]);
    }
    header('Location: /pages/saved.php');
    exit;
}

// Ambil data film yang tersimpan
$stmt = db()->prepare(
    'SELECT s.movie_id, m.title, m.release_year, m.avg_rating, m.poster_url
     FROM saved_movies s
     JOIN movies m ON m.id = s.movie_id
     WHERE s.user_id = ?
     ORDER BY s.saved_at DESC, s.movie_id DESC'
);
$stmt->execute([$uid]);
$items = $stmt->fetchAll();
$total = count($items);

require_once __DIR__ . '/../includes/partials/header.php';
?>

<section class="container py-4 py-lg-5">

  <div class="catalog-head mb-4">
    <div>
      <p class="eyebrow mb-1">KOLEKSI SAYA</p>
      <h1 class="section-title mb-0">Film Tersimpan</h1>
    </div>
    <p class="catalog-count mb-0"><?= $total ?> film</p>
  </div>

  <?php if (!$items): ?>
    <div class="catalog-empty">
      <h2 class="mb-2">Belum ada film tersimpan.</h2>
      <p class="mb-4">Simpan film favorit Anda untuk ditonton nanti.</p>
      <a href="/pages/movies.php" class="btn btn-brass px-4">Jelajahi Film</a>
    </div>
  <?php else: ?>
    <div class="row g-3 g-md-4">
      <?php foreach ($items as $it): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="sv-card">
            <div class="sv-card__media">
              <?php if ($it['poster_url']): ?>
                <img src="<?= e($it['poster_url']) ?>" alt="Poster <?= e($it['title']) ?>" loading="lazy">
              <?php else: ?>
                <div class="poster--ph"><span class="poster__initial"><?= e(mb_substr($it['title'], 0, 1)) ?></span></div>
              <?php endif; ?>

              <!-- Overlay aksi (hover atau klik film card) -->
              <div class="sv-card__actions">
                <form method="POST" action="/pages/saved.php" class="sv-remove-form">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="movie_id" value="<?= (int) $it['movie_id'] ?>">
                  <button type="submit" class="wl-remove-btn"><i class="bi bi-trash"></i> Hapus dari Tersimpan</button>
                </form>
              </div>
            </div>

            <div class="sv-card__foot">
              <h3 class="sv-title"><?= e($it['title']) ?></h3>
              <p class="sv-meta mb-0">
                <?= e((string) $it['release_year']) ?> · <span class="star">★</span> <?= e(number_format((float) $it['avg_rating'], 1)) ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<script>
// Klik card film untuk membuka/menutup overlay aksi
document.querySelectorAll('.sv-card').forEach(function (card) {
  card.addEventListener('click', function (e) {
    if (e.target.closest('button') || e.target.closest('form')) return;
    var wasOpen = card.classList.contains('is-open');
    document.querySelectorAll('.sv-card.is-open').forEach(function (c) { c.classList.remove('is-open'); });
    if (!wasOpen) card.classList.add('is-open');
  });
});

// Klik di luar card menutup semua overlay
document.addEventListener('click', function (e) {
  if (!e.target.closest('.sv-card')) {
    document.querySelectorAll('.sv-card.is-open').forEach(function (c) { c.classList.remove('is-open'); });
  }
});

// Konfirmasi sebelum hapus
document.querySelectorAll('.sv-remove-form').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!confirm('Hapus film ini dari tersimpan?')) e.preventDefault();
  });
});
</script>

<?php require_once __DIR__ . '/../includes/partials/footer.php'; ?>