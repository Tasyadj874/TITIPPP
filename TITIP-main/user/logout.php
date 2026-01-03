<?php
session_start();
session_unset();
session_destroy();

$redirect = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';
$redirect = str_replace(["\r", "\n"], '', $redirect);

if ($redirect === '' || strpos($redirect, '://') !== false) {
    $redirect = '../index.php';
}

header('Location: ' . $redirect);
exit();
?>
