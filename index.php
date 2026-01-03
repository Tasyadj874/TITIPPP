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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap"
      rel="stylesheet"
    />
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
  </head>

  <body>
    <div class="red-navbar">
      <nav class="navbar">
        <div class="logo-container">
          <img src="images/LOGO TITIP.png" alt="Logo TITIP" class="logo" />
        </div>
        <div class="navbar-nav">
          <a href="#home">Asu</a><a href="#layanan">Anjing</a><a href="#about">Tentang Kami</a><a href="#gabung-mitra">Gabung Mitra</a><a href="mitra-titip.php">Mitra</a><a href="#contact">Kontak</a>
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
              <a href="auth/login.php?role=user">Login User</a><a href="auth/login.php?role=kurir">Login Kurir</a><a href="auth/login.php?role=mitra">Login Mitra</a><a href="auth/login.php?role=admin">Login Admin</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </div>

    <section class="hero" id="home">
      <main class="content">
        <div class="text-content">
          <h1>SEMUA TENANG, SEMUA SENANG</h1>
          <h2>#SEMUABISADITITIP</h2>
          <p>Kami ada untuk semua kebahagiaan. Kami hadir untuk segala kemudahan. Kemudahan pesan makanan, belanja di pasar, mengirim barang, belanja obat di apotek dan masih banyak kemudahan lainnya.</p>
        </div>
        <div class="image-content">
          <img src="images/Kurir logo.png" alt="Kurir Logo" />
        </div>
      </main>
    </section>

    <section id="layanan">
      <div class="container mt-5">
        <div class="layanan-container">
          <div class="layanan-box"><img src="images/titip belanja.jpg" alt="Layanan Titip Belanja" /><div class="layanan-content"><h3>TITIP Belanja</h3><p>Lebih mudah berbelanja di swalayan, minimarket, pasar tradisional, hingga toko kelontong. Tidak perlu keluar rumah.</p><button class="btn-pesan" data-layanan="TITIP Belanja">Pesan Disini</button></div></div>
          <div class="layanan-box"><img src="images/titip barang.jpg" alt="Layanan Titip Barang" /><div class="layanan-content"><h3>TITIP Barang</h3><p>Kirim barang/paket ke pelanggan dengan lebih mudah dan aman. Melayani barang skala kecil hingga sedang.</p><button class="btn-pesan" data-layanan="TITIP Barang">Pesan Disini</button></div></div>
          <div class="layanan-box"><img src="images/titip makanan.jpg" alt="Layanan Titip Makanan" /><div class="layanan-content"><h3>TITIP Makanan</h3><p>Pesan makanan di warung atau restoran favorit kalian. Resto lebih lengkap dan support UMKM.</p><button class="btn-pesan" data-layanan="TITIP Makanan">Pesan Disini</button></div></div>
          <div class="layanan-box"><img src="images/titip obat.jpg" alt="Layanan Titip Obat" /><div class="layanan-content"><h3>TITIP Obat</h3><p>Istirahat lebih nyaman di rumah. Percayakan belanjaan obat-obatan atau sejenisnya kepada kami.</p><button class="btn-pesan" data-layanan="TITIP Obat">Pesan Disini</button></div></div>
          <div class="section-heading text-center"><br /><br /><h2>BINGUNG MAU BELI APA? YUK KE <button class="join-button-mitra" onclick="window.location.href='mitra-titip.php'">MITRA TITIP</button></h2></div>
        </div>
      </div>
    </section>

    <div id="form-pemesanan" class="form-popup">
      <div class="form-content">
        <h3 id="form-title"></h3>
        <form id="pemesanan-form">
          <label for="alamat-toko">Lokasi Jemput:</label>
          <input type="text" id="alamat-toko" name="alamat-toko" placeholder="Isi manual atau pilih lewat peta" required />

          <label for="alamat-penerima">Lokasi Antar:</label>
          <input type="text" id="alamat-penerima" name="alamat-penerima" placeholder="Isi manual atau pilih lewat peta" required />
          
          <div class="location-info" id="location-info-penerima" style="display: none;">
              <p><strong>Lokasi Antar:</strong></p>
              <span id="alamat-penerima-text"></span>
          </div>
           <div class="location-info" id="location-info-toko" style="display: none;">
              <p><strong>Lokasi Jemput:</strong></p>
              <span id="alamat-toko-text"></span>
          </div>

          <button type="button" class="btn-map" id="btn-set-location">Tentukan Lokasi di Peta</button>
          
          <div class="calculation-result" id="calculation-result" style="display: none;">
                <p>Jarak Tempuh: <span id="distance">0 km</span></p>
                <p>Estimasi Ongkir: <span id="ongkir">Rp 0</span></p>
            </div>

          <div id="mapid-container" style="display:none;"><div id="mapid"></div></div>

          <div id="order-details" style="display: none;">
            <hr>
            <label for="nama-penerima">Nama Penerima:</label>
            <input type="text" id="nama-penerima" name="nama-penerima" required />

            <label for="nama-toko">Nama Toko:</label>
            <input type="text" id="nama-toko" name="nama-toko" required />

            <label for="whatsapp">WhatsApp:</label>
            <input type="text" id="whatsapp" name="whatsapp" required />

            <label for="keterangan">Keterangan:</label>
            <textarea id="keterangan" name="keterangan" placeholder="Contoh: Pesanan & Jumlah"></textarea>

            <label for="metode">Metode Pembayaran:</label>
            <div>
              <button type="button" class="btn-metode" data-method="Transfer">Transfer</button>
              <button type="button" class="btn-metode" data-method="COD">COD</button>
            </div>
            <input type="hidden" id="metode" name="metode" value="Transfer" />

            <button type="submit" class="btn-submit">KIRIM PESANAN</button>
          </div>
        </form>
        <button class="btn-close">Tutup</button>
      </div>
    </div>
    <section id="about" class="about"><h2>SEKILAS TITIP</h2><h3 class="hashtag">#SemuaBisaDiTITIP</h3><br /><br /><br /><p>TITIP merupakan layanan pengantaran darat yang beroperasi di wilayah Kota Parepare dan sekitarnya. Dengan berbekal layanan berbasis aplikasi perpesanan Whatsapp yang cepat, sederhana dan aman, TITIP berkomitmen memberikan akses layanan yang lebih dekat dengan kehidupan masyarakat.</p><br /><p>Beberapa point tersebut diantaranya, kemudahan komunikasi dan transaksi, hospitality, konsisten brand, hingga menyangkut masalah kesejahteraan mitra pengemudi</p></section>
    <section id="gabung-mitra" class="gabung-mitra"><h2>GABUNG MITRA</h2><h3>MARI BERGABUNG BERSAMA TITIP</h3><br /><br /><div class="content"><div class="button-container"><div class="button-item"><button class="join-button" onclick="window.location.href='gabung-pasukan.php'">GABUNG PASUKAN TITIP</button><p>Bergabung bersama kami di TITIP. Dapatkan keuntungan lebih menjadi pasukan TITIP.</p><ul><li>Income 3 - 4 Juta</li><li>Driver Point</li><li>Driver Care (Program Jaminan Kesehatan)</li><li>Hospitality Class</li></ul></div><div class="button-item"><button class="join-button" onclick="window.location.href='gabung-umkm.php'">GABUNG MITRA UMKM</button><p>Raih Penjualan lebih baik bersama kami di TITIP. Menjadi mitra UMKM TITIP, upgrade level usaha kalian.</p><ul><li>WhatsApp Broadcast Promotion</li><li>Media Social Branding</li><li>UMKM Class by TITIP</li><li>Free Merchandise</li></ul></div></div></div><p class="footer">DAN MASIH BANYAK LAGI KEUNTUNGAN LAINNYA</p></section>
    <section id="contact" class="contact"><h2>KONTAK TITIP</h2><div class="row"><div class="info"><p>TITIP Indonesia adalah layanan kurir baru dan andal di Parepare. Kami menawarkan solusi pengiriman untuk individu dan bisnis di dalam kota.</p><div class="contact-details"><p><i data-feather="map-pin"></i>Bukit Ambassador C17 Kota Parepare, Sulawesi Selatan, Indonesia</p><p><i data-feather="phone"></i>0851 7990 2326</p><p><i data-feather="mail"></i>officialtitip@gmail.com</p></div></div><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3031.7130967431226!2d119.63935017352472!3d-4.000860644615813!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d95bafc4a389f07%3A0x5727e84f2eac3ff9!2sBukit%20Ambassador!5e1!3m2!1sen!2sid!4v1735952339916!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="maps"></iframe></div></section>
    <footer>Copyright 2024 TITIP Indonesia</footer>

    <script>feather.replace();</script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="js/script.js?v=20251230"></script>
  </body>
</html>