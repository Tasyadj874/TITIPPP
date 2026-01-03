<?php
// laporan.php
require "auth.php";
require "../config.php";

// Anda akan menambahkan logika filter dan export di sini
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Laporan</title>
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
                        <i class="fas fa-chart-bar me-2"></i> Laporan
                    </li>
                </ol>
            </nav>
            
            <h2 class="h4">Generasi Laporan Data</h2>
            <div class="card shadow p-4 mt-3">
                <p>Pilih jenis laporan yang ingin Anda buat dan klik Export.</p>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card p-3 bg-light">
                            <h5>Laporan UMKM Terdaftar</h5>
                            <p class="small text-muted">Mencakup semua detail UMKM (pemilik, kontak, alamat).</p>
                            <a href="export.php?type=umkm" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Export ke Excel</a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card p-3 bg-light">
                            <h5>Laporan Driver Disetujui</h5>
                            <p class="small text-muted">Mencakup driver dengan status 'approved', tidak termasuk dokumen rahasia.</p>
                            <a href="export.php?type=driver" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Export ke Excel</a>
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