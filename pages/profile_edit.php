<?php
require_once __DIR__ . '/../includes/init.php';

// Proteksi halaman — wajib login
if (session_status() === PHP_SESSION_NONE) session_start();
if (!is_logged_in()) {
    header('Location: /pages/login.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$errors   = [];
$success  = '';

// Ambil data user saat ini
$stmt = db()->prepare('SELECT username, email, avatar_url FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /pages/logout.php');
    exit;
}

// ----------------------------------------------------------------
// Proses form saat POST
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Validasi username ---
    $newUsername = trim($_POST['username'] ?? '');
    if ($newUsername === '') {
        $errors['username'] = 'Username tidak boleh kosong.';
    } elseif (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
        $errors['username'] = 'Username harus 3–50 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
        $errors['username'] = 'Username hanya boleh huruf, angka, dan underscore.';
    } else {
        // Cek apakah username sudah dipakai user lain
        $chk = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $chk->execute([$newUsername, $userId]);
        if ($chk->fetch()) {
            $errors['username'] = 'Username ini sudah dipakai orang lain.';
        }
    }

    // --- Validasi & proses upload avatar ---
    $newAvatarUrl = $user['avatar_url']; // default: tetap avatar lama

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['avatar'];

        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Upload gagal. Coba lagi.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            // Maksimal 2MB
            $errors['avatar'] = 'Ukuran file maksimal 2MB.';
        } else {
            // Validasi tipe file via MIME (lebih aman daripada cek ekstensi saja)
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo       = new finfo(FILEINFO_MIME_TYPE);
            $mime        = $finfo->file($file['tmp_name']);

            if (!in_array($mime, $allowedMime, true)) {
                $errors['avatar'] = 'Format file harus JPG, PNG, atau WebP.';
            } else {
                // Tentukan ekstensi dari MIME (bukan dari nama file asli — lebih aman)
                $ext      = match($mime) {
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                };

                // Nama file unik: userID_timestamp.ext
                $filename    = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $destination = __DIR__ . '/../assets/img/avatars/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $errors['avatar'] = 'Gagal menyimpan file. Hubungi administrator.';
                } else {
                    // Hapus avatar lama jika ada (bersihkan storage)
                    if ($user['avatar_url']) {
                        $oldPath = __DIR__ . '/../' . $user['avatar_url'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $newAvatarUrl = 'assets/img/avatars/' . $filename;
                }
            }
        }
    }

    // --- Simpan ke DB jika tidak ada error ---
    if (empty($errors)) {
        $upd = db()->prepare(
            'UPDATE users SET username = ?, avatar_url = ? WHERE id = ?'
        );
        $upd->execute([$newUsername, $newAvatarUrl, $userId]);

        // Perbarui session agar navbar langsung update
        $_SESSION['username']   = $newUsername;
        $_SESSION['avatar_url'] = $newAvatarUrl;

        $success = 'Profil berhasil diperbarui.';

        // Refresh data user untuk ditampilkan
        $user['username']   = $newUsername;
        $user['avatar_url'] = $newAvatarUrl;
    }
}

require_once __DIR__ . '/../includes/partials/header.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">

    <div class="d-flex align-items-center gap-3 mb-4">
      <a href="/pages/profile.php" class="btn btn-sm btn-outline-secondary">← Kembali</a>
      <h1 class="h4 mb-0" style="font-family:var(--font-display)">Edit Profil</h1>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Preview avatar saat ini -->
    <div class="text-center mb-4">
      <?= render_avatar($user['avatar_url'], $user['username'], 96, 'mb-2') ?>
      <p class="small text-secondary mt-2 mb-0">Foto profil saat ini</p>
    </div>

    <div class="card" style="background:var(--charcoal);border-color:var(--slate)">
      <div class="card-body p-4">
        <form id="formEditProfil"
              method="POST"
              action="/pages/profile_edit.php"
              enctype="multipart/form-data"
              novalidate>

          <!-- Upload Avatar -->
          <div class="mb-4">
            <label for="avatar" class="form-label fw-semibold">
              Foto Profil
              <span class="text-secondary fw-normal small ms-1">(opsional · JPG/PNG/WebP · maks. 2MB)</span>
            </label>
            <input type="file"
                   class="form-control <?= isset($errors['avatar']) ? 'is-invalid' : '' ?>"
                   id="avatar"
                   name="avatar"
                   accept="image/jpeg,image/png,image/webp">
            <?php if (isset($errors['avatar'])): ?>
              <div class="invalid-feedback"><?= e($errors['avatar']) ?></div>
            <?php endif; ?>
            <!-- Preview gambar sebelum submit (JS) -->
            <div id="avatarPreviewWrap" class="mt-2 d-none">
              <img id="avatarPreview"
                   src=""
                   alt="Preview foto profil"
                   width="72" height="72"
                   style="object-fit:cover;border-radius:50%;border:2px solid var(--brass)">
              <span class="small text-secondary ms-2">Preview</span>
            </div>
          </div>

          <!-- Username -->
          <div class="mb-4">
            <label for="username" class="form-label fw-semibold">Username</label>
            <input type="text"
                   class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                   id="username"
                   name="username"
                   value="<?= e($user['username']) ?>"
                   maxlength="50"
                   autocomplete="username"
                   required>
            <div class="form-text text-secondary">
              Hanya huruf, angka, dan underscore. 3–50 karakter.
            </div>
            <?php if (isset($errors['username'])): ?>
              <div class="invalid-feedback"><?= e($errors['username']) ?></div>
            <?php endif; ?>
            <!-- Pesan error inline dari JS -->
            <div id="usernameError" class="text-danger small mt-1 d-none"></div>
          </div>

          <button type="submit" class="btn w-100 fw-semibold"
                  style="background:var(--brass);color:var(--ink)">
            Simpan Perubahan
          </button>

        </form>
      </div>
    </div>

  </div>
</div>

<script>
// ----------------------------------------------------------------
// Validasi sisi klien sebelum submit (memenuhi rubrik PPW 1)
// ----------------------------------------------------------------
(function () {
  const form        = document.getElementById('formEditProfil');
  const usernameEl  = document.getElementById('username');
  const usernameErr = document.getElementById('usernameError');
  const avatarInput = document.getElementById('avatar');
  const previewWrap = document.getElementById('avatarPreviewWrap');
  const previewImg  = document.getElementById('avatarPreview');

  // --- Preview gambar saat file dipilih (addEventListener, bukan onclick) ---
  avatarInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) {
      previewWrap.classList.add('d-none');
      return;
    }

    // Validasi tipe & ukuran di sisi klien (double-check sebelum ke server)
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowed.includes(file.type)) {
      showUsernameError(''); // reset username error
      this.value = '';
      previewWrap.classList.add('d-none');
      alert('Format file harus JPG, PNG, atau WebP.');
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      this.value = '';
      previewWrap.classList.add('d-none');
      alert('Ukuran file maksimal 2MB.');
      return;
    }

    // Tampilkan preview dengan FileReader (manipulasi DOM)
    const reader = new FileReader();
    reader.addEventListener('load', function (e) {
      previewImg.src = e.target.result;
      previewWrap.classList.remove('d-none');
    });
    reader.readAsDataURL(file);
  });

  // --- Validasi username real-time ---
  usernameEl.addEventListener('input', function () {
    validateUsername(this.value);
  });

  function validateUsername(val) {
    val = val.trim();
    if (val.length === 0) {
      return showUsernameError('Username tidak boleh kosong.');
    }
    if (val.length < 3 || val.length > 50) {
      return showUsernameError('Username harus 3–50 karakter.');
    }
    if (!/^[a-zA-Z0-9_]+$/.test(val)) {
      return showUsernameError('Hanya huruf, angka, dan underscore yang diperbolehkan.');
    }
    clearUsernameError();
  }

  function showUsernameError(msg) {
    usernameErr.textContent = msg;
    usernameErr.classList.remove('d-none');
    usernameEl.classList.add('is-invalid');
  }

  function clearUsernameError() {
    usernameErr.classList.add('d-none');
    usernameEl.classList.remove('is-invalid');
  }

  // --- Cegah submit jika ada error ---
  form.addEventListener('submit', function (e) {
    const username = usernameEl.value.trim();
    const valid    = /^[a-zA-Z0-9_]{3,50}$/.test(username);

    if (!valid) {
      e.preventDefault();
      validateUsername(username); // tampilkan pesan error
      usernameEl.focus();
    }
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/partials/footer.php'; ?>