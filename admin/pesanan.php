<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_role(['admin']);

$conn = db();

$rows = [];
$sql = "
SELECT
  p.id,
  p.status,
  p.created_at,
  p.metode_pembayaran,
  p.alamat_jemput,
  p.alamat_antar,
  p.nama_penerima,
  p.whatsapp,
  u.nama AS user_nama,
  t.nama AS toko_nama,
  k.username AS kurir_username,
  GROUP_CONCAT(CONCAT(pi.nama_produk, ' x', pi.qty) SEPARATOR ', ') AS items
FROM pesanan p
JOIN users u ON u.id = p.user_id
JOIN toko t ON t.id = p.toko_id
LEFT JOIN kurir_accounts k ON k.id = p.kurir_id
LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
GROUP BY p.id
ORDER BY p.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$r = $stmt->get_result();
if ($r) {
    $rows = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>
<?php
admin_page_start('Pesanan', 'pesanan');
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Manajemen Pesanan</div>
      <div class="admin-card-subtitle">Halaman ini akan menampilkan seluruh pesanan user, statusnya, mitra, dan kurir yang mengambil.</div>
    </div>
  </div>
  <div class="admin-card-body">
    <div style="display:flex; gap: 10px; flex-wrap: wrap;">
      <a class="btn btn-secondary" href="index.php">Kembali ke Dashboard</a>
      <a class="btn btn-primary" href="toko.php">Lihat Toko</a>
    </div>
  </div>
</div>

<div class="admin-card" style="margin-top: 16px;">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Daftar Pesanan</div>
      <div class="admin-card-subtitle">Monitoring pesanan user dan statusnya.</div>
    </div>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Status</th>
          <th>Toko</th>
          <th>User</th>
          <th>Kurir</th>
          <th>Item</th>
          <th>Alamat Antar</th>
          <th>Dibuat</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr>
            <td colspan="8" style="padding: 12px;">Belum ada pesanan.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $o): ?>
            <tr>
              <td>#<?php echo (int)$o['id']; ?></td>
              <td><?php echo htmlspecialchars((string)$o['status']); ?></td>
              <td><?php echo htmlspecialchars((string)$o['toko_nama']); ?></td>
              <td><?php echo htmlspecialchars((string)$o['user_nama']); ?></td>
              <td><?php echo htmlspecialchars((string)($o['kurir_username'] ?? '-')); ?></td>
              <td><?php echo htmlspecialchars((string)($o['items'] ?? '-')); ?></td>
              <td><?php echo htmlspecialchars((string)($o['alamat_antar'] ?? '-')); ?></td>
              <td><?php echo htmlspecialchars((string)($o['created_at'] ?? '')); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_page_end(); ?>
