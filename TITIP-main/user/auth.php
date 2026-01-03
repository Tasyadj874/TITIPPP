<?php
session_start();

// Redirect ke halaman login user jika belum login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Pastikan role adalah user
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    // Jika bukan user, redirect ke login yang sesuai
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'mitra', 'pasukan'])) {
        header("Location: ../admin/login.php");
    } else {
        header("Location: login.php");
    }
    exit;
}

// Fungsi untuk mendapatkan data user yang sedang login
function getCurrentUser($conn) {
    if (!isset($_SESSION['id'])) return null;
    
    $id = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    
    return $user;
}
?>
