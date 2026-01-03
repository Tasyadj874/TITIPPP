<?php
session_start();
include "../config.php";

if (isset($_SESSION['login']) && $_SESSION['login'] === true && ($_SESSION['role'] ?? '') === 'user') {
    header("Location: ../index.php");
    exit;
}

$error = "";

if (isset($_POST['register'])) {
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($nama === '' || $email === '' || $password === '') {
        $error = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result && $result->fetch_assoc();
            $stmt->close();

            if ($exists) {
                $error = "Email sudah terdaftar!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';

                $stmt2 = $con->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                if (!$stmt2) {
                    $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
                } else {
                    $stmt2->bind_param("ssss", $nama, $email, $hashed_password, $role);
                    if ($stmt2->execute()) {
                        $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
                        $stmt2->close();
                        header("Location: login.php");
                        exit;
                    }
                    $stmt2->close();
                    $error = "Registrasi gagal, silakan coba lagi.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User | TITIP INDONESIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --titip-red: #d61f1f;
            --titip-red-dark: #b51616;
            --surface: #ffffff;
            --page: #f6f7fb;
            --text: #101828;
            --muted: #667085;
            --border: rgba(16, 24, 40, 0.10);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--page);
            min-height: 100vh;
            color: var(--text);
        }

        .login-shell {
            background:
                radial-gradient(900px 450px at 10% 0%, rgba(214, 31, 31, 0.14) 0%, rgba(214, 31, 31, 0) 60%),
                radial-gradient(900px 450px at 100% 20%, rgba(214, 31, 31, 0.10) 0%, rgba(214, 31, 31, 0) 60%),
                var(--page);
        }

        .login-container {
            background: var(--surface);
            border: 1px solid var(--border);
            box-shadow: 0 18px 50px rgba(16, 24, 40, 0.10);
        }

        .btn-primary {
            background: var(--titip-red);
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .btn-primary:hover {
            background: var(--titip-red-dark);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(214, 31, 31, 0.25);
        }
    </style>
</head>
<body>
    <div class="login-shell min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-5xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 items-stretch">
                <div class="hidden lg:flex flex-col justify-between rounded-2xl p-10" style="background: linear-gradient(180deg, rgba(214, 31, 31, 0.08) 0%, rgba(214, 31, 31, 0) 100%); border: 1px solid var(--border);">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background: var(--titip-red);">
                                <span class="text-white font-bold">TI</span>
                            </div>
                            <div>
                                <div class="font-bold" style="color: var(--text);">TITIP INDONESIA</div>
                                <div class="text-sm" style="color: var(--muted);">Buat akun User</div>
                            </div>
                        </div>

                        <div class="mt-10">
                            <h1 class="text-3xl font-bold leading-tight" style="color: var(--text);">Daftar akun dengan cepat</h1>
                            <p class="mt-3 text-base" style="color: var(--muted);">Pendaftaran ini khusus untuk role <strong>User</strong>.</p>
                        </div>
                    </div>

                    <div class="text-sm" style="color: var(--muted);">
                        <a href="../index.php" class="underline">Kembali ke Beranda</a>
                    </div>
                </div>

                <div class="login-container rounded-2xl p-7 sm:p-9">
                    <div class="flex items-center gap-3">
                        <img src="../images/LOGO TITIP.png" alt="Logo TITIP" class="w-12 h-12 object-contain" />
                        <div>
                            <div class="text-lg font-bold" style="color: var(--text);">Register User</div>
                            <div class="text-sm" style="color: var(--muted);">TITIP INDONESIA</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-2xl font-bold" style="color: var(--text);">Buat akun baru</h2>
                        <p class="mt-1" style="color: var(--muted);">Lengkapi data di bawah untuk mendaftar.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="mt-6 p-4 rounded-xl" style="background: rgba(239, 68, 68, 0.10); border: 1px solid rgba(239, 68, 68, 0.25); color: #991b1b;">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-6 space-y-5">
                        <div>
                            <label for="nama" class="block text-sm font-medium mb-2" style="color: var(--text);">Nama</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                placeholder="Nama lengkap"
                                required
                                class="w-full px-4 py-3 rounded-xl outline-none"
                                style="border: 1px solid var(--border);"
                            >
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium mb-2" style="color: var(--text);">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                required
                                class="w-full px-4 py-3 rounded-xl outline-none"
                                style="border: 1px solid var(--border);"
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium mb-2" style="color: var(--text);">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Buat password"
                                autocomplete="new-password"
                                required
                                class="w-full px-4 py-3 rounded-xl outline-none"
                                style="border: 1px solid var(--border);"
                            >
                        </div>

                        <button
                            type="submit"
                            name="register"
                            class="btn-primary w-full py-3 px-4 text-white font-semibold rounded-xl"
                        >
                            Daftar
                        </button>
                    </form>

                    <div class="mt-7 text-center">
                        <p class="text-sm" style="color: var(--muted);">
                            Sudah punya akun?
                            <a href="login.php" class="font-semibold" style="color: var(--titip-red);">Login</a>
                        </p>
                        <p class="lg:hidden text-xs mt-4">
                            <a href="../index.php" class="underline" style="color: var(--muted);">Kembali ke Beranda</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
