<?php
// pasukan-detail.php

require "auth.php";
checkRole(['admin']);
require "../config.php";

// Pastikan konstanta enkripsi didefinisikan (gunakan kunci yang sama dengan saat submit!)
define('ENCRYPTION_KEY', 'KunciRahasiaAndaDenganPanjang32Karakter'); 
define('CIPHER', 'aes-256-cbc');

// ==========================================================
// FUNGSI DEKRIPSI
// ==========================================================

function decryptData($data) {
    // Memecah data Base64 menjadi data terenkripsi dan IV
    $parts = explode('::', base64_decode($data), 2);
    if (count($parts) < 2) {
        return "ERROR: Format data tidak valid.";
    }
    
    $encrypted_data = $parts[0];
    $iv = $parts[1];
    
    // Melakukan dekripsi
    $decrypted = openssl_decrypt($encrypted_data, CIPHER, ENCRYPTION_KEY, 0, $iv);
    
    if ($decrypted === false) {
        return "ERROR: Gagal mendekripsi. Cek Kunci Enkripsi.";
    }
    return $decrypted;
}

// ==========================================================
// LOGIKA UTAMA
// ==========================================================

$id_pendaftar = $_GET['id'] ?? 0;

if ($id_pendaftar == 0) {
    echo '<div class="alert alert-danger">ID Pendaftar tidak ditemukan.</div>';
    exit;
}

// Mengambil data pendaftar Jastip dan data kendaraan
$stmt_data = $con->prepare("SELECT dp.*, kp.merk, kp.plat, kp.foto_kendaraan_path, kp.stnk_encrypted 
                           FROM daftar_pasukan dp 
                           LEFT JOIN kendaraan_pasukan kp ON dp.id = kp.id_pendaftar 
                           WHERE dp.id = ?");
$stmt_data->bind_param("i", $id_pendaftar);
$stmt_data->execute();
$result = $stmt_data->get_result();
$data = $result->fetch_assoc();
$stmt_data->close();

if (!$data) {
    echo '<div class="alert alert-danger">Data pendaftar tidak ditemukan di database.</div>';
    exit;
}

// --- LOGIKA UBAH STATUS (APPROVAL) ---
if (isset($_POST['ubah_status'])) {
    $new_status = (string) ($_POST['status_pendaftaran'] ?? '');

    // Periksa status yang valid
    if (in_array($new_status, ['pending', 'approved', 'rejected'], true)) {
        $stmt_update = $con->prepare("UPDATE daftar_pasukan SET status_pendaftaran = ? WHERE id = ?");
        $stmt_update->bind_param("si", $new_status, $id_pendaftar);
        
        if ($stmt_update->execute()) {
            $status_msg = "Status pendaftar berhasil diubah menjadi **" . strtoupper($new_status) . "**.";
            $data['status_pendaftaran'] = $new_status;
            echo '<meta http-equiv="refresh" content="2; url=pasukan-detail.php?id='.$id_pendaftar.'"/>';
        } else {
            $status_msg = "Gagal mengubah status: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $status_msg = "Status yang dimasukkan tidak valid.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftar | <?php echo htmlspecialchars($data['nama']); ?></title>
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
                        <li class="breadcrumb-item"><a href="pasukan.php">Manajemen Pasukan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
                    </ol>
                </nav>
                
                <h1 class="h3 mb-4">Detail Pendaftar: <?php echo htmlspecialchars($data['nama']); ?></h1>
                
                <?php if (isset($status_msg)): ?>
                    <div class="alert alert-info mt-3"><?php echo $status_msg; ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i> Data Pribadi</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Status Pendaftaran:</strong> 
                                    <?php 
                                        $badge_class = ($data['status_pendaftaran'] == 'approved') ? 'bg-success' : (($data['status_pendaftaran'] == 'rejected') ? 'bg-danger' : 'bg-warning');
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($data['status_pendaftaran']); ?></span>
                                </p>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($data['email']); ?></li>
                                    <li class="list-group-item"><strong>No. WA:</strong> <?php echo htmlspecialchars($data['no_telepon_wa']); ?></li>
                                    <li class="list-group-item"><strong>Alamat:</strong> <?php echo htmlspecialchars($data['alamat']); ?></li>
                                    <li class="list-group-item"><strong>Tgl Lahir:</strong> <?php echo htmlspecialchars($data['tanggal_lahir']); ?></li>
                                </ul>
                                
                                <h6 class="mt-4">Update Status Pendaftaran</h6>
                                <form method="POST">
                                    <select name="status_pendaftaran" class="form-select mb-2">
                                        <option value="pending" <?php echo ($data['status_pendaftaran'] == 'pending') ? 'selected' : ''; ?>>Pending (Menunggu Review)</option>
                                        <option value="approved" <?php echo ($data['status_pendaftaran'] == 'approved') ? 'selected' : ''; ?>>Approved (Disetujui)</option>
                                        <option value="rejected" <?php echo ($data['status_pendaftaran'] == 'rejected') ? 'selected' : ''; ?>>Rejected (Ditolak)</option>
                                    </select>
                                    <button type="submit" name="ubah_status" class="btn btn-warning w-100">
                                        <i class="fas fa-sync-alt me-1"></i> Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-car me-2"></i> Data Kendaraan</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Merek:</strong> <?php echo htmlspecialchars($data['merk'] ?? 'N/A'); ?></li>
                                    <li class="list-group-item"><strong>Plat No.:</strong> <?php echo htmlspecialchars($data['plat'] ?? 'N/A'); ?></li>
                                </ul>
                                <h6 class="mt-4">Foto Kendaraan:</h6>
                                <img src="<?php echo '../uploads/' . htmlspecialchars($data['foto_kendaraan_path'] ?? ''); ?>" 
                                     alt="Foto Kendaraan" class="img-fluid rounded border p-1" style="max-height: 200px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-lock me-2"></i> SIM C (Terenkripsi)</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="text-danger">Konten File (Teks hasil dekripsi):</h6>
                                <textarea class="form-control" rows="10" readonly><?php 
                                    // DEKRIPSI DAN TAMPILKAN
                                    echo htmlspecialchars(decryptData($data['file_sim_c']));
                                ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-lock me-2"></i> STNK (Terenkripsi)</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="text-danger">Konten File (Teks hasil dekripsi):</h6>
                                <textarea class="form-control" rows="10" readonly><?php 
                                    // DEKRIPSI DAN TAMPILKAN
                                    echo htmlspecialchars(decryptData($data['stnk_encrypted'] ?? ''));
                                ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>