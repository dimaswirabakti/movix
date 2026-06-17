<?php
require_once __DIR__ . '/../includes/init.php';

// Ambil dan bersihkan parameter filter dari URL
$q = trim((string) ($_GET['q'] ?? ''));
$genreId = isset($_GET['genre']) && ctype_digit((string) $_GET['genre']) ? (int) $_GET['genre'] : 0;
$sort = $_GET['sort'] ?? 'rating';
$page = isset($_GET['page']) && ctype_digit((string) $_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 12;

$sortOptions = [
    'rating' => 'Rating Tertinggi',
    'newest' => 'Terbaru',
    'az' => 'A sampai Z',
];
if (!isset($sortOptions[$sort])) {
    $sort = 'rating';
}

// Buat query WHERE secara dinamis (prepared statement)
$where = [];
$params = [];

if ($q !== '') {
    $where[] = 'm.title LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($genreId > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM movie_genres mg WHERE mg.movie_id = m.id AND mg.genre_id = ?)';
    $params[] = $genreId;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$orderSql = match ($sort) {
    'newest' => 'm.release_year DESC, m.id DESC',
    'az' => 'm.title ASC',
    default => 'm.avg_rating DESC, m.review_count DESC, m.id ASC',
};

// Hitung total untuk pagination
$countStmt = db()->prepare("SELECT COUNT(*) FROM movies m {$whereSql}");
$countStmt->execute($params);
$totalMovies = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalMovies / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Ambil data film halaman ini
$sql = "SELECT m.id, m.title, m.release_year, m.avg_rating, m.poster_url
        FROM movies m
        {$whereSql}
        ORDER BY {$orderSql}
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

// Genre untuk dropdown filter
$genres = db()->query('SELECT id, name FROM genres ORDER BY name')->fetchAll();
$activeGenreName = '';
foreach ($genres as $g) {
    if ((int) $g['id'] === $genreId) {
        $activeGenreName = $g['name'];
        break;
    }
}

$hasFilter = $q !== '' || $genreId > 0;

require_once __DIR__ . '/../includes/partials/header.php';
?>

<section class="container py-4 py-lg-5">

  <div class="catalog-head mb-4">
    <div>
      <p class="eyebrow mb-1">KATALOG</p>
      <h1 class="section-title mb-0">Jelajahi Film</h1>
    </div>
    <p class="catalog-count mb-0"><?= (int) $totalMovies ?> film</p>
  </div>

  <form class="filter-bar mb-4" method="get" action="/pages/movies.php" id="filterForm">
    <div class="filter-search">
      <i class="bi bi-search"></i>
      <input type="search" class="form-control" name="q" id="catalogSearch"
             value="<?= e($q) ?>" placeholder="Cari judul...">
    </div>

    <div class="dropdown">
      <button class="filter-pill <?= $genreId > 0 ? 'is-active' : '' ?> dropdown-toggle" type="button"
              data-bs-toggle="dropdown" aria-expanded="false">
        <?= $genreId > 0 ? e($activeGenreName) : 'Semua Genre' ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-movix">
        <li><a class="dropdown-item <?= $genreId === 0 ? 'active' : '' ?>" href="<?= e(query_with(['genre' => null, 'page' => null])) ?>">Semua Genre</a></li>
        <?php foreach ($genres as $g): ?>
          <li><a class="dropdown-item <?= $genreId === (int) $g['id'] ? 'active' : '' ?>"
                 href="<?= e(query_with(['genre' => $g['id'], 'page' => null])) ?>"><?= e($g['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="dropdown">
      <button class="filter-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <?= e($sortOptions[$sort]) ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-movix">
        <?php foreach ($sortOptions as $key => $label): ?>
          <li><a class="dropdown-item <?= $sort === $key ? 'active' : '' ?>"
                 href="<?= e(query_with(['sort' => $key, 'page' => null])) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <?php if ($hasFilter): ?>
      <a class="filter-pill filter-pill--clear" href="/pages/movies.php">
        <i class="bi bi-x-lg"></i> Hapus filter
      </a>
    <?php endif; ?>
  </form>

  <?php if (!$movies): ?>
    <div class="catalog-empty">
      <h2 class="mb-2">Tidak ada film yang cocok.</h2>
      <p class="mb-4">Coba ubah kata kunci atau filter.</p>
      <a href="/pages/movies.php" class="btn btn-brass px-4">Hapus filter</a>
    </div>
  <?php else: ?>

    <div class="row g-3 g-md-4">
      <?php foreach ($movies as $m): ?>
        <div class="col-6 col-md-4 col-lg-2">
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
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination-mvx" aria-label="Navigasi halaman">
        <?php if ($page > 1): ?>
          <a href="<?= e(query_with(['page' => $page - 1])) ?>" aria-label="Sebelumnya">‹</a>
        <?php else: ?>
          <span class="is-disabled">‹</span>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p === $page): ?>
            <span class="is-current"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= e(query_with(['page' => $p])) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="<?= e(query_with(['page' => $page + 1])) ?>" aria-label="Berikutnya">›</a>
        <?php else: ?>
          <span class="is-disabled">›</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

  <?php endif; ?>

</section>

<script>
(function () {
  var input = document.getElementById('catalogSearch');
  var form = document.getElementById('filterForm');
  if (!input || !form) return;

  var timer = null;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () {
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        HTMLFormElement.prototype.submit.call(form);
      }
    }, 500);
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/partials/footer.php'; ?>