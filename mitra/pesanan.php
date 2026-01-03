<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';
require_role(['mitra']);

$conn = db();
$mitraId = (int)($_SESSION['auth']['data']['mitra_id'] ?? 0);
$displayName = auth_display_name();

$rows = [];
if ($mitraId > 0) {
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
      GROUP_CONCAT(CONCAT(pi.nama_produk, ' x', pi.qty) SEPARATOR ', ') AS items
    FROM pesanan p
    JOIN toko t ON t.id = p.toko_id
    JOIN users u ON u.id = p.user_id
    LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
    WHERE t.mitra_id = ?
    GROUP BY p.id
    ORDER BY p.id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $mitraId);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r) {
        $rows = $r->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}
?>
<?php portal_page_start('mitra', 'Pesanan Masuk - Mitra', 'pesanan'); ?>
    <section class="portal">
      <div class="portal-head">
        <h1>Pesanan Masuk</h1>
        <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Mitra'); ?>. Di sini kamu akan melihat pesanan yang masuk ke tokomu.</p>
      </div>

      <div class="portal-grid">
        <div class="portal-card" style="grid-column: 1 / -1;">
          <div class="portal-card-icon"><i data-feather="clipboard"></i></div>
          <h3>Daftar Pesanan</h3>
          <p>Pesanan yang masuk ke toko milikmu.</p>

          <div style="overflow-x: auto; margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Status</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Toko</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">User</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Item</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Antar</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$rows): ?>
                  <tr>
                    <td colspan="6" style="padding: 10px;">Belum ada pesanan.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $o): ?>
                    <tr>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;">#<?php echo (int)$o['id']; ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$o['status']); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$o['toko_nama']); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$o['user_nama']); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['items'] ?? '-')); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['alamat_antar'] ?? '-')); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

<?php portal_page_end(); ?>
