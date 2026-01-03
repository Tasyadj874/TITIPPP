<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';

require_role(['mitra']);

$conn = db();
$mitraId = (int)($_SESSION['auth']['data']['mitra_id'] ?? 0);
$displayName = auth_display_name();

if ($mitraId <= 0) {
    http_response_code(403);
    die('Mitra tidak valid');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string)$_POST['action'] : '';

    if ($action === 'create_toko') {
        $nama = trim((string)($_POST['nama'] ?? ''));
        $kategori = trim((string)($_POST['kategori'] ?? ''));
        $alamat = trim((string)($_POST['alamat'] ?? ''));
        $jamBuka = trim((string)($_POST['jam_buka'] ?? ''));
        $jamTutup = trim((string)($_POST['jam_tutup'] ?? ''));

        if ($nama !== '' && $alamat !== '') {
            $stmt = $conn->prepare('INSERT INTO toko (mitra_id, nama, kategori, alamat, jam_buka, jam_tutup, status_aktif) VALUES (?, ?, ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'), 1)');
            $stmt->bind_param('isssss', $mitraId, $nama, $kategori, $alamat, $jamBuka, $jamTutup);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: toko.php');
        exit();
    }
}

$recommended = [];
$all = [];

$stmt = $conn->prepare('SELECT id, nama, kategori, rating_avg, jam_buka, jam_tutup, alamat FROM toko WHERE mitra_id = ? AND status_aktif = 1 ORDER BY rating_avg DESC, id DESC LIMIT 3');
$stmt->bind_param('i', $mitraId);
$stmt->execute();
$res = $stmt->get_result();
$recommended = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$stmt = $conn->prepare('SELECT id, nama, kategori, rating_avg, jam_buka, jam_tutup, alamat, status_aktif FROM toko WHERE mitra_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $mitraId);
$stmt->execute();
$res = $stmt->get_result();
$all = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

portal_page_start('mitra', 'Toko Saya - Mitra', 'toko');
?>
  <section class="portal">
    <div class="portal-head">
      <h1>Toko Saya</h1>
      <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Mitra'); ?>. Tambahkan toko dan pantau daftar toko milikmu.</p>
    </div>

    <div class="portal-grid">
      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="plus-circle"></i></div>
        <h3>Tambah Toko</h3>
        <p>Isi data toko untuk mulai menerima pesanan.</p>

        <form method="POST" style="margin-top: 12px; max-width: 900px;">
          <input type="hidden" name="action" value="create_toko" />

          <div class="form-group">
            <label>Nama Toko:</label>
            <input type="text" name="nama" required />
          </div>
          <div class="form-group">
            <label>Kategori:</label>
            <input type="text" name="kategori" />
          </div>
          <div class="form-group">
            <label>Alamat:</label>
            <input type="text" name="alamat" required />
          </div>
          <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
            <div class="form-group">
              <label>Jam Buka:</label>
              <input type="time" name="jam_buka" />
            </div>
            <div class="form-group">
              <label>Jam Tutup:</label>
              <input type="time" name="jam_tutup" />
            </div>
          </div>

          <button type="submit" class="btn btn-selesai">Simpan</button>
        </form>
      </div>

      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="star"></i></div>
        <h3>Rekomendasi (Toko Terbaik Anda)</h3>
        <p>Berdasarkan rating toko.</p>

        <div style="overflow-x: auto; margin-top: 10px;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Nama</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Kategori</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Jam</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Rating</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$recommended): ?>
                <tr>
                  <td colspan="4" style="padding: 10px;">Belum ada toko.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recommended as $t): ?>
                  <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$t['nama']); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($t['kategori'] ?? '-')); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                      <?php
                        $b = $t['jam_buka'] ? substr((string)$t['jam_buka'], 0, 5) : '-';
                        $u = $t['jam_tutup'] ? substr((string)$t['jam_tutup'], 0, 5) : '-';
                        echo htmlspecialchars($b . ' - ' . $u);
                      ?>
                    </td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$t['rating_avg']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="list"></i></div>
        <h3>Semua Toko</h3>
        <p>Daftar toko yang terhubung ke akun mitra ini.</p>

        <div style="overflow-x: auto; margin-top: 10px;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Nama</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Kategori</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Alamat</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Rating</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$all): ?>
                <tr>
                  <td colspan="6" style="padding: 10px;">Belum ada toko.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($all as $t): ?>
                  <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo (int)$t['id']; ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$t['nama']); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($t['kategori'] ?? '-')); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$t['alamat']); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$t['rating_avg']); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo ((int)($t['status_aktif'] ?? 0) === 1) ? 'Aktif' : 'Nonaktif'; ?></td>
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
