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

    if ($action === 'create_produk') {
        $tokoId = (int)($_POST['toko_id'] ?? 0);
        $nama = trim((string)($_POST['nama'] ?? ''));
        $harga = (int)($_POST['harga'] ?? 0);
        $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
        $foto = '';

        if (isset($_FILES['foto_file']) && is_array($_FILES['foto_file'])) {
            $tmp = (string)($_FILES['foto_file']['tmp_name'] ?? '');
            $orig = (string)($_FILES['foto_file']['name'] ?? '');
            $size = (int)($_FILES['foto_file']['size'] ?? 0);
            $err = (int)($_FILES['foto_file']['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($err === UPLOAD_ERR_OK && $tmp !== '' && is_uploaded_file($tmp) && $size > 0) {
                $ext = strtolower((string)pathinfo($orig, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($ext, $allowed, true) && $size <= 2_000_000) {
                    $rootDir = dirname(__DIR__);
                    $uploadDir = $rootDir . '/uploads/products';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }

                    try {
                        $rand = bin2hex(random_bytes(4));
                    } catch (Throwable $e) {
                        $rand = (string)mt_rand(1000, 9999);
                    }

                    $fileName = 'prod_' . $mitraId . '_' . time() . '_' . $rand . '.' . $ext;
                    $dest = $uploadDir . '/' . $fileName;
                    if (@move_uploaded_file($tmp, $dest)) {
                        $foto = 'uploads/products/' . $fileName;
                    }
                }
            }
        }

        $okToko = false;
        if ($tokoId > 0) {
            $stmt = $conn->prepare('SELECT id FROM toko WHERE id = ? AND mitra_id = ? LIMIT 1');
            $stmt->bind_param('ii', $tokoId, $mitraId);
            $stmt->execute();
            $res = $stmt->get_result();
            $okToko = (bool)($res && $res->fetch_assoc());
            $stmt->close();
        }

        if ($okToko && $nama !== '' && $harga > 0) {
            $stmt = $conn->prepare('INSERT INTO produk (toko_id, nama, harga, deskripsi, foto, status_aktif) VALUES (?, ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'), 1)');
            $stmt->bind_param('isiss', $tokoId, $nama, $harga, $deskripsi, $foto);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: produk.php');
        exit();
    }

    if ($action === 'delete_produk') {
        $produkId = (int)($_POST['produk_id'] ?? 0);
        if ($produkId > 0) {
            $stmt = $conn->prepare('DELETE p FROM produk p JOIN toko t ON t.id = p.toko_id WHERE p.id = ? AND t.mitra_id = ?');
            $stmt->bind_param('ii', $produkId, $mitraId);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: produk.php');
        exit();
    }

    if ($action === 'update_produk') {
        $produkId = (int)($_POST['produk_id'] ?? 0);
        $tokoId = (int)($_POST['toko_id'] ?? 0);
        $nama = trim((string)($_POST['nama'] ?? ''));
        $harga = (int)($_POST['harga'] ?? 0);
        $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));

        $currentFoto = '';
        if ($produkId > 0) {
            $stmt = $conn->prepare('SELECT p.foto FROM produk p JOIN toko t ON t.id = p.toko_id WHERE p.id = ? AND t.mitra_id = ? LIMIT 1');
            $stmt->bind_param('ii', $produkId, $mitraId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            $currentFoto = (string)($row['foto'] ?? '');
        }

        $foto = $currentFoto;
        if (isset($_FILES['foto_file']) && is_array($_FILES['foto_file'])) {
            $tmp = (string)($_FILES['foto_file']['tmp_name'] ?? '');
            $orig = (string)($_FILES['foto_file']['name'] ?? '');
            $size = (int)($_FILES['foto_file']['size'] ?? 0);
            $err = (int)($_FILES['foto_file']['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($err === UPLOAD_ERR_OK && $tmp !== '' && is_uploaded_file($tmp) && $size > 0) {
                $ext = strtolower((string)pathinfo($orig, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($ext, $allowed, true) && $size <= 2_000_000) {
                    $rootDir = dirname(__DIR__);
                    $uploadDir = $rootDir . '/uploads/products';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }

                    try {
                        $rand = bin2hex(random_bytes(4));
                    } catch (Throwable $e) {
                        $rand = (string)mt_rand(1000, 9999);
                    }

                    $fileName = 'prod_' . $mitraId . '_' . time() . '_' . $rand . '.' . $ext;
                    $dest = $uploadDir . '/' . $fileName;
                    if (@move_uploaded_file($tmp, $dest)) {
                        $foto = 'uploads/products/' . $fileName;
                    }
                }
            }
        }

        $okProduk = false;
        if ($produkId > 0 && $tokoId > 0) {
            $stmt = $conn->prepare('SELECT p.id FROM produk p JOIN toko t ON t.id = p.toko_id WHERE p.id = ? AND t.id = ? AND t.mitra_id = ? LIMIT 1');
            $stmt->bind_param('iii', $produkId, $tokoId, $mitraId);
            $stmt->execute();
            $res = $stmt->get_result();
            $okProduk = (bool)($res && $res->fetch_assoc());
            $stmt->close();
        }

        if ($okProduk && $nama !== '' && $harga > 0) {
            $stmt = $conn->prepare('UPDATE produk SET nama = ?, harga = ?, deskripsi = NULLIF(?, \'\'), foto = NULLIF(?, \'\') WHERE id = ?');
            $stmt->bind_param('sissi', $nama, $harga, $deskripsi, $foto, $produkId);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: produk.php');
        exit();
    }
}

$stores = [];
$stmt = $conn->prepare('SELECT id, nama FROM toko WHERE mitra_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $mitraId);
$stmt->execute();
$res = $stmt->get_result();
$stores = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$products = [];
$stmt = $conn->prepare('SELECT p.id, p.toko_id, p.nama, p.harga, p.deskripsi, p.status_aktif, p.foto, t.nama AS toko_nama FROM produk p JOIN toko t ON t.id = p.toko_id WHERE t.mitra_id = ? ORDER BY p.id DESC');
$stmt->bind_param('i', $mitraId);
$stmt->execute();
$res = $stmt->get_result();
$products = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

portal_page_start('mitra', 'Produk - Mitra', 'produk');
?>
  <section class="portal">
    <div class="portal-head">
      <h1>Produk</h1>
      <p>Halo, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Mitra'); ?>. Tambahkan produk dan kelola daftar produk toko kamu.</p>
    </div>

    <div class="portal-grid">
      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="plus-circle"></i></div>
        <h3>Tambah Produk</h3>
        <p>Pilih toko, isi nama produk, dan harga.</p>

        <?php if (!$stores): ?>
          <div style="margin-top: 12px; padding: 12px; border: 1px solid #eee; border-radius: 12px;">
            Kamu belum punya toko. Buat toko dulu di menu <strong>Toko Saya</strong>.
          </div>
        <?php else: ?>
          <form method="POST" enctype="multipart/form-data" style="margin-top: 12px; max-width: 900px;">
            <input type="hidden" name="action" value="create_produk" />

            <div class="form-group">
              <label>Toko:</label>
              <select name="toko_id" required>
                <?php foreach ($stores as $s): ?>
                  <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars((string)$s['nama']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Nama Produk:</label>
              <input type="text" name="nama" required />
            </div>
            <div class="form-group">
              <label>Harga (Rp):</label>
              <input type="number" name="harga" min="1" required />
            </div>
            <div class="form-group">
              <label>Deskripsi:</label>
              <textarea name="deskripsi" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label>Foto Produk (upload):</label>
              <input type="file" name="foto_file" accept="image/png,image/jpeg,image/webp,image/gif" />
            </div>

            <button type="submit" class="btn btn-selesai">Simpan</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="portal-card" style="grid-column: 1 / -1;">
        <div class="portal-card-icon"><i data-feather="list"></i></div>
        <h3>Daftar Produk</h3>
        <p>Produk yang terhubung ke toko kamu.</p>

        <div style="overflow-x: auto; margin-top: 10px;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Foto</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Nama</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Toko</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Harga</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Status</th>
                <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ddd;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$products): ?>
                <tr>
                  <td colspan="7" style="padding: 10px;">Belum ada produk.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($products as $p): ?>
                  <?php
                    $foto = (string)($p['foto'] ?? '');
                    $fotoSrc = '';
                    if ($foto !== '') {
                        $fotoSrc = '../' . ltrim($foto, '/');
                    }
                    $pid = (int)($p['id'] ?? 0);
                    $ptokoId = (int)($p['toko_id'] ?? 0);
                    $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
                  ?>
                  <?php if ($editId === $pid): ?>
                    <tr>
                      <td colspan="7" style="padding: 12px; border-bottom: 1px solid #eee;">
                        <form method="POST" enctype="multipart/form-data" style="max-width: 900px;">
                          <input type="hidden" name="action" value="update_produk" />
                          <input type="hidden" name="produk_id" value="<?php echo $pid; ?>" />
                          <input type="hidden" name="toko_id" value="<?php echo $ptokoId; ?>" />

                          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group">
                              <label>Nama Produk:</label>
                              <input type="text" name="nama" value="<?php echo htmlspecialchars((string)($p['nama'] ?? '')); ?>" required />
                            </div>
                            <div class="form-group">
                              <label>Harga (Rp):</label>
                              <input type="number" name="harga" min="1" value="<?php echo (int)($p['harga'] ?? 0); ?>" required />
                            </div>
                          </div>

                          <div class="form-group">
                            <label>Deskripsi:</label>
                            <textarea name="deskripsi" rows="3"><?php echo htmlspecialchars((string)($p['deskripsi'] ?? '')); ?></textarea>
                          </div>

                          <div class="form-group">
                            <label>Foto Produk (opsional):</label>
                            <?php if ($fotoSrc !== ''): ?>
                              <div style="margin-top: 8px; margin-bottom: 10px;">
                                <img src="<?php echo htmlspecialchars($fotoSrc); ?>" alt="Foto" style="width: 84px; height: 84px; border-radius: 18px; object-fit: cover; border: 1px solid rgba(16, 24, 40, 0.10);" />
                              </div>
                            <?php endif; ?>
                            <input type="file" name="foto_file" accept="image/png,image/jpeg,image/webp,image/gif" />
                          </div>

                          <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-selesai">Simpan</button>
                            <a href="produk.php" class="btn-pesan" style="background: rgba(16, 24, 40, 0.08); color: #101828;">Batal</a>
                          </div>
                        </form>
                      </td>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo $pid; ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                      <?php if ($fotoSrc !== ''): ?>
                        <img src="<?php echo htmlspecialchars($fotoSrc); ?>" alt="Foto" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid #eee;" />
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$p['nama']); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string)$p['toko_nama']); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Rp <?php echo number_format((int)$p['harga'], 0, ',', '.'); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo ((int)($p['status_aktif'] ?? 0) === 1) ? 'Aktif' : 'Nonaktif'; ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a class="btn-pesan" href="produk.php?edit=<?php echo $pid; ?>" style="background: rgba(16, 24, 40, 0.08); color: #101828;">Edit</a>
                        <form method="POST" onsubmit="return confirm('Hapus produk ini?');" style="display:inline;">
                          <input type="hidden" name="action" value="delete_produk" />
                          <input type="hidden" name="produk_id" value="<?php echo $pid; ?>" />
                          <button type="submit" class="btn-pesan" style="background: #ef4444;">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

<?php portal_page_end(); ?>
