<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';

require_role(['user']);

$conn = db();
$userId = auth_id() ?? 0;
$displayName = auth_display_name();

$flash = isset($_GET['saved']) ? (string)$_GET['saved'] : '';

$ensureRatingsTableSql = "CREATE TABLE IF NOT EXISTS toko_ratings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    pesanan_id BIGINT UNSIGNED NOT NULL,
    toko_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_toko_ratings_pesanan (pesanan_id),
    KEY idx_toko_ratings_toko (toko_id),
    KEY idx_toko_ratings_user (user_id)
) ENGINE=InnoDB";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string)$_POST['action'] : '';

    if ($action === 'rate_toko' && $userId > 0) {
        $conn->query($ensureRatingsTableSql);

        $pesananId = (int)($_POST['pesanan_id'] ?? 0);
        $tokoId = (int)($_POST['toko_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $review = trim((string)($_POST['review'] ?? ''));

        if ($rating < 1 || $rating > 5) {
            header('Location: pesanan.php?saved=error');
            exit();
        }

        $ok = false;
        if ($pesananId > 0 && $tokoId > 0) {
            $stmt = $conn->prepare('SELECT id FROM pesanan WHERE id = ? AND user_id = ? AND toko_id = ? AND status = \'selesai\' LIMIT 1');
            $stmt->bind_param('iii', $pesananId, $userId, $tokoId);
            $stmt->execute();
            $res = $stmt->get_result();
            $ok = (bool)($res && $res->fetch_assoc());
            $stmt->close();
        }

        if (!$ok) {
            header('Location: pesanan.php?saved=error');
            exit();
        }

        $stmt = $conn->prepare('INSERT INTO toko_ratings (pesanan_id, toko_id, user_id, rating, review) VALUES (?, ?, ?, ?, NULLIF(?, \'\'))');
        $stmt->bind_param('iiiis', $pesananId, $tokoId, $userId, $rating, $review);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('UPDATE toko SET rating_avg = (SELECT COALESCE(AVG(rating), 0) FROM toko_ratings WHERE toko_id = ?) WHERE id = ?');
        $stmt->bind_param('ii', $tokoId, $tokoId);
        $stmt->execute();
        $stmt->close();

        header('Location: pesanan.php?saved=1');
        exit();
    }
}

$rows = [];
if ($userId > 0) {
    $conn->query($ensureRatingsTableSql);

    $sql = "
    SELECT
      p.id,
      p.status,
      p.created_at,
      p.metode_pembayaran,
      p.alamat_antar,
      p.nama_penerima,
      p.whatsapp,
      t.id AS toko_id,
      t.nama AS toko_nama,
      tr.rating AS user_rating,
      GROUP_CONCAT(CONCAT(pi.nama_produk, ' x', pi.qty) SEPARATOR ', ') AS items
    FROM pesanan p
    JOIN toko t ON t.id = p.toko_id
    LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
    LEFT JOIN toko_ratings tr ON tr.pesanan_id = p.id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

portal_page_start('user', 'Pesanan Saya - User', 'pesanan');
?>
  <section class="portal">
    <div class="portal-head">
      <h1>Pesanan Saya</h1>
      <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'User'); ?>. Di sini tersimpan semua pesanan kamu.</p>
    </div>

    <?php if ($flash === '1'): ?>
      <div class="portal" style="padding-top: 0; padding-bottom: 0;">
        <div style="padding: 12px; border: 1px solid #bbf7d0; background: #f0fdf4; border-radius: 12px; color: #166534; font-weight: 800;">
          Rating berhasil dikirim.
        </div>
      </div>
    <?php elseif ($flash === 'error'): ?>
      <div class="portal" style="padding-top: 0; padding-bottom: 0;">
        <div style="padding: 12px; border: 1px solid #fecaca; background: #fef2f2; border-radius: 12px; color: #991b1b; font-weight: 800;">
          Gagal mengirim rating.
        </div>
      </div>
    <?php endif; ?>

    <div class="portal-grid">
      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="clipboard"></i></div>
        <h3>Riwayat Pesanan</h3>
        <p>Pesanan yang pernah kamu buat.</p>

        <div style="overflow-x: auto; margin-top: 10px;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Status</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Toko</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Item</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Alamat Antar</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Rating</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr>
                  <td colspan="6" style="padding: 10px;">Belum ada pesanan.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($rows as $o): ?>
                  <?php
                    $oid = (int)($o['id'] ?? 0);
                    $tokoId = (int)($o['toko_id'] ?? 0);
                    $status = (string)($o['status'] ?? '');
                    $userRating = isset($o['user_rating']) ? (int)$o['user_rating'] : 0;
                  ?>
                  <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">#<?php echo $oid; ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($status); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['toko_nama'] ?? '-')); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['items'] ?? '-')); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($o['alamat_antar'] ?? '-')); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                      <?php if ($status === 'selesai' && $userRating <= 0 && $oid > 0 && $tokoId > 0): ?>
                        <form method="POST" style="min-width: 240px;">
                          <input type="hidden" name="action" value="rate_toko" />
                          <input type="hidden" name="pesanan_id" value="<?php echo $oid; ?>" />
                          <input type="hidden" name="toko_id" value="<?php echo $tokoId; ?>" />

                          <div style="display: grid; grid-template-columns: 90px 1fr; gap: 10px; align-items: center;">
                            <label style="font-weight: 900;">Nilai</label>
                            <select name="rating" required>
                              <option value="">-</option>
                              <option value="5">5</option>
                              <option value="4">4</option>
                              <option value="3">3</option>
                              <option value="2">2</option>
                              <option value="1">1</option>
                            </select>
                          </div>

                          <div style="margin-top: 8px;">
                            <textarea name="review" rows="2" placeholder="Opsional" style="width: 100%;"></textarea>
                          </div>

                          <button type="submit" class="btn btn-selesai" style="margin-top: 10px;">Kirim</button>
                        </form>
                      <?php elseif ($userRating > 0): ?>
                        <?php echo (int)$userRating; ?>/5
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
