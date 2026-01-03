<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/portal_layout.php';

require_role(['mitra']);

$conn = db();
$mitraId = (int)($_SESSION['auth']['data']['mitra_id'] ?? 0);
$displayName = auth_display_name();
$username = (string)($_SESSION['auth']['data']['username'] ?? '');
$email = (string)($_SESSION['auth']['data']['email'] ?? '');

$saved = isset($_GET['saved']) ? (string)$_GET['saved'] : '';

$mitra = null;
if ($mitraId > 0) {
    $stmt = $conn->prepare('SELECT id, nama, pemilik, whatsapp, email, bidang, notelepon, alamat, sosial_media, logo, status_pengajuan, status_aktif FROM mitra WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $mitraId);
    $stmt->execute();
    $res = $stmt->get_result();
    $mitra = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string)$_POST['action'] : '';
    if ($action === 'update_profile' && $mitraId > 0) {
        $nama = trim((string)($_POST['nama'] ?? ''));
        $pemilik = trim((string)($_POST['pemilik'] ?? ''));
        $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
        $bidang = trim((string)($_POST['bidang'] ?? ''));
        $notelepon = trim((string)($_POST['notelepon'] ?? ''));
        $alamat = trim((string)($_POST['alamat'] ?? ''));
        $sosialMedia = trim((string)($_POST['sosial_media'] ?? ''));

        $logoPath = (string)($mitra['logo'] ?? '');
        if (isset($_FILES['logo_file']) && is_array($_FILES['logo_file'])) {
            $tmp = (string)($_FILES['logo_file']['tmp_name'] ?? '');
            $orig = (string)($_FILES['logo_file']['name'] ?? '');
            $size = (int)($_FILES['logo_file']['size'] ?? 0);
            $err = (int)($_FILES['logo_file']['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($err === UPLOAD_ERR_OK && $tmp !== '' && is_uploaded_file($tmp) && $size > 0) {
                $ext = strtolower((string)pathinfo($orig, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed, true) && $size <= 2_000_000) {
                    $rootDir = dirname(__DIR__);
                    $uploadDir = $rootDir . '/uploads/mitra/logo';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }

                    try {
                        $rand = bin2hex(random_bytes(4));
                    } catch (Throwable $e) {
                        $rand = (string)mt_rand(1000, 9999);
                    }

                    $fileName = 'mitra_logo_' . $mitraId . '_' . time() . '_' . $rand . '.' . $ext;
                    $dest = $uploadDir . '/' . $fileName;
                    if (@move_uploaded_file($tmp, $dest)) {
                        $logoPath = 'uploads/mitra/logo/' . $fileName;
                    }
                }
            }
        }

        $stmt = $conn->prepare('UPDATE mitra SET nama = ?, pemilik = ?, whatsapp = ?, bidang = ?, notelepon = ?, alamat = ?, sosial_media = ?, logo = ? WHERE id = ?');
        $stmt->bind_param('ssssssssi', $nama, $pemilik, $whatsapp, $bidang, $notelepon, $alamat, $sosialMedia, $logoPath, $mitraId);
        $stmt->execute();
        $stmt->close();

        header('Location: profil.php?saved=1');
        exit();
    }
}

portal_page_start('mitra', 'Profil - Mitra', 'profil');
?>
  <section class="portal">
    <div class="portal-head">
      <h1>Profil Mitra</h1>
      <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Mitra'); ?>. Informasi akun dan data mitra kamu.</p>
    </div>

    <div class="portal-grid">
      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="user"></i></div>
        <h3>Akun</h3>
        <p>Informasi akun login.</p>

        <div style="margin-top: 10px; overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <tbody>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; width: 220px;">Username</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($username !== '' ? $username : '-'); ?></td>
              </tr>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">Email</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($email !== '' ? $email : '-'); ?></td>
              </tr>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">Mitra ID</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo (int)$mitraId; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="info"></i></div>
        <h3>Data Mitra</h3>
        <p>Data dari tabel mitra.</p>

        <?php if ($saved === '1'): ?>
          <div style="margin-top: 12px; padding: 12px; border: 1px solid #bbf7d0; background: #f0fdf4; border-radius: 12px; color: #166534; font-weight: 800;">
            Profil berhasil diperbarui.
          </div>
        <?php endif; ?>

        <?php if ($mitra): ?>
          <?php
            $logo = (string)($mitra['logo'] ?? '');
            $logoSrc = '';
            if ($logo !== '') {
                $logoSrc = '../' . ltrim($logo, '/');
            }
          ?>
          <form method="POST" enctype="multipart/form-data" style="margin-top: 12px; max-width: 900px;">
            <input type="hidden" name="action" value="update_profile" />

            <div class="form-group">
              <label>Nama Mitra:</label>
              <input type="text" name="nama" value="<?php echo htmlspecialchars((string)($mitra['nama'] ?? '')); ?>" required />
            </div>

            <div class="form-group">
              <label>Nama Pemilik:</label>
              <input type="text" name="pemilik" value="<?php echo htmlspecialchars((string)($mitra['pemilik'] ?? '')); ?>" required />
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
              <div class="form-group">
                <label>WhatsApp:</label>
                <input type="text" name="whatsapp" value="<?php echo htmlspecialchars((string)($mitra['whatsapp'] ?? '')); ?>" required />
              </div>
              <div class="form-group">
                <label>No Telepon:</label>
                <input type="text" name="notelepon" value="<?php echo htmlspecialchars((string)($mitra['notelepon'] ?? '')); ?>" required />
              </div>
            </div>

            <div class="form-group">
              <label>Bidang:</label>
              <input type="text" name="bidang" value="<?php echo htmlspecialchars((string)($mitra['bidang'] ?? '')); ?>" required />
            </div>

            <div class="form-group">
              <label>Alamat:</label>
              <input type="text" name="alamat" value="<?php echo htmlspecialchars((string)($mitra['alamat'] ?? '')); ?>" required />
            </div>

            <div class="form-group">
              <label>Sosial Media:</label>
              <input type="text" name="sosial_media" value="<?php echo htmlspecialchars((string)($mitra['sosial_media'] ?? '')); ?>" required />
            </div>

            <div class="form-group">
              <label>Logo (opsional):</label>
              <?php if ($logoSrc !== ''): ?>
                <div style="margin-top: 8px; margin-bottom: 10px;">
                  <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo" style="width: 84px; height: 84px; border-radius: 18px; object-fit: cover; border: 1px solid rgba(16, 24, 40, 0.10);" />
                </div>
              <?php endif; ?>
              <input type="file" name="logo_file" accept="image/png,image/jpeg,image/webp" />
            </div>

            <button type="submit" class="btn btn-selesai">Simpan Perubahan</button>
          </form>
        <?php endif; ?>

        <div style="margin-top: 10px; overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <tbody>
              <?php if (!$mitra): ?>
                <tr>
                  <td style="padding: 10px;">Data mitra tidak ditemukan.</td>
                </tr>
              <?php else: ?>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee; width: 220px;">Nama</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($mitra['nama'] ?? '-')); ?></td>
                </tr>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;">Pemilik</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($mitra['pemilik'] ?? '-')); ?></td>
                </tr>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;">WhatsApp</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($mitra['whatsapp'] ?? '-')); ?></td>
                </tr>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;">Bidang</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($mitra['bidang'] ?? '-')); ?></td>
                </tr>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;">Alamat</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($mitra['alamat'] ?? '-')); ?></td>
                </tr>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;">Status Pengajuan</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)($mitra['status_pengajuan'] ?? '-')); ?></td>
                </tr>
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;">Status Aktif</td>
                  <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo ((int)($mitra['status_aktif'] ?? 0) === 1) ? 'Aktif' : 'Nonaktif'; ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

<?php portal_page_end(); ?>
