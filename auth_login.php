<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

auth_start();

$role = isset($_POST['role']) ? trim((string)$_POST['role']) : '';

$redirectLogin = 'auth/login.php';
if ($role === 'admin' || $role === 'mitra' || $role === 'kurir' || $role === 'user') {
    $redirectLogin = 'auth/login.php?role=' . urlencode($role);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectLogin);
    exit();
}
$identifier = isset($_POST['identifier']) ? trim((string)$_POST['identifier']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($role === '' || $identifier === '' || $password === '') {
    header('Location: ' . $redirectLogin . '?error=1');
    exit();
}

$conn = db();

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT id, username, nama, password_hash, status_aktif FROM admins WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $identifier);
} elseif ($role === 'mitra') {
    $stmt = $conn->prepare('SELECT id, username, mitra_id, password_hash, status_aktif FROM mitra_accounts WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $identifier);
} elseif ($role === 'kurir') {
    $stmt = $conn->prepare('SELECT id, username, driver_id, password_hash, status_aktif FROM kurir_accounts WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $identifier);
} elseif ($role === 'user') {
    $stmt = $conn->prepare('SELECT id, email, nama, password_hash, status_aktif FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $identifier);
} else {
    header('Location: ' . $redirectLogin . '?error=1');
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    header('Location: ' . $redirectLogin . '?error=1');
    exit();
}

$statusAktif = (int)($row['status_aktif'] ?? 0);
if ($statusAktif !== 1) {
    header('Location: ' . $redirectLogin . '?error=1');
    exit();
}

$storedPassword = (string)($row['password_hash'] ?? '');
if ($storedPassword === '' || !hash_equals($storedPassword, $password)) {
    header('Location: ' . $redirectLogin . '?error=1');
    exit();
}

$id = (int)$row['id'];

auth_login($role, $id, $row);

if ($role === 'admin') {
    $stmt = $conn->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} elseif ($role === 'mitra') {
    $stmt = $conn->prepare('UPDATE mitra_accounts SET last_login_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} elseif ($role === 'kurir') {
    $stmt = $conn->prepare('UPDATE kurir_accounts SET last_login_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} elseif ($role === 'user') {
    $stmt = $conn->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

auth_redirect_home();
