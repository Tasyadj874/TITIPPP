<?php
require "auth.php";
require '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: kategori.php');
    exit();
}

// Optional: cek keberadaan data
$stmt = mysqli_prepare($con, 'SELECT id FROM kategori WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$exists = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$exists) {
    header('Location: kategori.php');
    exit();
}

// Cek apakah kategori masih digunakan oleh UMKM
$stmt = mysqli_prepare($con, 'SELECT nama FROM kategori WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$namaKategori = $row['nama'] ?? '';
if ($namaKategori !== '') {
    $stmt = mysqli_prepare($con, 'SELECT COUNT(*) AS cnt FROM umkm_profile WHERE bidang_usaha = ?');
    mysqli_stmt_bind_param($stmt, 's', $namaKategori);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $used = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if ((int)($used['cnt'] ?? 0) > 0) {
        header('Location: kategori.php?error=in_use');
        exit();
    }
}

// Hapus data kategori
$stmt = mysqli_prepare($con, 'DELETE FROM kategori WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: kategori.php');
exit();
