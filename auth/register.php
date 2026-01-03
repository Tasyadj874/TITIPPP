<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

auth_start();

if (auth_check()) {
    auth_redirect_home();
}

$conn = db();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim((string)($_POST['nama'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $noHp = trim((string)($_POST['no_hp'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if ($nama === '' || $email === '' || $password === '' || $password2 === '') {
        $error = 'Lengkapi semua field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($exists) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = $password;
            $noHpDb = $noHp !== '' ? $noHp : null;

            $stmt = $conn->prepare('INSERT INTO users (nama, email, no_hp, password_hash, status_aktif) VALUES (?, ?, ?, ?, 1)');
            $stmt->bind_param('ssss', $nama, $email, $noHpDb, $hash);
            $ok = $stmt->execute();
            $userId = (int)$conn->insert_id;
            $stmt->close();

            if ($ok && $userId > 0) {
                $stmt = $conn->prepare('SELECT id, email, nama, password_hash, status_aktif FROM users WHERE id = ? LIMIT 1');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();

                auth_login('user', $userId, $row ?: ['id' => $userId, 'email' => $email, 'nama' => $nama]);

                $stmt = $conn->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->close();

                header('Location: ../user/index.php');
                exit();
            }

            $error = 'Gagal membuat akun. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kurir TITIP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap"
      rel="stylesheet"
    />

    <script src="https://unpkg.com/feather-icons"></script>

    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/auth.css" />
  </head>

  <body>
    <div class="red-navbar">
      <nav class="navbar">
        <div class="logo-container">
          <img src="../images/LOGO TITIP.png" alt="Logo TITIP" class="logo" />
        </div>
        <div class="navbar-nav">
          <a href="../index.php">Home</a>
        </div>
        <div class="navbar-extra">
          <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
        </div>
      </nav>
    </div>

    <div class="auth-page">
      <div class="auth-shell">
        <section class="auth-brand">
          <img src="../images/Kurir logo.png" alt="Kurir Logo" style="max-width: 220px; width: 100%;" />
          <h1>Buat Akun User</h1>
          <p>Daftar untuk mulai pesan layanan TITIP dari website.</p>
          <div class="auth-badges">
            <span class="auth-badge">TITIP</span>
            <span class="auth-badge">Cepat</span>
            <span class="auth-badge">Aman</span>
          </div>
        </section>

        <section class="auth-card">
          <h2>Register</h2>
          <div class="hint">Akun ini khusus untuk User (pemesan).</div>

          <?php if ($error !== ''): ?>
            <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form class="auth-form" method="POST">
            <div class="form-group">
              <label for="nama">Nama</label>
              <input type="text" id="nama" name="nama" required value="<?php echo htmlspecialchars((string)($_POST['nama'] ?? '')); ?>" />
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars((string)($_POST['email'] ?? '')); ?>" />
            </div>

            <div class="form-group">
              <label for="no_hp">No HP (opsional)</label>
              <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars((string)($_POST['no_hp'] ?? '')); ?>" />
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" id="password" name="password" required />
            </div>

            <div class="form-group">
              <label for="password2">Konfirmasi Password</label>
              <input type="password" id="password2" name="password2" required />
            </div>

            <div class="auth-actions">
              <button type="submit" class="auth-primary">Buat Akun</button>
            </div>
          </form>

          <div style="margin-top: 1rem; color: #6b7280; font-size: 0.95rem;">
            Sudah punya akun?
            <a href="login.php?role=user" style="color: var(--primary); font-weight: 800;">Login</a>
          </div>
        </section>
      </div>
    </div>

    <script>
      feather.replace();
    </script>

    <script src="../js/script.js?v=20251230"></script>
  </body>
</html>
