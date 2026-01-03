<?php
require_once __DIR__ . '/../includes/auth.php';
auth_start();

if (auth_check()) {
    auth_redirect_home();
}

$role = isset($_GET['role']) ? trim((string)$_GET['role']) : 'user';
$allowed = ['user', 'kurir', 'mitra', 'admin'];
if (!in_array($role, $allowed, true)) {
    $role = 'user';
}

$error = isset($_GET['error']) ? (string)$_GET['error'] : '';
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
          <h1>Masuk ke TITIP</h1>
          <p>
            Pilih peran kamu, lalu login untuk mulai pesan, kelola toko, atau ambil order.
          </p>
          <div class="auth-badges">
            <span class="auth-badge">Cepat</span>
            <span class="auth-badge">Aman</span>
            <span class="auth-badge">Mudah</span>
            <span class="auth-badge">#SemuaBisaDiTITIP</span>
          </div>
        </section>

        <section class="auth-card">
          <h2>Login</h2>
          <div class="hint">Login seperti aplikasi delivery modern: simple dan fokus.</div>

          <?php if ($error !== ''): ?>
            <div class="auth-error">Login gagal. Periksa akun dan password.</div>
          <?php endif; ?>

          <div class="auth-roles" id="role-grid">
            <div class="auth-role" data-role="user">
              <strong>User</strong>
              <span>Pesan layanan</span>
            </div>
            <div class="auth-role" data-role="kurir">
              <strong>Kurir</strong>
              <span>Ambil order</span>
            </div>
            <div class="auth-role" data-role="mitra">
              <strong>Mitra</strong>
              <span>Kelola toko</span>
            </div>
            <div class="auth-role" data-role="admin">
              <strong>Admin</strong>
              <span>Kontrol sistem</span>
            </div>
          </div>

          <form class="auth-form" action="../auth_login.php" method="POST">
            <input type="hidden" name="role" id="role" value="<?php echo htmlspecialchars($role); ?>" />

            <div class="form-group">
              <label for="identifier" id="identifierLabel">Email</label>
              <input type="text" id="identifier" name="identifier" required />
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" id="password" name="password" required />
            </div>

            <div class="auth-actions">
              <button type="submit" class="auth-primary">Masuk</button>
            </div>
          </form>

          <?php if ($role === 'user'): ?>
            <div style="margin-top: 1rem; color: #6b7280; font-size: 0.95rem;">
              Belum punya akun?
              <a href="register.php" style="color: var(--primary); font-weight: 800;">Daftar</a>
              <span style="margin: 0 6px;">|</span>
              <a href="forgot.php" style="color: var(--primary); font-weight: 800;">Lupa password</a>
            </div>
          <?php else: ?>
            <div style="margin-top: 1rem; color: #6b7280; font-size: 0.95rem;">
              Akun role ini dibuat oleh Admin.
            </div>
          <?php endif; ?>
        </section>
      </div>
    </div>

    <script>
      feather.replace();

      const initialRole = <?php echo json_encode($role); ?>;
      const roleInput = document.getElementById('role');
      const roles = document.querySelectorAll('.auth-role');
      const label = document.getElementById('identifierLabel');
      const identifier = document.getElementById('identifier');

      function applyRole(role) {
        roleInput.value = role;
        roles.forEach((el) => el.classList.toggle('active', el.dataset.role === role));

        if (role === 'user') {
          label.textContent = 'Email';
          identifier.type = 'email';
          identifier.placeholder = 'nama@email.com';
        } else {
          label.textContent = 'Username';
          identifier.type = 'text';
          identifier.placeholder = 'username';
        }
      }

      roles.forEach((el) => {
        el.addEventListener('click', () => {
          applyRole(el.dataset.role);
        });
      });

      applyRole(initialRole);
    </script>

    <script src="../js/script.js?v=20251230"></script>
  </body>
</html>
