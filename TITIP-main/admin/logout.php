<?php
session_start();
session_unset(); // Menghapus semua sesi
session_destroy(); // Mengakhiri sesi

$redirect = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';
$redirect = str_replace(["\r", "\n"], '', $redirect);

if ($redirect === '' || strpos($redirect, '://') !== false) {
    $redirect = 'login.php';
}

header('Location: ' . $redirect);
exit();
?>
