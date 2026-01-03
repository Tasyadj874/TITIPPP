<?php
require_once __DIR__ . '/includes/auth.php';
auth_start();

$conn = db();

$recommendedProducts = [];
$stores = [];

$produkQueryError = '';
$tokoQueryError = '';

try {
    $stmt = $conn->prepare('SELECT p.id, p.toko_id, p.nama, p.harga, p.foto, t.nama AS toko_nama, t.kategori AS toko_kategori, t.rating_avg AS toko_rating_avg FROM produk p JOIN toko t ON t.id = p.toko_id WHERE p.status_aktif = 1 AND t.status_aktif = 1 ORDER BY p.id DESC LIMIT 8');
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r) {
        $recommendedProducts = $r->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $produkQueryError = $e->getMessage();
}

try {
    $stmt = $conn->prepare('SELECT t.id, t.nama, t.kategori, t.logo, t.rating_avg, t.jam_buka, t.jam_tutup FROM toko t WHERE t.status_aktif = 1 ORDER BY t.rating_avg DESC, t.id DESC LIMIT 24');
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r) {
        $stores = $r->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $tokoQueryError = $e->getMessage();
}
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
          <a href="mitra-titip.php" >Mitra </a>
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

    <section id="mitra-titip" class="mitra-titip">
      <h2>MITRA TITIP</h2>

      <div class="mitra-block">
        <div class="mitra-block-head">
          <h3>Rekomendasi Produk</h3>
          <p>Produk terbaru dari toko mitra (aktif).</p>
        </div>

        <div class="container">
          <?php if ($produkQueryError !== ''): ?>
            <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">
              <?php if (stripos($produkQueryError, "doesn't exist") !== false): ?>
                Database belum lengkap (tabel <strong>produk</strong> belum ada). Import file <strong>admintitip_schema.sql</strong> ke database <strong>titip2</strong>.
              <?php else: ?>
                Gagal memuat produk: <?php echo htmlspecialchars($produkQueryError); ?>
              <?php endif; ?>
            </div>
          <?php elseif (!$recommendedProducts): ?>
            <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">Belum ada produk untuk ditampilkan.</div>
          <?php else: ?>
            <?php foreach ($recommendedProducts as $p): ?>
              <?php
                $foto = (string)($p['foto'] ?? '');
                if ($foto === '') {
                    $foto = 'images/Kurir logo.png';
                }
                $tokoId = (int)($p['toko_id'] ?? 0);
                $produkId = (int)($p['id'] ?? 0);
              ?>
              <div class="product-box">
                <img src="<?php echo htmlspecialchars($foto); ?>" alt="<?php echo htmlspecialchars((string)$p['nama']); ?>" />
                <div class="product-info">
                  <h3><?php echo htmlspecialchars((string)$p['nama']); ?></h3>
                  <p><?php echo htmlspecialchars((string)($p['toko_nama'] ?? '')); ?></p>
                  <p><?php echo htmlspecialchars((string)($p['toko_kategori'] ?? '')); ?></p>
                  <p class="rating">Rating Toko: <?php echo htmlspecialchars((string)($p['toko_rating_avg'] ?? '0')); ?></p>
                  <p class="product-price">Rp <?php echo number_format((int)($p['harga'] ?? 0), 0, ',', '.'); ?></p>
                  <?php if ($tokoId > 0 && $produkId > 0): ?>
                    <div class="product-actions">
                      <a class="btn-pesan" href="toko.php?id=<?php echo urlencode((string)$tokoId); ?>&produk_id=<?php echo urlencode((string)$produkId); ?>#form-pesan">Pesan</a>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="mitra-block">
        <div class="mitra-block-head">
          <h3>Daftar Toko</h3>
          <p>Pilih toko favoritmu untuk belanja makanan, minuman, obat, dan lainnya.</p>
        </div>

        <div class="container">
          <?php if ($tokoQueryError !== ''): ?>
            <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">
              <?php if (stripos($tokoQueryError, "doesn't exist") !== false): ?>
                Database belum lengkap (tabel <strong>toko</strong> belum ada). Import file <strong>admintitip_schema.sql</strong> ke database <strong>titip2</strong>.
              <?php else: ?>
                Gagal memuat toko: <?php echo htmlspecialchars($tokoQueryError); ?>
              <?php endif; ?>
            </div>
          <?php elseif (!$stores): ?>
            <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">Belum ada toko untuk ditampilkan.</div>
          <?php else: ?>
            <?php foreach ($stores as $t): ?>
              <?php
                $logo = (string)($t['logo'] ?? '');
                if ($logo === '') {
                    $logo = 'images/LOGO TITIP.png';
                }
                $b = $t['jam_buka'] ? substr((string)$t['jam_buka'], 0, 5) : '-';
                $u = $t['jam_tutup'] ? substr((string)$t['jam_tutup'], 0, 5) : '-';
                $tokoId = (int)($t['id'] ?? 0);
              ?>
              <div class="product-box">
                <img src="<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars((string)$t['nama']); ?>" />
                <div class="product-info">
                  <h3><?php echo htmlspecialchars((string)$t['nama']); ?></h3>
                  <p><?php echo htmlspecialchars((string)($t['kategori'] ?? '')); ?></p>
                  <p>Jam Operasi: <?php echo htmlspecialchars($b . ' - ' . $u); ?></p>
                  <p class="rating">Rating: <?php echo htmlspecialchars((string)($t['rating_avg'] ?? '0')); ?></p>
                  <?php if ($tokoId > 0): ?>
                    <div class="product-actions">
                      <a class="btn-pesan" href="toko.php?id=<?php echo urlencode((string)$tokoId); ?>">Kunjungi Toko</a>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Feather Icons-->
    <script>
      feather.replace();
    </script>

    <!-- My Javascript -->
    <script src="js/script.js?v=20251230"></script>
  </body>
</html>