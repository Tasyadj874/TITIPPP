<?php
require "auth.php";
require '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) {
    header('Location: kategori.php');
    exit();
}

$stmt = mysqli_prepare($con, 'SELECT * FROM kategori WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
if (!$data) {
    header('Location: kategori.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    if ($nama === '') {
        $errors[] = 'Nama kategori tidak boleh kosong.';
    } else {
        $stmt = mysqli_prepare($con, 'SELECT COUNT(*) FROM kategori WHERE nama = ? AND id <> ?');
        mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $cnt);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($cnt > 0) {
            $errors[] = 'Nama kategori sudah digunakan.';
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($con, 'UPDATE kategori SET nama = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) {
            header('Location: kategori.php');
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
    <title>Edit Kategori</title>
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
            <li class="breadcrumb-item"><a href="kategori.php">Kategori</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Kategori</li>
        </ol>
    </nav>

    <div class="card shadow p-4">
        <h4 class="mb-3">Edit Kategori</h4>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" name="nama" required value="<?php echo htmlspecialchars($_POST['nama'] ?? $data['nama'] ?? ''); ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="kategori.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
