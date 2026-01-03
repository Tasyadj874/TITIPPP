<?php 
// File ini mengasumsikan bahwa Anda sudah memiliki folder 'uploads/logo/'
// Pastikan folder tersebut memiliki izin tulis.

require 'config.php'; // menyediakan $con

// --- Bagian Pengambilan Data dari Form ---
// Menggunakan htmlspecialchars() untuk membersihkan input dari XSS (Cross-Site Scripting)
$nama_usaha = htmlspecialchars($_POST['nama-usaha'] ?? '');
$bidang_usaha = htmlspecialchars($_POST['bidang-usaha'] ?? '');
$alamat = htmlspecialchars($_POST['alamat'] ?? '');
$telepon = htmlspecialchars($_POST['telepon'] ?? '');
$sosial_media = htmlspecialchars($_POST['sosial-media'] ?? '');

$nama_pemilik = htmlspecialchars($_POST['nama-pemilik'] ?? '');
// Saya berasumsi 'whatsapp' dan 'telepon' merujuk ke input yang sama atau berbeda di formulir
$whatsapp = htmlspecialchars($_POST['whatsapp'] ?? ''); 
$email = htmlspecialchars($_POST['email'] ?? '');

// Validasi kategori harus ada di tabel kategori
$stmt = $con->prepare('SELECT COUNT(*) AS cnt FROM kategori WHERE nama = ?');
$stmt->bind_param('s', $bidang_usaha);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();
if ((int)($row['cnt'] ?? 0) < 1) {
    header('Location: gabung-umkm.php?status=invalid_category');
    exit();
}

// --- Bagian Menyimpan File Logo ---
$target_dir = __DIR__ . "/uploads/logo/";
// Pastikan folder 'uploads/logo/' sudah ada dan memiliki izin tulis (write permission)!
if (!is_dir($target_dir)) {
    @mkdir($target_dir, 0777, true);
}

// Generate nama file unik untuk mencegah konflik nama
$file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
$unique_filename = uniqid('logo_', true) . '.' . $file_extension;
$target_file = $target_dir . $unique_filename;
$upload_success = 0; // Flag untuk status upload

// Pengecekan dan proses upload
if (!empty($_FILES['logo']['name'])) {
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
        $logo_filename = $unique_filename; // Simpan hanya nama file ke DB
        $upload_success = 1;
    } else {
        header('Location: gabung-umkm.php?status=error_upload');
        exit();
    }
} else {
    $logo_filename = NULL; // Jika logo tidak diupload, masukkan NULL ke DB
    $upload_success = 1;
}

// --- Bagian SQL (Prepared Statement untuk Keamanan) ---

// Ganti nama tabel 'mitra' ke 'umkm_profile' dan sesuaikan kolomnya.
$sql = "INSERT INTO umkm_profile (
            nama_usaha, bidang_usaha, alamat, no_telepon_wa, social_media, 
            nama_pemilik, no_telepon_pemilik, email_pemilik, logo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

// 1. Persiapkan statement
$stmt = $con->prepare($sql);

if ($stmt === FALSE) {
    die("Error preparing statement: " . $con->error);
}

// 2. Ikat (Bind) parameter ke statement
// 'sssssssss' berarti sembilan parameter string yang akan dimasukkan
$stmt->bind_param(
    "sssssssss", 
    $nama_usaha, 
    $bidang_usaha, 
    $alamat, 
    $telepon, // Menggunakan $telepon untuk no_telepon_wa
    $sosial_media, 
    $nama_pemilik, 
    $whatsapp, // Menggunakan $whatsapp untuk no_telepon_pemilik (asumsi)
    $email, 
    $logo_filename // Simpan hanya nama file logo
);

// 3. Eksekusi statement dan cek hasil
if ($upload_success && $stmt->execute()) {
    // Redirect ke form dengan pesan sukses
    header("Location: gabung-umkm.php?status=success");
    exit();
} else {
    header('Location: gabung-umkm.php?status=error_save');
    exit();
}

// Tutup statement dan koneksi
$stmt->close();
$con->close();
?>