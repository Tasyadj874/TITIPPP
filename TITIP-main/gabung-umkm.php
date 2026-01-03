<?php 
    session_start();
 
    $isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
    $namaLogin = $isLoggedIn ? (string) ($_SESSION['nama'] ?? '') : '';
    $roleLogin = $isLoggedIn ? (string) ($_SESSION['role'] ?? 'user') : 'user';
 
    $dashboardUrl = 'index.php';
    if ($roleLogin === 'admin') {
        $dashboardUrl = 'admin/dashboard.php';
    } elseif ($roleLogin === 'mitra') {
        $dashboardUrl = 'mitra-titip.php';
    } elseif ($roleLogin === 'pasukan' || $roleLogin === 'kurir') {
        $dashboardUrl = 'admin/pasukan.php';
    }
 
    require "config.php"; // Menggunakan $con sebagai variabel koneksi

    // 1. Mengambil Data Profil UMKM
    // Kita asumsikan hanya ada satu atau kita ingin data profil UMKM yang terakhir diinput
    $queryUMKM = mysqli_query($con, "SELECT * FROM umkm_profile ORDER BY id DESC LIMIT 1");
    $dataUMKM = mysqli_fetch_assoc($queryUMKM); // Ambil data profil UMKM sebagai array asosiatif
    $jumlahUMKM = mysqli_num_rows($queryUMKM); // Seharusnya 1 jika data sudah ada

    // 2. Mengambil Daftar Kategori (Tabel Kategori yang sudah Anda buat)
    $queryKategori = mysqli_query($con, "SELECT * FROM kategori");

    // 3. Fungsi Utility: Generate Random String
    // Fungsi ini tidak langsung terkait dengan UMKM, tetapi berguna untuk nama file atau kode unik
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString; // Sudah benar, titik koma tidak perlu jika return di baris terakhir
    }

    // Variabel yang siap digunakan:
    // $dataUMKM   -> Array asosiatif berisi profil UMKM (nama_usaha, alamat, dll.)
    // $jumlahUMKM -> Jumlah baris data UMKM yang ditemukan (seharusnya 1)
    // $queryKategori -> Resource query berisi semua kategori
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
    <link rel="stylesheet" href="css/mitra.css" />
  </head>

  <body>
    <!-- Navbar Logo Start -->
    <div class="white-navbar">
      <div class="logo-container">
        <img src="images/LOGO TITIP.png" alt="Logo TITIP" class="logo" />
      </div>
    </div>
    <!-- Navbar Logo End -->

    <!-- Navbar Menu Start -->
    <div class="red-navbar">
      <nav class="navbar">
        <div class="navbar-nav">
          <a href="index.php#home">Home</a>
          <a href="index.php#layanan">Layanan</a>
          <a href="index.php#about">Tentang Kami</a>
          <a href="index.php#gabung-mitra">Gabung Mitra</a>
          <a href="mitra-titip.php">Mitra</a>
          <a href="index.php#contact">Kontak</a>
        </div>
        <div class="navbar-extra">
          <a href="#" id="login" class="account-trigger">
            <?php if ($isLoggedIn): ?>
              <span class="account-name"><?php echo htmlspecialchars($namaLogin !== '' ? $namaLogin : 'Akun'); ?></span>
            <?php endif; ?>
            <i data-feather="users"></i>
          </a>
          <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
          <div class="dropdown" id="login-options">
            <?php if (!$isLoggedIn): ?>
              <a href="user/login.php">Login</a>
              <a href="user/register.php">Daftar</a>
            <?php else: ?>
              <div class="dropdown-header">
                <div class="dropdown-name"><?php echo htmlspecialchars($namaLogin !== '' ? $namaLogin : 'Akun'); ?></div>
                <div class="dropdown-role"><?php echo htmlspecialchars($roleLogin); ?></div>
              </div>
              <div class="dropdown-divider"></div>
              <a href="<?php echo htmlspecialchars($dashboardUrl); ?>">Dashboard</a>
              <?php if ($roleLogin === 'user'): ?>
                <a href="user/logout.php?redirect=<?php echo urlencode('../gabung-umkm.php'); ?>">Logout</a>
              <?php else: ?>
                <a href="admin/logout.php?redirect=<?php echo urlencode('../gabung-umkm.php'); ?>">Logout</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </div>

    <!-- Navbar Menu End -->

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
            <select name="bidang-usaha" required>
                <option value="">-- Pilih Kategori --</option>
                <?php 
                if ($queryKategori) {
                    @mysqli_data_seek($queryKategori, 0);
                    while ($kat = mysqli_fetch_assoc($queryKategori)) { 
                        $namaKat = $kat['nama'] ?? '';
                        echo '<option value="' . htmlspecialchars($namaKat) . '">' . htmlspecialchars($namaKat) . '</option>';
                    }
                }
                ?>
            </select>
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
        } else if (status === 'invalid_category') {
            alert("Kategori tidak valid. Silakan pilih kategori yang tersedia.");
        } else if (status === 'error_upload') {
            alert("Gagal mengunggah logo. Coba lagi.");
        } else if (status === 'error_save') {
            alert("Gagal menyimpan data. Coba lagi.");
        }
    </script>
    <section id="contact" class="contact"><h2>KONTAK TITIP</h2><div class="row"><div class="info"><p>TITIP Indonesia adalah layanan kurir baru dan andal di Parepare. Kami menawarkan solusi pengiriman untuk individu dan bisnis di dalam kota.</p><div class="contact-details"><p><i data-feather="map-pin"></i>Bukit Ambassador C17 Kota Parepare, Sulawesi Selatan, Indonesia</p><p><i data-feather="phone"></i>0851 7990 2326</p><p><i data-feather="mail"></i>officialtitip@gmail.com</p></div></div><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3031.7130967431226!2d119.63935017352472!3d-4.000860644615813!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d95bafc4a389f07%3A0x5727e84f2eac3ff9!2sBukit%20Ambassador!5e1!3m2!1sen!2sid!4v1735952339916!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="maps"></iframe></div></section>
    <footer>Copyright © 2024 TITIP Indonesia</footer>
    
  <!-- Feather Icons-->
  <script>
    feather.replace();
  </script>

  <!-- My Javascript -->
  <script src="js/script.js?v=2"></script>
</body>
</html>