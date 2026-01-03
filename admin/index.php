<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_role(['admin']);

$conn = db();

$countPendingMitra = 0;
$countPendingKurir = 0;
$countToko = 0;
$countPesanan = 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM mitra WHERE status_pengajuan = 'pending'");
$stmt->execute();
$r = $stmt->get_result();
if ($r && ($row = $r->fetch_assoc())) {
    $countPendingMitra = (int)($row['c'] ?? 0);
}
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM registrasi_driver WHERE status_pengajuan = 'pending'");
$stmt->execute();
$r = $stmt->get_result();
if ($r && ($row = $r->fetch_assoc())) {
    $countPendingKurir = (int)($row['c'] ?? 0);
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS c FROM toko');
$stmt->execute();
$r = $stmt->get_result();
if ($r && ($row = $r->fetch_assoc())) {
    $countToko = (int)($row['c'] ?? 0);
}
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) AS c FROM pesanan');
$stmt->execute();
$r = $stmt->get_result();
if ($r && ($row = $r->fetch_assoc())) {
    $countPesanan = (int)($row['c'] ?? 0);
}
$stmt->close();

admin_page_start('Dashboard', 'dashboard');
?>
<div class="admin-grid" style="margin-bottom: 16px;">
  <div class="admin-stat">
    <div class="label">Mitra Pending</div>
    <div class="value"><?php echo (int)$countPendingMitra; ?></div>
    <div class="hint">Butuh review & approval</div>
  </div>
  <div class="admin-stat">
    <div class="label">Kurir Pending</div>
    <div class="value"><?php echo (int)$countPendingKurir; ?></div>
    <div class="hint">Butuh review & approval</div>
  </div>
  <div class="admin-stat">
    <div class="label">Total Toko</div>
    <div class="value"><?php echo (int)$countToko; ?></div>
    <div class="hint">Aktif + nonaktif</div>
  </div>
  <div class="admin-stat">
    <div class="label">Total Pesanan</div>
    <div class="value"><?php echo (int)$countPesanan; ?></div>
    <div class="hint">Semua status</div>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Aksi Cepat</div>
      <div class="admin-card-subtitle">Kelola workflow utama admin dalam 1 klik</div>
    </div>
  </div>
  <div class="admin-card-body">
    <div style="display:flex; gap: 10px; flex-wrap: wrap;">
      <a class="btn btn-primary" href="mitra.php">Kelola Mitra</a>
      <a class="btn btn-primary" href="kurir.php">Kelola Kurir</a>
      <a class="btn btn-secondary" href="toko.php">Kelola Toko</a>
      <a class="btn btn-secondary" href="pesanan.php">Kelola Pesanan</a>
    </div>
  </div>
</div>

<?php admin_page_end(); ?>
