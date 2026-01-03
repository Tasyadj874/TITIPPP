<?php
// Edit Mitra
require "auth.php";
require '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) {
    header('Location: umkm.php');
    exit();
}

// Fetch existing data
$stmt = mysqli_prepare($con, 'SELECT * FROM umkm_profile WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$mitra = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$mitra) {
    header('Location: umkm.php');
    exit();
}


$kategoriRes = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama ASC");

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_usaha = trim($_POST['nama_usaha'] ?? '');
    $bidang_usaha = trim($_POST['bidang_usaha'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_telepon_wa = trim($_POST['no_telepon_wa'] ?? '');
    $social_media = trim($_POST['social_media'] ?? '');
    $nama_pemilik = trim($_POST['nama_pemilik'] ?? '');
    $no_telepon_pemilik = trim($_POST['no_telepon_pemilik'] ?? '');
    $email_pemilik = trim($_POST['email_pemilik'] ?? '');

    if ($nama_usaha === '' || $bidang_usaha === '' || $alamat === '') {
        $errors[] = 'Nama usaha, bidang usaha, dan alamat wajib diisi';
    }

    // Handle logo upload (optional)
    $newLogoFileName = '';
    if (!empty($_FILES['logo']['name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $safeName = 'logo_' . uniqid() . '.' . strtolower($ext);
        $targetRel = '../uploads/logo/' . $safeName; // relative from admin
        $targetFs = __DIR__ . '/../uploads/logo/' . $safeName; // filesystem path
        if (!is_dir(__DIR__ . '/../uploads/logo')) {
            @mkdir(__DIR__ . '/../uploads/logo', 0777, true);
        }
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFs)) {
            $newLogoFileName = $safeName; // store only filename for consistency
        } else {
            $errors[] = 'Gagal mengunggah logo.';
        }
    }

    if (empty($errors)) {
        if ($newLogoFileName !== '') {
            $sql = 'UPDATE umkm_profile SET nama_usaha=?, bidang_usaha=?, alamat=?, no_telepon_wa=?, social_media=?, nama_pemilik=?, no_telepon_pemilik=?, email_pemilik=?, logo=? WHERE id=?';
        } else {
            $sql = 'UPDATE umkm_profile SET nama_usaha=?, bidang_usaha=?, alamat=?, no_telepon_wa=?, social_media=?, nama_pemilik=?, no_telepon_pemilik=?, email_pemilik=? WHERE id=?';
        }
        $stmt = mysqli_prepare($con, $sql);
        if ($newLogoFileName !== '') {
            mysqli_stmt_bind_param($stmt, 'sssssssssi', $nama_usaha, $bidang_usaha, $alamat, $no_telepon_wa, $social_media, $nama_pemilik, $no_telepon_pemilik, $email_pemilik, $newLogoFileName, $id);
        } else {
            mysqli_stmt_bind_param($stmt, 'ssssssssi', $nama_usaha, $bidang_usaha, $alamat, $no_telepon_wa, $social_media, $nama_pemilik, $no_telepon_pemilik, $email_pemilik, $id);
        }
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($success) {
            header('Location: umkm.php');
            exit();
        } else {
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mitra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require 'navbar.php'; ?>
<div class="dashboard-wrapper">
<?php require 'sidebar.php'; ?>
<div class="main-content">
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="umkm.php">UMKM</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Mitra</li>
        </ol>
    </nav>

    <div class="card shadow p-4">
        <h4 class="mb-3">Edit Mitra UMKM</h4>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Usaha</label>
                    <input type="text" class="form-control" name="nama_usaha" required value="<?php echo htmlspecialchars($mitra['nama_usaha'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bidang Usaha</label>
                    <select name="bidang_usaha" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php 
                        if ($kategoriRes) {
                            @mysqli_data_seek($kategoriRes, 0);
                            while ($kat = mysqli_fetch_assoc($kategoriRes)) { 
                                $namaKat = $kat['nama'] ?? '';
                                $sel = ($namaKat === ($mitra['bidang_usaha'] ?? '')) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($namaKat) . '"' . $sel . '>' . htmlspecialchars($namaKat) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <input type="text" class="form-control" name="alamat" required value="<?php echo htmlspecialchars($mitra['alamat'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No Telepon/WA</label>
                    <input type="text" class="form-control" name="no_telepon_wa" value="<?php echo htmlspecialchars($mitra['no_telepon_wa'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sosial Media</label>
                    <input type="text" class="form-control" name="social_media" value="<?php echo htmlspecialchars($mitra['social_media'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Pemilik</label>
                    <input type="text" class="form-control" name="nama_pemilik" value="<?php echo htmlspecialchars($mitra['nama_pemilik'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No Telp Pemilik/WA</label>
                    <input type="text" class="form-control" name="no_telepon_pemilik" value="<?php echo htmlspecialchars($mitra['no_telepon_pemilik'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Pemilik</label>
                    <input type="email" class="form-control" name="email_pemilik" value="<?php echo htmlspecialchars($mitra['email_pemilik'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo (opsional)</label>
                    <input type="file" class="form-control" name="logo" accept="image/*">
                    <?php 
                        $rawLogo = $mitra['logo'] ?? ''; 
                        if ($rawLogo) {
                            if (strpos($rawLogo, 'uploads') !== false || strpos($rawLogo, '/') !== false) {
                                $logoPrev = '../' . ltrim($rawLogo, '/');
                            } else {
                                $logoPrev = '../uploads/logo/' . $rawLogo;
                            }
                            echo '<div class="mt-2"><img src="' . htmlspecialchars($logoPrev) . '" alt="Logo" style="height:60px;object-fit:cover;border:1px solid #eee;border-radius:.25rem"></div>';
                        }
                    ?>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="umkm.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
