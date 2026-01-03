<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';
require_role(['mitra']);

$displayName = auth_display_name();
?>
<?php portal_page_start('mitra', 'Dashboard Mitra', 'dashboard'); ?>
    <section class="portal">
      <div class="portal-head">
        <h1>Dashboard Mitra</h1>
        <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Mitra'); ?>. Kelola toko, produk, dan pesananmu di sini.</p>
      </div>

      <div class="portal-grid">
        <div class="portal-card">
          <div class="portal-card-icon"><i data-feather="shopping-bag"></i></div>
          <h3>Toko Saya</h3>
          <p>Atur profil toko, jam buka, dan status aktif.</p>
          <div class="portal-card-actions">
            <button class="join-button" onclick="window.location.href='toko.php'">Buka</button>
          </div>
        </div>

        <div class="portal-card">
          <div class="portal-card-icon"><i data-feather="package"></i></div>
          <h3>Produk</h3>
          <p>Tambah, edit, dan nonaktifkan produk yang dijual.</p>
          <div class="portal-card-actions">
            <button class="join-button" onclick="window.location.href='produk.php'">Kelola</button>
          </div>
        </div>

        <div class="portal-card">
          <div class="portal-card-icon"><i data-feather="clipboard"></i></div>
          <h3>Pesanan</h3>
          <p>Lihat pesanan masuk dan status pengantaran.</p>
          <div class="portal-card-actions">
            <button class="join-button" onclick="window.location.href='pesanan.php'">Lihat</button>
          </div>
        </div>
      </div>
    </section>

<?php portal_page_end(); ?>
