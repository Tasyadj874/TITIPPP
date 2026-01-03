<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';
require_role(['kurir']);

$conn = db();
$kurirId = (int)(auth_id() ?? 0);
$displayName = auth_display_name();

$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $pesananId = (int)($_POST['pesanan_id'] ?? 0);

    if ($kurirId > 0 && $pesananId > 0 && $action === 'ambil') {
        $stmt = $conn->prepare("UPDATE pesanan SET kurir_id = ?, status = 'diambil_kurir' WHERE id = ? AND kurir_id IS NULL AND status = 'dibuat'");
        $stmt->bind_param('ii', $kurirId, $pesananId);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();

        $flash = $ok ? 'Pesanan berhasil diambil.' : 'Pesanan sudah diambil kurir lain atau status berubah.';
    }

    if ($kurirId > 0 && $pesananId > 0 && $action === 'set_status') {
        $newStatus = (string)($_POST['status'] ?? '');
        if (!in_array($newStatus, ['diantar', 'selesai', 'batal'], true)) {
            $newStatus = '';
        }

        if ($newStatus !== '') {
            $stmt = $conn->prepare('SELECT status FROM pesanan WHERE id = ? AND kurir_id = ? LIMIT 1');
            $stmt->bind_param('ii', $pesananId, $kurirId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            $curr = (string)($row['status'] ?? '');
            $allowed = false;
            if ($curr === 'diambil_kurir' && ($newStatus === 'diantar' || $newStatus === 'batal')) {
                $allowed = true;
            }
            if ($curr === 'diantar' && ($newStatus === 'selesai' || $newStatus === 'batal')) {
                $allowed = true;
            }

            if ($allowed) {
                $stmt = $conn->prepare('UPDATE pesanan SET status = ? WHERE id = ? AND kurir_id = ?');
                $stmt->bind_param('sii', $newStatus, $pesananId, $kurirId);
                $stmt->execute();
                $stmt->close();
                $flash = 'Status pesanan diperbarui.';
            } else {
                $flash = 'Perubahan status tidak valid.';
            }
        }
    }
}

$available = [];
$mine = [];

$sqlAvailable = "
SELECT
  p.id,
  p.alamat_jemput,
  p.alamat_antar,
  p.nama_penerima,
  p.whatsapp,
  p.metode_pembayaran,
  p.status,
  p.created_at,
  u.nama AS user_nama,
  t.nama AS toko_nama,
  GROUP_CONCAT(CONCAT(pi.nama_produk, ' x', pi.qty) SEPARATOR ', ') AS items
FROM pesanan p
JOIN users u ON u.id = p.user_id
JOIN toko t ON t.id = p.toko_id
LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
WHERE p.kurir_id IS NULL AND p.status = 'dibuat'
GROUP BY p.id
ORDER BY p.id DESC
";

$stmt = $conn->prepare($sqlAvailable);
$stmt->execute();
$r = $stmt->get_result();
if ($r) {
    $available = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$sqlMine = "
SELECT
  p.id,
  p.alamat_jemput,
  p.alamat_antar,
  p.nama_penerima,
  p.whatsapp,
  p.metode_pembayaran,
  p.status,
  p.created_at,
  u.nama AS user_nama,
  t.nama AS toko_nama,
  GROUP_CONCAT(CONCAT(pi.nama_produk, ' x', pi.qty) SEPARATOR ', ') AS items
FROM pesanan p
JOIN users u ON u.id = p.user_id
JOIN toko t ON t.id = p.toko_id
LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
WHERE p.kurir_id = ?
GROUP BY p.id
ORDER BY p.id DESC
";

$stmt = $conn->prepare($sqlMine);
$stmt->bind_param('i', $kurirId);
$stmt->execute();
$r = $stmt->get_result();
if ($r) {
    $mine = $r->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>
<?php portal_page_start('kurir', 'Pesanan Tersedia - Kurir', 'pesanan'); ?>
    <section class="portal">
      <div class="portal-head">
        <h1>Pesanan Tersedia</h1>
        <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Kurir'); ?>. Pilih pesanan, ambil job, lalu update status pengantaran.</p>
      </div>

      <div class="portal-grid">
        <?php if ($flash !== ''): ?>
          <div class="portal-card" style="grid-column: 1 / -1; border-color: rgba(255,0,0,.15);">
            <div class="portal-card-icon"><i data-feather="info"></i></div>
            <h3>Info</h3>
            <p><?php echo htmlspecialchars($flash); ?></p>
          </div>
        <?php endif; ?>

        <div class="portal-card" style="grid-column: 1 / -1;">
          <div class="portal-card-icon"><i data-feather="inbox"></i></div>
          <h3>Job Tersedia</h3>
          <p>Pesanan yang belum diambil kurir.</p>

          <div style="overflow-x: auto; margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Toko</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Item</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Jemput</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Antar</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$available): ?>
                  <tr>
                    <td colspan="6" style="padding: 10px;">Belum ada pesanan tersedia.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($available as $o): ?>
                    <tr>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;">#<?php echo (int)$o['id']; ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$o['toko_nama']); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['items'] ?? '-')); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['alamat_jemput'] ?? '-')); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['alamat_antar'] ?? '-')); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;">
                        <form method="POST">
                          <input type="hidden" name="action" value="ambil" />
                          <input type="hidden" name="pesanan_id" value="<?php echo (int)$o['id']; ?>" />
                          <button type="submit" class="join-button">Ambil Job</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="portal-card" style="grid-column: 1 / -1;">
          <div class="portal-card-icon"><i data-feather="navigation"></i></div>
          <h3>Pesanan Saya</h3>
          <p>Job yang sudah kamu ambil.</p>

          <div style="overflow-x: auto; margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Status</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Toko</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Item</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Penerima</th>
                  <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$mine): ?>
                  <tr>
                    <td colspan="6" style="padding: 10px;">Belum ada pesanan yang kamu ambil.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($mine as $o): ?>
                    <?php $st = (string)($o['status'] ?? ''); ?>
                    <tr>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;">#<?php echo (int)$o['id']; ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($st); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$o['toko_nama']); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['items'] ?? '-')); ?></td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;">
                        <?php echo htmlspecialchars((string)($o['nama_penerima'] ?? '-')); ?>
                        <div style="color:#667085; font-size: 12px;"><?php echo htmlspecialchars((string)($o['whatsapp'] ?? '-')); ?></div>
                      </td>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;">
                        <?php if ($st === 'diambil_kurir'): ?>
                          <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="action" value="set_status" />
                            <input type="hidden" name="pesanan_id" value="<?php echo (int)$o['id']; ?>" />
                            <input type="hidden" name="status" value="diantar" />
                            <button type="submit" class="join-button">Mulai Antar</button>
                          </form>
                        <?php elseif ($st === 'diantar'): ?>
                          <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="action" value="set_status" />
                            <input type="hidden" name="pesanan_id" value="<?php echo (int)$o['id']; ?>" />
                            <input type="hidden" name="status" value="selesai" />
                            <button type="submit" class="join-button">Selesai</button>
                          </form>
                        <?php else: ?>
                          -
                        <?php endif; ?>
                      </td>
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
