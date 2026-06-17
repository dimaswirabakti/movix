<?php
require_once __DIR__ . '/../includes/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (is_logged_in()) { header('Location: /index.php'); exit; }

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $errors['username'] = 'Username wajib diisi.';
    } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        $errors['username'] = 'Username harus 3 sampai 50 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username hanya boleh huruf, angka, dan underscore.';
    }

    if ($email === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    if ($password === '') {
        $errors['password'] = 'Kata sandi wajib diisi.';
    } elseif (mb_strlen($password) < 8) {
        $errors['password'] = 'Kata sandi minimal 8 karakter.';
    }

    if (!isset($errors['username'])) {
        $c = db()->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $c->execute([$username]);
        if ($c->fetch()) $errors['username'] = 'Username ini sudah dipakai.';
    }
    if (!isset($errors['email'])) {
        $c = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $c->execute([mb_strtolower($email)]);
        if ($c->fetch()) $errors['email'] = 'Email ini sudah terdaftar.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins  = db()->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $ins->execute([$username, $email, $hash]); // trigger meng-lowercase email
        $newId = (int) db()->lastInsertId();

        session_regenerate_id(true); // auto-login
        $_SESSION['user_id'] = $newId;
        $_SESSION['username'] = $username;
        $_SESSION['avatar_url'] = null;
        header('Location: /index.php');
        exit;
    }
}

$authTitle = 'Daftar';
$authEyebrow = 'BUAT AKUN BARU';
$authHeading = 'Daftar ke Movix';
$asideClass = 'auth-aside--register';
$asideEyebrow = 'BERGABUNG SEKARANG';
$asideTitle = 'Mulai petualangan sinema Anda.';
$asideSubtitle = 'Bergabung dengan ribuan sinefil Indonesia. Buat watchlist, beri ulasan, dan temukan film baru setiap hari.';

require_once __DIR__ . '/../includes/partials/auth_header.php';
?>

<form id="registerForm" class="auth-form" method="POST" action="/pages/register.php" novalidate>
  <div class="mb-3">
    <label for="username" class="form-label">Username</label>
    <input type="text" class="form-control" id="username" name="username"
           value="<?= e($username) ?>" maxlength="50" autocomplete="username" placeholder="username" required>
    <div class="auth-err" id="errUser"><?= isset($errors['username']) ? e($errors['username']) : '' ?></div>
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email"
           value="<?= e($email) ?>" autocomplete="email" placeholder="nama@email.com" required>
    <div class="auth-err" id="errEmail"><?= isset($errors['email']) ? e($errors['email']) : '' ?></div>
  </div>

  <div class="mb-2">
    <label for="password" class="form-label">Kata sandi</label>
    <div class="auth-pass">
      <input type="password" class="form-control" id="password" name="password"
             autocomplete="new-password" placeholder="Minimal 8 karakter" required>
      <button type="button" class="auth-eye" data-toggle-password="password" aria-label="Lihat kata sandi">
        <i class="bi bi-eye-slash"></i>
      </button>
    </div>
    <div class="auth-strength" id="strengthBars" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="d-flex justify-content-between">
      <small class="auth-strength__label" id="strengthLabel"></small>
      <small class="auth-err" id="errPass" style="margin-top:.3rem"><?= isset($errors['password']) ? e($errors['password']) : '' ?></small>
    </div>
  </div>

  <button type="submit" class="btn btn-brass w-100 py-2 mb-3 mt-2">Buat Akun</button>
  <p class="auth-alt mb-0">Sudah punya akun? <a href="/pages/login.php">Masuk</a></p>
</form>

<script>
(function () {
  var form = document.getElementById('registerForm');
  var u = document.getElementById('username');
  var em = document.getElementById('email');
  var pw = document.getElementById('password');
  var bars = document.getElementById('strengthBars').children;
  var label = document.getElementById('strengthLabel');

  function setErr(el, id, msg) {
    var box = document.getElementById(id);
    if (msg) { el.classList.add('input-error'); box.textContent = msg; }
    else { el.classList.remove('input-error'); box.textContent = ''; }
  }
  function validEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
  function validUser(v){ return /^[a-zA-Z0-9_]{3,50}$/.test(v); }

  pw.addEventListener('input', function () {
    var v = pw.value, score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
    if (/[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v)) score++;
    var eff = v ? Math.max(score, 1) : 0;
    var colors = ['var(--rust)', 'var(--brass)', 'var(--moss)'];
    var texts  = ['Lemah', 'Sedang', 'Kuat'];
    for (var i = 0; i < 3; i++) {
      bars[i].style.background = (i < eff) ? colors[eff - 1] : 'var(--slate)';
    }
    label.textContent = eff ? texts[eff - 1] : '';
    label.style.color = eff ? colors[eff - 1] : '';
    if (v.length >= 8) setErr(pw, 'errPass', '');
  });

  u.addEventListener('input', function(){ if (validUser(u.value.trim())) setErr(u, 'errUser', ''); });
  em.addEventListener('input', function(){ if (validEmail(em.value.trim())) setErr(em, 'errEmail', ''); });

  form.addEventListener('submit', function (e) {
    var ok = true;
    if (!validUser(u.value.trim())) { setErr(u, 'errUser', 'Username 3 sampai 50 karakter (huruf, angka, underscore).'); ok = false; }
    if (!validEmail(em.value.trim())) { setErr(em, 'errEmail', 'Format email tidak valid.'); ok = false; }
    if (pw.value.length < 8) { setErr(pw, 'errPass', 'Kata sandi minimal 8 karakter.'); ok = false; }
    if (!ok) e.preventDefault();
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/partials/auth_footer.php'; ?>