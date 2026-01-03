<?php
require_once __DIR__ . '/includes/db.php';

$conn = db();

function save_upload_file(array $file, string $subdir, array $allowedExt, int $maxBytes): string
{
    $tmp = (string)($file['tmp_name'] ?? '');
    $orig = (string)($file['name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($err !== UPLOAD_ERR_OK || $tmp === '' || !is_uploaded_file($tmp) || $size <= 0) {
        return '';
    }

    $ext = strtolower((string)pathinfo($orig, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true) || $size > $maxBytes) {
        return '';
    }

    $uploadDir = __DIR__ . '/uploads/' . trim($subdir, '/');
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    try {
        $rand = bin2hex(random_bytes(6));
    } catch (Throwable $e) {
        $rand = (string)mt_rand(100000, 999999);
    }

    $fileName = $rand . '_' . time() . '.' . $ext;
    $dest = $uploadDir . '/' . $fileName;
    if (!@move_uploaded_file($tmp, $dest)) {
        return '';
    }

    return 'uploads/' . trim($subdir, '/') . '/' . $fileName;
}

// Mengambil data dari form
$nama = trim((string)($_POST['nama'] ?? ''));
$tempat_lahir = trim((string)($_POST['tempat'] ?? ''));
$tanggal_lahir = trim((string)($_POST['tanggal_lahir'] ?? ''));
$jenis_kelamin = trim((string)($_POST['jenis_kelamin'] ?? ''));
$alamat = trim((string)($_POST['alamat'] ?? ''));
$telepon = trim((string)($_POST['telepon'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

$simcPath = isset($_FILES['sim_c']) && is_array($_FILES['sim_c'])
    ? save_upload_file($_FILES['sim_c'], 'kurir/simc', ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 2_000_000)
    : '';
$fotoDiriPath = isset($_FILES['foto_diri']) && is_array($_FILES['foto_diri'])
    ? save_upload_file($_FILES['foto_diri'], 'kurir/foto_diri', ['jpg', 'jpeg', 'png', 'webp'], 2_000_000)
    : '';

if ($simcPath === '' || $fotoDiriPath === '') {
    header('Location: gabung-pasukan.php?status=error_upload');
    exit();
}

$stmt = $conn->prepare('INSERT INTO registrasi_driver (nama, tgl_lahir, jenis_kelamin, alamat, notelpon, email, simc, foto_diri) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssssss', $nama, $tanggal_lahir, $jenis_kelamin, $alamat, $telepon, $email, $simcPath, $fotoDiriPath);

if ($stmt->execute()) {
    $stmt->close();
    // Ambil ID dari registrasi terakhir yang dimasukkan
    $rd = $conn->insert_id;

    // Mengambil data kendaraan dari form
    $merk_kendaraan = trim((string)($_POST['merk_kendaraan'] ?? ''));
    $plat_kendaraan = trim((string)($_POST['plat_kendaraan'] ?? ''));

    $fotoKendaraanPath = isset($_FILES['foto_kendaraan']) && is_array($_FILES['foto_kendaraan'])
        ? save_upload_file($_FILES['foto_kendaraan'], 'kurir/kendaraan', ['jpg', 'jpeg', 'png', 'webp'], 2_000_000)
        : '';
    $stnkPath = isset($_FILES['foto_stnk']) && is_array($_FILES['foto_stnk'])
        ? save_upload_file($_FILES['foto_stnk'], 'kurir/stnk', ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 2_000_000)
        : '';

    if ($fotoKendaraanPath === '' || $stnkPath === '') {
        header('Location: gabung-pasukan.php?status=error_upload');
        exit();
    }

    $stmt2 = $conn->prepare('INSERT INTO kendaraan_driver (rd, merk, plat, foto_kendaraan, stnk) VALUES (?, ?, ?, ?, ?)');
    $stmt2->bind_param('issss', $rd, $merk_kendaraan, $plat_kendaraan, $fotoKendaraanPath, $stnkPath);

    if ($stmt2->execute()) {
        $stmt2->close();
        header("Location: gabung-pasukan.php?status=success");
        exit();
    } else {
        $stmt2->close();
        header("Location: gabung-pasukan.php?status=error_kendaraan");
        exit();
    }

} else {
    $stmt->close();
    header("Location: gabung-pasukan.php?status=error_registrasi");
    exit();
}
?>