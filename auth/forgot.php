<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

auth_start();

if (auth_check()) {
    auth_redirect_home();
}

$conn = db();

$error = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '') {
        $error = 'Masukkan email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } else {
        $stmt = $conn->prepare('SELECT id, email, status_aktif FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$user || (int)($user['status_aktif'] ?? 0) !== 1) {
            $error = 'Jika email terdaftar, link reset akan dibuat.';
        } else {
            $userId = (int)$user['id'];
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $tokenHash = hash('sha256', $token);

            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
            $stmt->bind_param('is', $userId, $tokenHash);
            $stmt->execute();
            $stmt->close();

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
            $resetLink = $scheme . '://' . $host . $base . '/reset.php?token=' . urlencode($token);
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
          <h1>Lupa Password</h1>
          <p>Masukkan email akun User. Sistem akan membuat link reset password.</p>
          <div class="auth-badges">
            <span class="auth-badge">User</span>
            <span class="auth-badge">Reset</span>
          </div>
        </section>

        <section class="auth-card">
          <h2>Reset Password</h2>
          <div class="hint">Link reset berlaku 30 menit.</div>

          <?php if ($error !== ''): ?>
            <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <?php if ($resetLink !== ''): ?>
            <div style="margin-bottom: 1rem; padding: 10px; border: 1px solid #ddd; border-radius: 12px; background: #fff;">
              <div style="font-weight: 800; margin-bottom: 6px;">Link reset (copy):</div>
              <div style="word-break: break-all; color: #111827; font-weight: 700;"><?php echo htmlspecialchars($resetLink); ?></div>
            </div>
          <?php endif; ?>

          <form class="auth-form" method="POST">
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars((string)($_POST['email'] ?? '')); ?>" />
            </div>

            <div class="auth-actions">
              <button type="submit" class="auth-primary">Buat Link Reset</button>
            </div>
          </form>

          <div style="margin-top: 1rem; color: #6b7280; font-size: 0.95rem;">
            Kembali ke
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
