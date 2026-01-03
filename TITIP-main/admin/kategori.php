<?php 
require "auth.php";
require "../config.php";

// Tambah Kategori (Gunakan Prepared Statement untuk Keamanan)
if(isset($_POST['simpan_kategori'])){
    $kategori_baru = htmlspecialchars(trim($_POST['kategori']));

    if (empty($kategori_baru)) {
        $error_message = "Nama kategori tidak boleh kosong.";
    } else {
        // 1. Cek duplikasi
        $stmt_check = $con->prepare("SELECT COUNT(*) FROM kategori WHERE nama = ?");
        $stmt_check->bind_param("s", $kategori_baru);
        $stmt_check->execute();
        $stmt_check->bind_result($jumlahDataKategoriBaru);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($jumlahDataKategoriBaru > 0) {
            $error_message = "Kategori **$kategori_baru** sudah ada!";
        } else {
            // 2. Simpan Data
            $stmt_insert = $con->prepare("INSERT INTO kategori (nama) VALUES (?)");
            $stmt_insert->bind_param("s", $kategori_baru);

            if($stmt_insert->execute()){
                $success_message = "Yey!! Kategori berhasil ditambahkan!";
                // Redirect setelah sukses
                echo '<meta http-equiv="refresh" content="1; url=kategori.php"/>';
            } else {
                $error_message = "Gagal menyimpan: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
    }
}

// Ambil semua data kategori untuk ditampilkan
$queryKategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama ASC");
$jumlahKategori = mysqli_num_rows($queryKategori);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
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
                            <i class="fas fa-tags me-2"></i> Manajemen Kategori
                        </li>
                    </ol>
                </nav>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'in_use'): ?>
                    <div class="alert alert-warning" role="alert">
                        Kategori tidak dapat dihapus karena masih digunakan oleh UMKM.
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-12 col-md-6 mb-5">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Tambah Kategori Baru</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="mb-3">
                                        <label for="kategori" class="form-label">Nama Kategori</label>
                                        <input type="text" id="kategori" name="kategori" placeholder="Input nama kategori" class="form-control" required>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary" type="submit" name="simpan_kategori">
                                            <i class="fas fa-save me-1"></i> Simpan
                                        </button>
                                    </div>
                                </form>
                                
                                <?php if (isset($success_message)): ?>
                                    <div class="alert alert-success mt-3" role="alert"><?php echo $success_message; ?></div>
                                <?php endif; ?>
                                <?php if (isset($error_message)): ?>
                                    <div class="alert alert-danger mt-3" role="alert"><?php echo $error_message; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6">
                        <h2 class="h4">List Kategori (<?php echo $jumlahKategori; ?>)</h2>
                        <div class="table-responsive mt-3 card shadow">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Kategori</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($jumlahKategori == 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data Kategori</td>
                                        </tr>
                                    <?php else: 
                                        $number = 1;
                                        // Gunakan $queryKategori yang sudah di-fetch di atas
                                        // Karena kita sudah fetch sebelumnya, kita harus query ulang jika tidak mau pakai array sementara.
                                        // Untuk kemudahan, kita query ulang di sini:
                                        $queryKategoriDisplay = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama ASC");
                                        while ($data = mysqli_fetch_array($queryKategoriDisplay)): ?>
                                            <tr>
                                                <td><?php echo $number; ?></td>
                                                <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Aksi
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="edit-kategori.php?id=<?php echo $data['id']; ?>">Edit</a></li>
                                                            <li><a class="dropdown-item text-danger" href="hapus-kategori.php?id=<?php echo $data['id']; ?>" onclick="return confirm('Yakin hapus kategori ini?')">Hapus</a></li>
                                                            <li><a class="dropdown-item" href="kategori-detail.php?id=<?php echo $data['id']?>">Detail</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php $number++; ?> 
                                        <?php endwhile; 
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>