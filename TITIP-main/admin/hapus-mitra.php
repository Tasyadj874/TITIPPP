<?php
// Hapus Mitra
require "auth.php";
require '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: umkm.php');
    exit();
}

// Ambil data untuk mengetahui path logo
$stmt = mysqli_prepare($con, 'SELECT logo FROM umkm_profile WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Hapus record
$stmt = mysqli_prepare($con, 'DELETE FROM umkm_profile WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok && $row && !empty($row['logo'])) {
    $raw = $row['logo'];
    $paths = [];
    if (strpos($raw, 'uploads') !== false || strpos($raw, '/') !== false) {
        $paths[] = __DIR__ . '/../' . ltrim($raw, '/');
    } else {
        $paths[] = __DIR__ . '/../uploads/logo/' . $raw;
        $paths[] = __DIR__ . '/../uploads/' . $raw;
    }
    foreach ($paths as $p) {
        if (is_file($p)) { @unlink($p); }
    }
}

header('Location: umkm.php');
exit();
