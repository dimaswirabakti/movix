<?php
require_once __DIR__ . '/../includes/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Wajib login
if (!is_logged_in()) {
    header('Location: /pages/login.php?next=' . rawurlencode('/pages/watchlist.php'));
    exit;
}
$uid = (int) $_SESSION['user_id'];

$statusLabels = [
    'plan_to_watch' => 'Mau Ditonton',
    'watching' => 'Sedang Ditonton',
    'watched' => 'Sudah Ditonton',
];

// Handle POST (ubah status / hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieId = (isset($_POST['movie_id']) && ctype_digit((string) $_POST['movie_id'])) ? (int) $_POST['movie_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($movieId > 0 && $action === 'update_status') {
        $status = $_POST['status'] ?? '';
        if (isset($statusLabels[$status])) {
            $stmt = db()->prepare('UPDATE watchlist SET status = ? WHERE user_id = ? AND movie_id = ?');
            $stmt->execute([$status, $uid, $movieId]);
        }
    } elseif ($movieId > 0 && $action === 'remove') {
        $stmt = db()->prepare('DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?');
        $stmt->execute([$uid, $movieId]);
    }

    header('Location: /pages/watchlist.php' . (isset($_POST['filter']) && $_POST['filter'] !== '' ? '?status=' . urlencode($_POST['filter']) : ''));
    exit;
}

// Filter dari tab
$filter = $_GET['status'] ?? '';
if ($filter !== '' && !isset($statusLabels[$filter])) {
    $filter = '';
}

// Ambil data watchlist
$sql = 'SELECT w.movie_id, w.status, m.title, m.backdrop_url, m.poster_url
        FROM watchlist w
        JOIN movies m ON m.id = w.movie_id
        WHERE w.user_id = ?';
$params = [$uid];
if ($filter !== '') {
    $sql .= ' AND w.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY w.added_at DESC, w.movie_id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Total keseluruhan film
$totalStmt = db()->prepare('SELECT COUNT(*) FROM watchlist WHERE user_id = ?');
$totalStmt->execute([$uid]);
$total = (int) $totalStmt->fetchColumn();

// Warna titik status
$statusDot = [
    'plan_to_watch' => 'var(--ash)',
    'watching' => 'var(--sky)',
    'watched' => 'var(--moss)',
];

require_once __DIR__ . '/../includes/partials/header.php';
?>

<section class="container py-4 py-lg-5">

  <div class="catalog-head mb-4">
    <div>
      <p class="eyebrow mb-1">KOLEKSI SAYA</p>
      <h1 class="section-title mb-0">Watchlist Saya</h1>
    </div>
    <p class="catalog-count mb-0"><?= $total ?> film</p>
  </div>

  <!-- Tab filter status -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="filter-pill <?= $filter === '' ? 'is-active' : '' ?>" href="/pages/watchlist.php">Semua</a>
    <?php foreach ($statusLabels as $key => $label): ?>
      <a class="filter-pill <?= $filter === $key ? 'is-active' : '' ?>" href="/pages/watchlist.php?status=<?= e($key) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (!$items): ?>
    <div class="catalog-empty">
      <h2 class="mb-2">Belum ada film di sini.</h2>
      <p class="mb-4">Jelajahi film dan tambahkan ke watchlist Anda.</p>
      <a href="/pages/movies.php" class="btn btn-brass px-4">Jelajahi Film</a>
    </div>
  <?php else: ?>
    <div class="row g-3 g-md-4">
      <?php foreach ($items as $it): ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl">
          <div class="wl-card">
            <div class="wl-card__media">
              <?php if ($it['backdrop_url'] || $it['poster_url']): ?>
                <img src="<?= e($it['backdrop_url'] ?: $it['poster_url']) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
              <?php endif; ?>

              <!-- Overlay aksi (muncul ketika hover atau klik kartu) -->
              <div class="wl-card__actions">
                <p class="wl-actions-title">Ubah Status</p>
                <div class="wl-status-options">
                  <?php foreach ($statusLabels as $key => $label): ?>
                    <form method="POST" action="/pages/watchlist.php">
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="movie_id" value="<?= (int) $it['movie_id'] ?>">
                      <input type="hidden" name="filter" value="<?= e($filter) ?>">
                      <input type="hidden" name="status" value="<?= e($key) ?>">
                      <button type="submit" class="wl-status-opt <?= $it['status'] === $key ? 'is-current' : '' ?>">
                        <span class="wl-dot" style="background:<?= $statusDot[$key] ?>"></span>
                        <?= e($label) ?>
                        <i class="bi bi-check-lg wl-check"></i>
                      </button>
                    </form>
                  <?php endforeach; ?>
                </div>
                <form method="POST" action="/pages/watchlist.php" class="wl-remove-form">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="movie_id" value="<?= (int) $it['movie_id'] ?>">
                  <input type="hidden" name="filter" value="<?= e($filter) ?>">
                  <button type="submit" class="wl-remove-btn"><i class="bi bi-trash"></i> Hapus</button>
                </form>
              </div>
            </div>

            <div class="wl-card__foot">
              <p class="wl-status-label" style="color:<?= $statusDot[$it['status']] ?>">
                <span class="wl-dot" style="background:<?= $statusDot[$it['status']] ?>"></span>
                <?= e(mb_strtoupper($statusLabels[$it['status']])) ?>
              </p>
              <h3 class="wl-title"><?= e($it['title']) ?></h3>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<script>
// Klik kartu untuk membuka/menutup overlay aksi
document.querySelectorAll('.wl-card').forEach(function (card) {
  card.addEventListener('click', function (e) {
    // Jangan toggle kalau yang diklik adalah tombol/aksi di dalam overlay.
    if (e.target.closest('button') || e.target.closest('form')) return;
    var wasOpen = card.classList.contains('is-open');
    document.querySelectorAll('.wl-card.is-open').forEach(function (c) { c.classList.remove('is-open'); });
    if (!wasOpen) card.classList.add('is-open');
  });
});

// Klik di luar kartu menutup semua overlay yang terbuka.
document.addEventListener('click', function (e) {
  if (!e.target.closest('.wl-card')) {
    document.querySelectorAll('.wl-card.is-open').forEach(function (c) { c.classList.remove('is-open'); });
  }
});

// Konfirmasi sebelum hapus.
document.querySelectorAll('.wl-remove-form').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!confirm('Hapus film ini dari watchlist?')) e.preventDefault();
  });
});
</script>

<?php require_once __DIR__ . '/../includes/partials/footer.php'; ?>