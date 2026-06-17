</main>

<footer class="footer-movix mt-auto">
  <div class="sprocket-rail"></div>
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-12 col-md-4">
        <a class="footer-brand h5 mb-3 d-inline-flex" href="/index.php">
          <i class="bi bi-film"></i> MOVIX
        </a>
        <p class="small mb-0" style="max-width: 18rem;">
          Gerbang Anda menuju dunia sinema. Temukan, nilai, dan simpan film favorit.
        </p>
      </div>

      <div class="col-6 col-md-2 offset-md-2">
        <h4>Jelajahi</h4>
        <ul class="list-unstyled footer-links">
          <li><a href="/index.php">Beranda</a></li>
          <li><a href="/pages/movies.php">Daftar Film</a></li>
        </ul>
      </div>

      <div class="col-6 col-md-2">
        <h4>Akun</h4>
        <ul class="list-unstyled footer-links">
          <?php if (!empty($loggedIn)): ?>
            <li><a href="/pages/profile.php">Profil</a></li>
            <li><a href="/pages/watchlist.php">Watchlist</a></li>
            <li><a href="/pages/saved.php">Tersimpan</a></li>
          <?php else: ?>
            <li><a href="/pages/login.php">Masuk</a></li>
            <li><a href="/pages/register.php">Daftar</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="col-6 col-md-2">
        <h4>Tentang</h4>
        <ul class="list-unstyled footer-links">
          <li><a href="/index.php">Tentang Movix</a></li>
          <li><a href="/index.php">Kebijakan Privasi</a></li>
          <li><a href="/index.php">Kontak</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom mt-4 pt-3 d-flex flex-column flex-sm-row justify-content-between gap-2">
      <span>&copy; <?= date('Y') ?> Movix. Semua hak dilindungi.</span>
      <span>Dibuat untuk tugas PPW 1 &amp; PBD</span>
    </div>
  </div>
</footer>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
