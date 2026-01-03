<?php
// dashboard.php

include "auth.php";
checkRole(['admin']);

require "../config.php"; 

// --- MENGAMBIL DATA STATISTIK DARI DATABASE ---
$query_umkm = mysqli_query($con, "SELECT COUNT(id) AS total FROM umkm_profile");
$data_umkm = mysqli_fetch_assoc($query_umkm);
$total_umkm = $data_umkm['total'] ?? 0;

$query_drivers = mysqli_query($con, "SELECT COUNT(id) AS total FROM daftar_pasukan WHERE status_pendaftaran = 'approved'");
$data_drivers = mysqli_fetch_assoc($query_drivers);
$total_drivers = $data_drivers['total'] ?? 0;

$query_pending = mysqli_query($con, "SELECT COUNT(id) AS total FROM daftar_pasukan WHERE status_pendaftaran = 'pending'");
$data_pending = mysqli_fetch_assoc($query_pending);
$pending_apps = $data_pending['total'] ?? 0;

// --- MENGAMBIL DATA AKTIVITAS TERBARU ---
$query_activity = mysqli_query($con, "SELECT id, nama, tanggal_daftar, status_pendaftaran FROM daftar_pasukan ORDER BY tanggal_daftar DESC LIMIT 5");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | TITIP Control</title>
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
            
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Utama</h1>
            </div>
            <div class="row">
                
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="stats-card bg-primary-stats">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">TOTAL MITRA UMKM</div>
                                <p class="mb-0"><?php echo number_format($total_umkm); ?></p>
                            </div>
                            <div class="col-auto"><i class="fas fa-store card-icon"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="stats-card bg-success-stats">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">DRIVER PASUKAN AKTIF</div>
                                <p class="mb-0"><?php echo number_format($total_drivers); ?></p>
                            </div>
                            <div class="col-auto"><i class="fas fa-motorcycle card-icon"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="stats-card bg-danger-stats">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">APLIKASI BARU TERTUNDA</div>
                                <p class="mb-0"><?php echo number_format($pending_apps); ?></p>
                            </div>
                            <div class="col-auto"><i class="fas fa-clock card-icon"></i></div>
                        </div>
                    </div>
                </div>
            </div> <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Aktivitas Pendaftar Terbaru</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nama Pendaftar</th>
                                            <th>Tanggal Daftar</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($query_activity) == 0): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada pendaftar baru-baru ini.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php while ($activity = mysqli_fetch_assoc($query_activity)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($activity['nama']); ?></td>
                                                    <td><?php echo date('d M Y H:i', strtotime($activity['tanggal_daftar'])); ?></td>
                                                    <td>
                                                        <?php
                                                            $badge_class = 'bg-secondary';
                                                            if ($activity['status_pendaftaran'] == 'approved') {
                                                                $badge_class = 'bg-success';
                                                            } elseif ($activity['status_pendaftaran'] == 'pending') {
                                                                $badge_class = 'bg-warning text-dark';
                                                            } elseif ($activity['status_pendaftaran'] == 'rejected') {
                                                                $badge_class = 'bg-danger';
                                                            }
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($activity['status_pendaftaran']); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="pasukan-detail.php?id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-info">Review</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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