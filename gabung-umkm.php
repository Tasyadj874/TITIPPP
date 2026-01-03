<?php
require_once __DIR__ . '/includes/auth.php';
auth_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kurir TITIP</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap"
      rel="stylesheet"
    />

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- My Style-->
    <link rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <!-- Navbar Menu Start -->
    <div class="red-navbar">
      <nav class="navbar">
        <div class="logo-container">
          <img src="images/LOGO TITIP.png" alt="Logo TITIP" class="logo" />
        </div>
        <div class="navbar-nav">
          <a href="index.php#home">Home</a>
          <a href="index.php#layanan">Layanan</a>
          <a href="index.php#about">Tentang Kami</a>
          <a href="index.php#gabung-mitra" >Gabung Mitra </a>
          <a href="index.php#contact">Kontak</a>
        </div>
        
        <div class="navbar-extra">
            <?php if (auth_check()): ?>
              <a href="#login-options" id="login" style="display: inline-flex; align-items: center; gap: 10px;">
                <i data-feather="users"></i>
                <span style="font-size: 16px; font-weight: 800; color: #fff; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?php echo htmlspecialchars(auth_display_name()); ?>
                </span>
              </a>
            <?php else: ?>
              <a href="auth/login.php?role=user" id="login"><i data-feather="users"></i></a>
            <?php endif; ?>
            <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
            <div class="dropdown" id="login-options">
                <?php if (auth_check()): ?>
                  <?php if (auth_role() === 'admin'): ?>
                    <a href="laman-admin.php">Dashboard</a>
                  <?php elseif (auth_role() === 'mitra'): ?>
                    <a href="laman-mitra.php">Dashboard</a>
                  <?php elseif (auth_role() === 'kurir'): ?>
                    <a href="laman-kurir.php">Dashboard</a>
                  <?php elseif (auth_role() === 'user'): ?>
                    <a href="user/index.php">Dashboard</a>
                  <?php endif; ?>
                  <a href="logout.php?role=<?php echo urlencode((string)(auth_role() ?? 'user')); ?>">Logout</a>
                <?php else: ?>
                  <a href="auth/login.php?role=user">Login User</a>
                  <a href="auth/login.php?role=kurir">Login Kurir</a>
                  <a href="auth/login.php?role=mitra">Login Mitra</a>
                  <a href="auth/login.php?role=admin">Login Admin</a>
                <?php endif; ?>
            </div>
        </div>
      </nav>
    </div>

    <!-- Content -->
    <form action="submit-mitra.php" method="POST" enctype="multipart/form-data">
      <div class="container">
        <h1>DAFTAR MITRA UMKM</h1>

        <!-- Info UMKM -->
        <h2>Informasi UMKM</h2>
        <div class="form-group">
            <label>Nama Usaha:</label>
            <input type="text" name="nama-usaha" required />
        </div>
        <div class="form-group">
            <label>Bidang Usaha:</label>
            <input type="text" name="bidang-usaha" required />
        </div>
        <div class="form-group">
            <label>Alamat:</label>
            <input type="text" name="alamat" required />
        </div>
        <div class="form-group">
            <label>No Telepon/Whatsapp:</label>
            <input type="number" name="telepon" required />
        </div>
        <div class="form-group">
            <label>Sosial Media:</label>
            <input type="text" name="sosial-media" required />
        </div>
        <div class="form-group">
            <label>Logo</label>
            <input type="file" name="logo" required />
        </div>

        <!-- Info Pemilik -->
        <h2>Informasi Pemilik/Penanggung Jawab</h2>
        <div class="form-group">
            <label>Nama Pemilik:</label>
            <input type="text" name="nama-pemilik" required />
        </div>
        <div class="form-group">
            <label>No Telepon/Whatsapp:</label>
            <input type="number" name="whatsapp" required />
        </div>
        <div class="form-group">
            <label>Email :</label>
            <input type="email" name="email" required />
        </div>

        <button type="submit" class="btn btn-selesai">Kirim</button>
    </div>
</form>
<script>
        // Menangkap parameter status di URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        
        // Jika status adalah 'success', tampilkan alert
        if (status === 'success') {
            alert("Data berhasil dikirim!");
        } else if (status === 'error_logo') {
            alert("Error: Upload logo gagal. Pastikan file jpg/png/webp dan ukuran max 2MB.");
        }
    </script>
    
  <!-- Feather Icons-->
  <script>
    feather.replace();
  </script>

  <!-- My Javascript -->
  <script src="js/script.js?v=20251230"></script>
</body>
</html>