<?php
session_start();
include "../config.php";

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    $role = $_SESSION['role'] ?? 'user';

    if ($role === 'admin') {
        header("Location: dashboard.php");
        exit;
    }

    if ($role === 'mitra') {
        header("Location: ../mitra-titip.php");
        exit;
    }

    if ($role === 'pasukan' || $role === 'kurir') {
        header("Location: pasukan.php");
        exit;
    }

    header("Location: ../index.php");
    exit;
}

$error = "";
$success = "";
if (!empty($_SESSION['success'])) {
    $success = (string) $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_POST['login'])) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $error = "Email dan password wajib diisi.";
    } else {
        $stmt = $con->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $u = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($u && password_verify($pass, (string) $u['password'])) {
                session_regenerate_id(true);

                $role = (string) ($u['role'] ?? 'user');
                if ($role === 'kurir') {
                    $role = 'pasukan';
                }

                // Block user role from admin login
                if ($role === 'user') {
                    $error = "Gunakan halaman login user";
                } else {
                    $_SESSION['login'] = true;
                    $_SESSION['id'] = (int) $u['id'];
                    $_SESSION['nama'] = (string) $u['nama'];
                    $_SESSION['role'] = $role;

                    if ($role === 'admin') {
                        header("Location: dashboard.php");
                        exit;
                    }

                    if ($role === 'mitra') {
                        header("Location: ../mitra-titip.php");
                        exit;
                    }

                    if ($role === 'pasukan') {
                        header("Location: pasukan.php");
                        exit;
                    }

                    header("Location: ../index.php");
                    exit;
                }
            }

            $error = "Email atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login | TITIP INDONESIA</title>
  <link rel="stylesheet" href="../css/login.css">
</head>
<body>

<div class="login-box">
  <h3>Login TITIP INDONESIA</h3>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label class="field">
      <span>Email</span>
      <input type="email" name="email" placeholder="nama@email.com" autocomplete="email" required>
    </label>
    <label class="field">
      <span>Password</span>
      <input type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
    </label>
    <button type="submit" name="login">Masuk</button>
  </form>

  <div class="login-footer">
    <span>Belum punya akun?</span>
    <a href="register.php">Daftar</a>
    <br>
    <span>Login untuk User?</span>
    <a href="../user/login.php">Klik disini</a>
  </div>
</div>

</body>
</html>
