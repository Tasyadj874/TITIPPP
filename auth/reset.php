<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

auth_start();

if (auth_check()) {
    auth_redirect_home();
}

$conn = db();

$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
$error = '';
$success = '';

if ($token === '') {
    $error = 'Token tidak ditemukan.';
}

$resetRow = null;
if ($error === '') {
    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare("SELECT id, user_id, expires_at, used_at FROM password_resets WHERE token_hash = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $res = $stmt->get_result();
    $resetRow = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$resetRow) {
        $error = 'Token reset tidak valid.';
    } elseif ($resetRow['used_at'] !== null) {
        $error = 'Token reset sudah digunakan.';
    } else {
        $expiresAt = strtotime((string)$resetRow['expires_at']);
        if ($expiresAt !== false && $expiresAt < time()) {
            $error = 'Token reset sudah expired.';
        }
    }
}

if ($error === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if ($password === '' || $password2 === '') {
        $error = 'Lengkapi password.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $userId = (int)$resetRow['user_id'];
        $hash = $password;

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $userId);
            $stmt->execute();
            $stmt->close();

            $resetId = (int)$resetRow['id'];
            $stmt = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
            $stmt->bind_param('i', $resetId);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success = 'Password berhasil diubah. Silakan login.';
        } catch (Throwable $e) {
            $conn->rollback();
            $error = 'Gagal reset password. Coba lagi.';
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
          <h1>Buat Password Baru</h1>
          <p>Masukkan password baru untuk akun User.</p>
          <div class="auth-badges">
            <span class="auth-badge">User</span>
            <span class="auth-badge">Reset</span>
          </div>
        </section>

        <section class="auth-card">
          <h2>Password Baru</h2>
          <div class="hint">Pastikan password kamu aman.</div>

          <?php if ($error !== ''): ?>
            <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <?php if ($success !== ''): ?>
            <div style="margin-bottom: 1rem; padding: 10px; border: 1px solid #ddd; border-radius: 12px; background: #fff; font-weight: 800;">
              <?php echo htmlspecialchars($success); ?>
            </div>
            <div style="margin-top: 0.5rem; color: #6b7280; font-size: 0.95rem;">
              <a href="login.php?role=user" style="color: var(--primary); font-weight: 800;">Login</a>
            </div>
          <?php elseif ($error === ''): ?>
            <form class="auth-form" method="POST">
              <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" required />
              </div>

              <div class="form-group">
                <label for="password2">Konfirmasi Password</label>
                <input type="password" id="password2" name="password2" required />
              </div>

              <div class="auth-actions">
                <button type="submit" class="auth-primary">Simpan Password</button>
              </div>
            </form>
          <?php else: ?>
            <div style="margin-top: 1rem; color: #6b7280; font-size: 0.95rem;">
              Kembali ke
              <a href="forgot.php" style="color: var(--primary); font-weight: 800;">Lupa Password</a>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </div>

    <script>
      feather.replace();
    </script>

    <script src="../js/script.js?v=20251230"></script>
  </body>
</html>
