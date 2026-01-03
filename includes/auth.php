<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function auth_normalize_role(string $role): string
{
    $role = strtolower(trim($role));
    $allowed = ['user', 'admin', 'mitra', 'kurir'];
    return in_array($role, $allowed, true) ? $role : 'user';
}

function auth_detect_context_role(): string
{
    $role = '';
    if (isset($_POST['role'])) {
        $role = (string)$_POST['role'];
    } elseif (isset($_GET['role'])) {
        $role = (string)$_GET['role'];
    }
    if ($role !== '') {
        return auth_normalize_role($role);
    }

    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    if ($script !== '') {
        $script = str_replace('\\', '/', $script);

        if (preg_match('~/(admin/|laman-admin\.php|login-admin\.php|admin-[^/]+\.php)~', $script)) {
            return 'admin';
        }

        if (preg_match('~/(mitra/|laman-mitra\\.php|login-mitra\\.php|mitra-toko\\.php|unggah-produk-mitra\\.php|laman-profil-mitra\\.php)~', $script)) {
            return 'mitra';
        }

        if (preg_match('~/(kurir/|laman-kurir\\.php|login-kurir\\.php)~', $script)) {
            return 'kurir';
        }
    }

    return 'user';
}

function app_base_path(): string
{
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    if ($script === '') {
        return '';
    }

    if (preg_match('~/(auth|admin|mitra|kurir|user)/~', $script, $m, PREG_OFFSET_CAPTURE)) {
        $pos = (int)$m[0][1];
        return substr($script, 0, $pos);
    }

    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return $dir === '/' ? '' : $dir;
}

function auth_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $role = auth_detect_context_role();
        session_name('TITIP2_' . $role);
        session_start();
    }
}

function auth_login(string $role, int $id, array $data = []): void
{
    auth_start();

    session_regenerate_id(true);

    $_SESSION['auth'] = [
        'role' => $role,
        'id' => $id,
        'data' => $data,
    ];
}

function auth_logout(): void
{
    auth_start();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        if ($params['path'] !== '/') {
            setcookie(session_name(), '', time() - 42000, '/', $params['domain'], $params['secure'], $params['httponly']);
        }
    }

    session_destroy();
}

function auth_check(): bool
{
    auth_start();
    return isset($_SESSION['auth']['role'], $_SESSION['auth']['id']);
}

function auth_role(): ?string
{
    auth_start();
    return $_SESSION['auth']['role'] ?? null;
}

function auth_id(): ?int
{
    auth_start();
    $id = $_SESSION['auth']['id'] ?? null;
    return is_int($id) ? $id : null;
}

function auth_display_name(): string
{
    auth_start();

    if (!auth_check()) {
        return '';
    }

    $role = auth_role();
    $data = $_SESSION['auth']['data'] ?? [];
    if (!is_array($data)) {
        $data = [];
    }

    if ($role === 'user') {
        $name = (string)($data['nama'] ?? '');
        if ($name !== '') {
            return $name;
        }
        return (string)($data['email'] ?? 'User');
    }

    if ($role === 'admin') {
        $name = (string)($data['nama'] ?? '');
        if ($name !== '') {
            return $name;
        }
        return (string)($data['username'] ?? 'Admin');
    }

    return (string)($data['username'] ?? $role ?? '');
}

function auth_redirect_home(): void
{
    $base = app_base_path();
    $role = auth_role();

    if ($role === 'admin') {
        header('Location: ' . $base . '/admin/index.php');
        exit();
    }

    if ($role === 'mitra') {
        header('Location: ' . $base . '/mitra/index.php');
        exit();
    }

    if ($role === 'kurir') {
        header('Location: ' . $base . '/kurir/index.php');
        exit();
    }

    if ($role === 'user') {
        header('Location: ' . $base . '/user/index.php');
        exit();
    }

    header('Location: ' . $base . '/index.php');
    exit();
}

function require_login(): void
{
    if (!auth_check()) {
        $base = app_base_path();
        $role = auth_detect_context_role();
        header('Location: ' . $base . '/auth/login.php?role=' . urlencode($role));
        exit();
    }

    $role = auth_role();
    $id = auth_id();
    if ($role === null || $id === null || $id <= 0) {
        auth_logout();
        $base = app_base_path();
        header('Location: ' . $base . '/auth/login.php?role=user');
        exit();
    }

    $conn = db();
    $active = 0;
    if ($role === 'admin') {
        $stmt = $conn->prepare('SELECT status_aktif FROM admins WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
    } elseif ($role === 'mitra') {
        $stmt = $conn->prepare('SELECT status_aktif FROM mitra_accounts WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
    } elseif ($role === 'kurir') {
        $stmt = $conn->prepare('SELECT status_aktif FROM kurir_accounts WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
    } else {
        $stmt = $conn->prepare('SELECT status_aktif FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    $active = (int)($row['status_aktif'] ?? 0);

    if ($active !== 1) {
        auth_logout();
        $base = app_base_path();
        header('Location: ' . $base . '/auth/login.php?role=' . urlencode($role));
        exit();
    }
}

function require_role(array $roles): void
{
    require_login();

    $role = auth_role();
    if ($role === null || !in_array($role, $roles, true)) {
        auth_redirect_home();
    }
}
