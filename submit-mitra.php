<?php
require_once __DIR__ . '/includes/db.php';

$conn = db();

// Mengambil data dari form
$nama_usaha = trim((string)($_POST['nama-usaha'] ?? ''));
$bidang_usaha = trim((string)($_POST['bidang-usaha'] ?? ''));
$alamat = trim((string)($_POST['alamat'] ?? ''));
$telepon = trim((string)($_POST['telepon'] ?? ''));
$sosial_media = trim((string)($_POST['sosial-media'] ?? ''));
$nama_pemilik = trim((string)($_POST['nama-pemilik'] ?? ''));
$whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

// Menyimpan file logo
$logoPath = '';
if (isset($_FILES['logo']) && is_array($_FILES['logo'])) {
    $tmp = (string)($_FILES['logo']['tmp_name'] ?? '');
    $orig = (string)($_FILES['logo']['name'] ?? '');
    $size = (int)($_FILES['logo']['size'] ?? 0);
    $err = (int)($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($err === UPLOAD_ERR_OK && $tmp !== '' && is_uploaded_file($tmp) && $size > 0) {
        $ext = strtolower((string)pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed, true) && $size <= 2_000_000) {
            $uploadDir = __DIR__ . '/uploads/mitra/logo';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            try {
                $rand = bin2hex(random_bytes(4));
            } catch (Throwable $e) {
                $rand = (string)mt_rand(1000, 9999);
            }

            $fileName = 'mitra_logo_' . time() . '_' . $rand . '.' . $ext;
            $dest = $uploadDir . '/' . $fileName;
            if (@move_uploaded_file($tmp, $dest)) {
                $logoPath = 'uploads/mitra/logo/' . $fileName;
            }
        }
    }
}

if ($logoPath === '') {
    header('Location: gabung-umkm.php?status=error_logo');
    exit();
}

// SQL untuk memasukkan data ke tabel mitra
$stmt = $conn->prepare('INSERT INTO mitra (pemilik, whatsapp, email, nama, bidang, notelepon, alamat, sosial_media, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('sssssssss', $nama_pemilik, $whatsapp, $email, $nama_usaha, $bidang_usaha, $telepon, $alamat, $sosial_media, $logoPath);

if ($stmt->execute()) {
    // Redirect ke form dengan pesan sukses
    $stmt->close();
    header("Location: gabung-umkm.php?status=success");
    exit();
} else {
    $stmt->close();
    echo "Error: " . $conn->error;
}
?>