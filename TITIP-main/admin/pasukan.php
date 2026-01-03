<?php
// pasukan.php
require "auth.php";
checkRole(['admin']);
require "../config.php";

if (isset($_POST['set_status'])) {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $new_status = (string) ($_POST['status_pendaftaran'] ?? '');

    if ($id > 0 && in_array($new_status, ['pending', 'approved', 'rejected'], true)) {
        $stmt_update = $con->prepare("UPDATE daftar_pasukan SET status_pendaftaran = ? WHERE id = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("si", $new_status, $id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }

    header("Location: pasukan.php");
    exit;
}

// Ambil data pendaftar Jastip dan gabungkan dengan data kendaraan
// Asumsi: 1 pendaftar hanya punya 1 entry kendaraan
$queryPasukan = mysqli_query($con, "SELECT dp.*, kp.merk, kp.plat FROM daftar_pasukan dp LEFT JOIN kendaraan_pasukan kp ON dp.id = kp.id_pendaftar ORDER BY dp.tanggal_daftar DESC");
$jumlahPasukan = mysqli_num_rows($queryPasukan);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Pasukan Jastip</title>
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
                        <i class="fas fa-motorcycle me-2"></i> Manajemen Pasukan Jastip
                    </li>
                </ol>
            </nav>
            
            <h2 class="h4">Daftar Pendaftar Jastip (<?php echo $jumlahPasukan; ?>)</h2>
            <div class="table-responsive mt-3 card shadow p-3">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>No. WA</th>
                            <th>Plat Kendaraan</th>
                            <th>Status</th>
                            <th>Tgl Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($jumlahPasukan == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada Pendaftar Jastip.</td>
                            </tr>
                        <?php else: 
                            $number = 1;
                            while ($data = mysqli_fetch_array($queryPasukan)): 
                                $badge_class = ($data['status_pendaftaran'] == 'approved') ? 'bg-success' : (($data['status_pendaftaran'] == 'rejected') ? 'bg-danger' : 'bg-warning');
                            ?>
                                <tr>
                                    <td><?php echo $number++; ?></td>
                                    <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($data['no_telepon_wa']); ?></td>
                                    <td><?php echo htmlspecialchars($data['plat']); ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($data['status_pendaftaran']); ?></span></td>
                                    <td><?php echo date('d-M-Y', strtotime($data['tanggal_daftar'])); ?></td>
                                    <td>
                                        <a href="pasukan-detail.php?id=<?php echo $data['id']?>" class="btn btn-info btn-sm"><i class="fas fa-search"></i> Detail</a>
                                        <?php if (($data['status_pendaftaran'] ?? 'pending') === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo (int) $data['id']; ?>">
                                                <input type="hidden" name="status_pendaftaran" value="approved">
                                                <button type="submit" name="set_status" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo (int) $data['id']; ?>">
                                                <input type="hidden" name="status_pendaftaran" value="rejected">
                                                <button type="submit" name="set_status" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; 
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>