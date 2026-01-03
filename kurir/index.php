<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';
require_role(['kurir']);

$displayName = auth_display_name();
?>
<?php portal_page_start('kurir', 'Dashboard Kurir', 'dashboard'); ?>
    <section class="portal">
      <div class="portal-head">
        <h1>Dashboard Kurir</h1>
        <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Kurir'); ?>. Ambil job dan update status pengantaran dengan cepat.</p>
      </div>

      <div class="portal-grid">
        <div class="portal-card">
          <div class="portal-card-icon"><i data-feather="inbox"></i></div>
          <h3>Pesanan Tersedia</h3>
          <p>Lihat daftar pesanan yang bisa kamu ambil.</p>
          <div class="portal-card-actions">
            <button class="join-button" onclick="window.location.href='pesanan.php'">Buka</button>
          </div>
        </div>

        <div class="portal-card">
          <div class="portal-card-icon"><i data-feather="navigation"></i></div>
          <h3>Status Pengantaran</h3>
          <p>Update status: diambil, diantar, selesai.</p>
          <div class="portal-card-actions">
            <button class="join-button" onclick="window.location.href='pesanan.php'">Update</button>
          </div>
        </div>

        <div class="portal-card">
          <div class="portal-card-icon"><i data-feather="help-circle"></i></div>
          <h3>Panduan</h3>
          <p>Tips cepat: ambil pesanan, konfirmasi lokasi, dan komunikasi.</p>
          <div class="portal-card-actions">
            <button class="join-button" onclick="window.location.href='pesanan.php'">Lihat</button>
          </div>
        </div>
      </div>
    </section>

<?php portal_page_end(); ?>
