<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_role(['admin']);

$conn = db();

$recommended = [];
$all = [];

$stmt = $conn->prepare("SELECT t.id, t.nama, t.kategori, t.rating_avg, m.nama AS nama_mitra FROM toko t JOIN mitra m ON m.id = t.mitra_id WHERE t.status_aktif = 1 ORDER BY t.rating_avg DESC, t.id DESC LIMIT 5");
$stmt->execute();
$r = $stmt->get_result();
if ($r) {
    $recommended = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$stmt = $conn->prepare("SELECT t.id, t.nama, t.kategori, t.rating_avg, m.nama AS nama_mitra FROM toko t JOIN mitra m ON m.id = t.mitra_id ORDER BY t.id DESC");
$stmt->execute();
$r = $stmt->get_result();
if ($r) {
    $all = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>
<?php
admin_page_start('Toko', 'toko');
?>

<div class="admin-card" style="margin-bottom: 16px;">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Rekomendasi Toko</div>
      <div class="admin-card-subtitle">Top 5 berdasarkan rating</div>
    </div>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Toko</th>
          <th>Kategori</th>
          <th>Mitra</th>
          <th>Rating</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recommended as $t): ?>
          <tr>
            <td><?php echo (int)$t['id']; ?></td>
            <td><?php echo htmlspecialchars((string)$t['nama']); ?></td>
            <td><?php echo htmlspecialchars((string)($t['kategori'] ?? '-')); ?></td>
            <td><?php echo htmlspecialchars((string)$t['nama_mitra']); ?></td>
            <td><?php echo htmlspecialchars((string)$t['rating_avg']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Semua Toko</div>
      <div class="admin-card-subtitle">Daftar toko terdaftar di sistem</div>
    </div>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Toko</th>
          <th>Kategori</th>
          <th>Mitra</th>
          <th>Rating</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($all as $t): ?>
          <tr>
            <td><?php echo (int)$t['id']; ?></td>
            <td><?php echo htmlspecialchars((string)$t['nama']); ?></td>
            <td><?php echo htmlspecialchars((string)($t['kategori'] ?? '-')); ?></td>
            <td><?php echo htmlspecialchars((string)$t['nama_mitra']); ?></td>
            <td><?php echo htmlspecialchars((string)$t['rating_avg']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_page_end(); ?>
