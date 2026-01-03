<?php
session_start();

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Fungsi untuk mengecek role user
function checkRole($allowed_roles = []) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../index.php");
        exit;
    }
}

// Fungsi untuk mendapatkan data user yang sedang login
function getCurrentUser($conn) {
    if (!isset($_SESSION['id'])) return null;
    
    $id = $_SESSION['id'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    return mysqli_fetch_assoc($query);
}
?>