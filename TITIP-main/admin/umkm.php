<?php
// umkm.php
require "auth.php";
require "../config.php";

// Ambil semua data UMKM
$queryUMKM = mysqli_query($con, "SELECT * FROM umkm_profile ORDER BY created_at DESC");
$jumlahUMKM = mysqli_num_rows($queryUMKM);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Manajemen UMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
<?php require "navbar.php"; ?>

<div class="dashboard-wrapper">
    <?php require "sidebar.php"; ?>

    <div class="main-content">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-store me-2"></i> Manajemen UMKM
                    </li>
                </ol>
            </nav>
            
            <h2 class="h4">Daftar Mitra UMKM (<?php echo $jumlahUMKM; ?>)</h2>
            <div class="table-responsive mt-3 card shadow p-3">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Usaha</th>
                            <th>Bidang Usaha</th>
                            <th>Alamat</th>
                            <th>No Telepon / WhatsApp</th>
                            <th>Sosial Media</th>
                            <th>Logo</th>
                            <th>Nama Pemilik</th>
                            <th>No Telp Pemilik / WhatsApp</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($jumlahUMKM == 0) { ?>
                            <tr>
                                <td colspan="12" class="text-center">Belum ada mitra UMKM yang terdaftar.</td>
                            </tr>
                        <?php 
                        } else  
                            $no = 1;
                            while ($data = mysqli_fetch_assoc($queryUMKM)) { 
                            $nama_usaha = htmlspecialchars($data['nama_usaha'] ?? '-');
                            $bidang_usaha = htmlspecialchars($data['bidang_usaha'] ?? '-');
                            $alamat = htmlspecialchars($data['alamat'] ?? '-');
                            $telepon = htmlspecialchars($data['no_telepon_wa'] ?? '-');
                            $sosial_media = htmlspecialchars($data['social_media'] ?? '-');
                            $logo = $data['logo'] ?? '';
                            $nama_pemilik = htmlspecialchars($data['nama_pemilik'] ?? '-');
                            $telepon_pemilik = htmlspecialchars($data['no_telepon_pemilik'] ?? '-');
                            $email = htmlspecialchars($data['email_pemilik'] ?? '-');
                            $tanggal_daftar = !empty($data['created_at']) ? date('d-M-Y', strtotime($data['created_at'])) : '-';
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= $nama_usaha; ?></td>
                                <td><?= $bidang_usaha; ?></td>
                                <td><?= $alamat; ?></td>
                                <td><?= $telepon; ?></td>
                                <td><?= $sosial_media; ?></td>
                                <td class="text-center">
                                    <?php if (!empty($logo)) { 
                                        $raw = $logo; 
                                        if (strpos($raw, 'uploads') !== false || strpos($raw, '/') !== false) {
                                            $logoPath = (strpos($raw, '../') === 0) ? $raw : '../' . ltrim($raw, '/');
                                        } else {
                                            $logoPath = '../uploads/logo/' . $raw;
                                        }
                                    ?>
                                        <img src="<?= htmlspecialchars($logoPath); ?>" alt="Logo" width="60" height="60" style="object-fit:cover;">
                                    <?php } else { ?>
                                        <span>-</span>
                                    <?php } ?>
                                </td>
                                <td><?= $nama_pemilik; ?></td>
                                <td><?= $telepon_pemilik; ?></td>
                                <td><?= $email; ?></td>
                                <td><?= $tanggal_daftar; ?></td>
                                <td class="text-center">
                                    <a href="../mitra-detail.php?id=<?= $data['id']; ?>" target="_blank" class="btn btn-primary btn-sm">Lihat</a>
                                    <a href="edit-mitra.php?id=<?= $data['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="hapus-mitra.php?id=<?= $data['id']; ?>" onclick="return confirm('Yakin hapus mitra ini?')" class="btn btn-danger btn-sm">Hapus</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
