<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';

require_role(['user']);

$displayName = auth_display_name();

portal_page_start('user', 'Dashboard - User', 'dashboard');
?>
  <section class="portal">
    <div class="portal-head">
      <h1>Dashboard</h1>
      <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'User'); ?>. Pilih menu untuk mulai pesan atau cek riwayat pesanan.</p>
    </div>

    <div class="portal-grid">
      <div class="portal-card">
        <div class="portal-card-icon"><i data-feather="shopping-bag"></i></div>
        <h3>Belanja Mitra</h3>
        <p>Lihat rekomendasi produk dan daftar toko mitra.</p>
        <div class="portal-card-actions">
          <a class="btn-pesan" href="<?php echo htmlspecialchars(app_base_path()); ?>/mitra-titip.php">Buka Mitra</a>
        </div>
      </div>

      <div class="portal-card">
        <div class="portal-card-icon"><i data-feather="clock"></i></div>
        <h3>Pesanan Saya</h3>
        <p>Lihat status pesanan, detail item, dan rating toko setelah selesai.</p>
        <div class="portal-card-actions">
          <a class="btn-pesan" href="<?php echo htmlspecialchars(app_base_path()); ?>/user/pesanan.php">Lihat Pesanan</a>
        </div>
      </div>

      <div class="portal-card">
        <div class="portal-card-icon"><i data-feather="map-pin"></i></div>
        <h3>Pesan Layanan</h3>
        <p>Buat pesanan layanan (antar barang, titip, dll) dari halaman utama.</p>
        <div class="portal-card-actions">
          <a class="btn-pesan" href="<?php echo htmlspecialchars(app_base_path()); ?>/index.php">Ke Home</a>
        </div>
      </div>
    </div>
  </section>

<?php portal_page_end(); ?>
