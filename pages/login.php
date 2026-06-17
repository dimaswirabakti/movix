<?php
require_once __DIR__ . '/../includes/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (is_logged_in()) { header('Location: /index.php'); exit; }

// Tujuan setelah login
$next = $_GET['next'] ?? '/index.php';
if (!is_string($next) || $next === '' || $next[0] !== '/' || str_starts_with($next, '//')) {
    $next = '/index.php';
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = db()->prepare('SELECT id, username, password_hash, avatar_url FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([mb_strtolower($email)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['avatar_url'] = $user['avatar_url'];
            header('Location: ' . $next);
            exit;
        }
        $error = 'Email atau kata sandi salah.';
    }
}

$authTitle = 'Masuk';
$authEyebrow = 'SELAMAT DATANG KEMBALI';
$authHeading = 'Masuk ke akun Anda';
$asideClass = 'auth-aside--login';
$asideEyebrow = 'GERBANG SINEMA ANDA';
$asideTitle = 'Setiap film adalah perjalanan baru.';
$asideSubtitle = 'Temukan, nilai, dan simpan film favorit Anda bersama komunitas sinefil Indonesia.';

require_once __DIR__ . '/../includes/partials/auth_header.php';
?>

<?php if ($error): ?>
  <div class="alert alert-danger auth-alert" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<form id="loginForm" class="auth-form" method="POST"
      action="/pages/login.php?next=<?= e(rawurlencode($next)) ?>" novalidate>
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email"
           value="<?= e($email) ?>" autocomplete="email" placeholder="nama@email.com" required>
    <div class="auth-err" id="errEmail"></div>
  </div>

  <div class="mb-4">
    <label for="password" class="form-label">Kata sandi</label>
    <div class="auth-pass">
      <input type="password" class="form-control" id="password" name="password"
             autocomplete="current-password" placeholder="Kata sandi" required>
      <button type="button" class="auth-eye" data-toggle-password="password" aria-label="Lihat kata sandi">
        <i class="bi bi-eye-slash"></i>
      </button>
    </div>
    <div class="auth-err" id="errPass"></div>
  </div>

  <button type="submit" class="btn btn-brass w-100 py-2 mb-3">Masuk</button>
  <p class="auth-alt mb-0">Belum punya akun? <a href="/pages/register.php">Daftar</a></p>
</form>

<script>
(function () {
  var form = document.getElementById('loginForm');
  var email = document.getElementById('email');
  var pass = document.getElementById('password');
  function setErr(el, id, msg) {
    var box = document.getElementById(id);
    if (msg) { el.classList.add('input-error'); box.textContent = msg; }
    else { el.classList.remove('input-error'); box.textContent = ''; }
  }
  function validEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

  email.addEventListener('input', function(){ if (validEmail(email.value.trim())) setErr(email, 'errEmail', ''); });
  pass.addEventListener('input', function(){ if (pass.value) setErr(pass, 'errPass', ''); });

  form.addEventListener('submit', function (e) {
    var ok = true;
    if (!validEmail(email.value.trim())) { setErr(email, 'errEmail', 'Format email tidak valid.'); ok = false; }
    if (!pass.value) { setErr(pass, 'errPass', 'Kata sandi wajib diisi.'); ok = false; }
    if (!ok) e.preventDefault();
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/partials/auth_footer.php'; ?>