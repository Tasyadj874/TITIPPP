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

    // 1. Mengambil Daftar Semua Pendaftar Jastip
    // Mengambil semua kolom dari tabel pendaftar_jastip, diurutkan berdasarkan tanggal pendaftaran terbaru
    $queryPendaftar = mysqli_query($con, "SELECT * FROM daftar_pasukan ORDER BY tanggal_daftar DESC");
    
    // Hitung jumlah total pendaftar
    $jumlahPendaftar = mysqli_num_rows($queryPendaftar); 

    // Variabel $queryKategori dihilangkan karena tidak relevan dengan data pendaftar jastip.

    // 2. Fungsi Utility: Generate Random String
    // Fungsi ini sangat berguna di halaman pemrosesan data (seperti saat upload dokumen pendaftar)
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    // Variabel yang siap digunakan:
    // $queryPendaftar  -> Resource query berisi daftar semua pendaftar jastip.
    // $jumlahPendaftar -> Jumlah total pendaftar yang ditemukan.
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
                <a href="user/logout.php?redirect=<?php echo urlencode('../gabung-pasukan.php'); ?>">Logout</a>
              <?php else: ?>
                <a href="admin/logout.php?redirect=<?php echo urlencode('../gabung-pasukan.php'); ?>">Logout</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </div>

    <!-- Navbar Menu End -->

    <!-- Content -->
    <form action="submit-pasukan.php" method="POST" enctype="multipart/form-data">
    <div class="container">
        <h1>DAFTAR PASUKAN TITIP</h1>

        <!-- Data Pribadi -->
        <h2>Data pribadi</h2>
        <div class="form-group">
            <label>Nama:</label>
            <input type="text" name="nama" required />
        </div>
        <div class="form-group">
            <label>Tempat/Tgl lahir:</label>
            <input type="text" name="tempat" placeholder="Masukkan tempat lahir" required />
            <input type="date" name="tanggal_lahir" required />
        </div>
        <div class="form-group">
            <label>Jenis kelamin:</label>
            <select name="jenis_kelamin" required>
                <option value="laki-laki">Laki-laki</option>
                <option value="perempuan">Perempuan</option>
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
            <label>Email:</label>
            <input type="email" name="email" required />
        </div>

        
        <div class="form-group">
            <label>Upload File Sim C</label>
            <input type="file" name="sim_c" required />
        </div>
        <div class="form-group">
            <label>Foto Driver</label>
            <input type="file" name="foto_diri" required />
        </div>

        <!-- Informasi Kendaraan -->
        <h2>Informasi kendaraan</h2>
        <div class="form-group">
            <label>Merk kendaraan:</label>
            <input type="text" name="merk_kendaraan" required />
        </div>
        <div class="form-group">
            <label>Nomor plat kendaraan:</label>
            <input type="text" name="plat_kendaraan" required />
        </div>
        <div class="form-group">
            <label>Foto kendaraan</label>
            <input type="file" name="foto_kendaraan" required />
        </div>
        <div class="form-group">
            <label>Foto STNK</label>
            <input type="file" name="foto_stnk" required />
        </div>

        <button type="submit" class="btn btn-selesai">Kirim</button>
    </div>
</form>

    <script>
      //script pasukan TITIP
      // Fungsi untuk menangani tombol "Simpan"
      function handleSave(button) {
        button.classList.add("saving"); // Ubah warna tombol menjadi merah
        button.textContent = "Menyimpan..."; // Ubah teks tombol

        // Simulasikan proses penyimpanan
        setTimeout(() => {
          button.classList.remove("saving"); // Kembalikan warna tombol
          button.textContent = "Simpan"; // Kembalikan teks tombol
          alert("Data berhasil disimpan!");
        }, 2000); // Simulasi waktu penyimpanan 2 detik
      }

      // Fungsi untuk menangani tombol "Batal"
      function handleCancel(button) {
        const confirmation = confirm(
          "Apakah Anda yakin ingin membatalkan penyimpanan?"
        );
        if (confirmation) {
          alert("Penyimpanan dibatalkan.");
        }
      }
    </script>
    <script>
        // Menangkap parameter status di URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        
        // Jika status adalah 'success', tampilkan alert
        if (status === 'success') {
            alert("Data berhasil disimpan!");
        } else if (status === 'error_upload') {
            const file = urlParams.get('file');
            alert("Gagal mengunggah file" + (file ? " (" + file + ")" : "") + ". Coba lagi.");
        } else if (status === 'error_missing_file') {
            const file = urlParams.get('file');
            alert("File wajib belum diunggah" + (file ? " (" + file + ")" : "") + ".");
        } else if (status === 'error_kendaraan_save') {
            alert("Error: Data kendaraan gagal disimpan.");
        } else if (status === 'error_registrasi_save') {
            alert("Error: Data registrasi gagal disimpan.");
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
