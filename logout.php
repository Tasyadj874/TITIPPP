<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

auth_start();
$role = auth_detect_context_role();

auth_logout();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$base = app_base_path();
if ($role === 'admin' || $role === 'mitra' || $role === 'kurir') {
    header('Location: ' . $base . '/auth/login.php?role=' . urlencode($role));
    exit();
}

header('Location: ' . $base . '/index.php');
exit();
