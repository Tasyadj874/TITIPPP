<?php
require_once __DIR__ . '/includes/auth.php';

auth_start();

$conn = db();

$tokoId = (int)($_GET['id'] ?? 0);
$selectedProdukId = (int)($_GET['produk_id'] ?? 0);
$success = (string)($_GET['success'] ?? '') === '1';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_role(['user']);

    $tokoId = (int)($_POST['toko_id'] ?? 0);
    $produkId = (int)($_POST['produk_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);

    $alamatAntar = trim((string)($_POST['alamat_antar'] ?? ''));
    $namaPenerima = trim((string)($_POST['nama_penerima'] ?? ''));
    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
    $keterangan = trim((string)($_POST['keterangan'] ?? ''));

    $metode = (string)($_POST['metode_pembayaran'] ?? 'Transfer');
    if (!in_array($metode, ['Transfer', 'COD'], true)) {
        $metode = 'Transfer';
    }

    if ($tokoId <= 0 || $produkId <= 0 || $qty <= 0) {
        $error = 'Data pesanan tidak valid.';
    } elseif ($alamatAntar === '' || $namaPenerima === '' || $whatsapp === '') {
        $error = 'Lengkapi alamat antar, nama penerima, dan WhatsApp.';
    } else {
        $stmt = $conn->prepare('SELECT p.id, p.nama, p.harga, t.id AS toko_id, t.alamat AS alamat_jemput FROM produk p JOIN toko t ON t.id = p.toko_id WHERE p.id = ? AND t.id = ? AND p.status_aktif = 1 AND t.status_aktif = 1 LIMIT 1');
        $stmt->bind_param('ii', $produkId, $tokoId);
        $stmt->execute();
        $res = $stmt->get_result();
        $produk = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$produk) {
            $error = 'Produk tidak ditemukan atau toko tidak aktif.';
        } else {
            $userId = (int)(auth_id() ?? 0);
            if ($userId <= 0) {
                $error = 'User tidak valid.';
            } else {
                $alamatJemput = (string)($produk['alamat_jemput'] ?? '');
                $namaProduk = (string)($produk['nama'] ?? '');
                $harga = (int)($produk['harga'] ?? 0);

                $conn->begin_transaction();
                try {
                    $status = 'dibuat';
                    $stmt = $conn->prepare("INSERT INTO pesanan (user_id, toko_id, alamat_jemput, alamat_antar, nama_penerima, whatsapp, keterangan, metode_pembayaran, status) VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?, ?)");
                    $stmt->bind_param('iisssssss', $userId, $tokoId, $alamatJemput, $alamatAntar, $namaPenerima, $whatsapp, $keterangan, $metode, $status);
                    $stmt->execute();
                    $pesananId = (int)$conn->insert_id;
                    $stmt->close();

                    if ($pesananId <= 0) {
                        throw new RuntimeException('Gagal membuat pesanan.');
                    }

                    $stmt = $conn->prepare('INSERT INTO pesanan_items (pesanan_id, produk_id, nama_produk, qty, harga) VALUES (?, ?, ?, ?, ?)');
                    $stmt->bind_param('iisii', $pesananId, $produkId, $namaProduk, $qty, $harga);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit();

                    header('Location: toko.php?id=' . urlencode((string)$tokoId) . '&success=1');
                    exit();
                } catch (Throwable $e) {
                    $conn->rollback();
                    $error = 'Gagal membuat pesanan. Coba lagi.';
                }
            }
        }
    }
}

$toko = null;
$produkList = [];

if ($tokoId > 0) {
    $stmt = $conn->prepare('SELECT id, nama, kategori, alamat, jam_buka, jam_tutup, logo, rating_avg FROM toko WHERE id = ? AND status_aktif = 1 LIMIT 1');
    $stmt->bind_param('i', $tokoId);
    $stmt->execute();
    $res = $stmt->get_result();
    $toko = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($toko) {
        $stmt = $conn->prepare('SELECT id, nama, harga, deskripsi, foto FROM produk WHERE toko_id = ? AND status_aktif = 1 ORDER BY id DESC');
        $stmt->bind_param('i', $tokoId);
        $stmt->execute();
        $res = $stmt->get_result();
        $produkList = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }
}

$selectedProduk = null;
if ($selectedProdukId > 0) {
    foreach ($produkList as $p) {
        if ((int)($p['id'] ?? 0) === $selectedProdukId) {
            $selectedProduk = $p;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Toko - TITIP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap"
      rel="stylesheet"
    />

    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <div class="red-navbar">
      <nav class="navbar">
        <div class="logo-container">
          <img src="images/LOGO TITIP.png" alt="Logo TITIP" class="logo" />
        </div>
        <div class="navbar-nav">
          <a href="index.php#home">Home</a>
          <a href="mitra-titip.php">Mitra</a>
          <a href="index.php#contact">Kontak</a>
        </div>

        <div class="navbar-extra">
          <?php if (auth_check()): ?>
            <a href="#login-options" id="login" style="display: inline-flex; align-items: center; gap: 10px;">
              <i data-feather="users"></i>
              <span style="font-size: 16px; font-weight: 800; color: #fff; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <?php echo htmlspecialchars(auth_display_name()); ?>
              </span>
            </a>
          <?php else: ?>
            <a href="auth/login.php?role=user" id="login"><i data-feather="users"></i></a>
          <?php endif; ?>
          <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
          <div class="dropdown" id="login-options">
            <?php if (auth_check()): ?>
              <?php if (auth_role() === 'admin'): ?>
                <a href="laman-admin.php">Dashboard</a>
              <?php elseif (auth_role() === 'mitra'): ?>
                <a href="laman-mitra.php">Dashboard</a>
              <?php elseif (auth_role() === 'kurir'): ?>
                <a href="laman-kurir.php">Dashboard</a>
              <?php elseif (auth_role() === 'user'): ?>
                <a href="user/index.php">Dashboard</a>
              <?php endif; ?>
              <a href="logout.php?role=<?php echo urlencode((string)(auth_role() ?? 'user')); ?>">Logout</a>
            <?php else: ?>
              <a href="auth/login.php?role=user">Login User</a>
              <a href="auth/login.php?role=kurir">Login Kurir</a>
              <a href="auth/login.php?role=mitra">Login Mitra</a>
              <a href="auth/login.php?role=admin">Login Admin</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </div>

    <section class="mitra-titip toko-page">
      <h2>DETAIL TOKO</h2>

      <div class="container">
        <?php if (!$toko): ?>
          <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">
            Toko tidak ditemukan atau tidak aktif.
          </div>
        <?php else: ?>
          <?php
            $logo = (string)($toko['logo'] ?? '');
            if ($logo === '') {
                $logo = 'images/LOGO TITIP.png';
            }
            $b = $toko['jam_buka'] ? substr((string)$toko['jam_buka'], 0, 5) : '-';
            $u = $toko['jam_tutup'] ? substr((string)$toko['jam_tutup'], 0, 5) : '-';
          ?>

          <?php if ($success): ?>
            <div style="padding: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 12px; margin-bottom: 14px;">
              Pesanan berhasil dibuat. Silakan tunggu kurir mengambil job.
            </div>
          <?php endif; ?>

          <?php if ($error !== ''): ?>
            <div style="padding: 14px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; margin-bottom: 14px;">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <div class="toko-detail-card">
            <div class="toko-detail-media">
              <img src="<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars((string)$toko['nama']); ?>" />
            </div>
            <div class="toko-detail-info">
              <h3><?php echo htmlspecialchars((string)$toko['nama']); ?></h3>
              <div class="toko-detail-meta">
                <p><?php echo htmlspecialchars((string)($toko['kategori'] ?? '')); ?></p>
                <p>Jam Operasi: <?php echo htmlspecialchars($b . ' - ' . $u); ?></p>
                <p class="rating">Rating: <?php echo htmlspecialchars((string)($toko['rating_avg'] ?? '0')); ?></p>
                <p>Alamat: <?php echo htmlspecialchars((string)($toko['alamat'] ?? '')); ?></p>
              </div>
            </div>
          </div>

          <div class="mitra-block" style="margin-top: 22px;">
            <div class="mitra-block-head">
              <h3>Menu / Produk</h3>
              <p>Pilih menu lalu buat pesanan.</p>
            </div>

            <div class="toko-menu-grid">
              <?php if (!$produkList): ?>
                <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">Belum ada produk di toko ini.</div>
              <?php else: ?>
                <?php foreach ($produkList as $p): ?>
                  <?php
                    $foto = (string)($p['foto'] ?? '');
                    if ($foto === '') {
                        $foto = 'images/Kurir logo.png';
                    }
                    $pid = (int)($p['id'] ?? 0);
                  ?>
                  <div class="product-box">
                    <img src="<?php echo htmlspecialchars($foto); ?>" alt="<?php echo htmlspecialchars((string)$p['nama']); ?>" />
                    <div class="product-info">
                      <h3><?php echo htmlspecialchars((string)$p['nama']); ?></h3>
                      <p><?php echo htmlspecialchars((string)($p['deskripsi'] ?? '')); ?></p>
                      <p style="font-weight: 900; color: #111;">Rp <?php echo number_format((int)($p['harga'] ?? 0), 0, ',', '.'); ?></p>
                      <a class="btn-pesan" href="toko.php?id=<?php echo urlencode((string)$tokoId); ?>&produk_id=<?php echo urlencode((string)$pid); ?>#form-pesan">Pilih</a>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <div id="form-pesan" class="mitra-block" style="margin-top: 28px;">
            <div class="mitra-block-head">
              <h3>Buat Pesanan</h3>
              <p>Isi data penerima dan detail antar.</p>
            </div>

            <?php if (!auth_check() || auth_role() !== 'user'): ?>
              <div style="padding: 14px; background: #fff; border: 1px solid #eee; border-radius: 12px;">
                Untuk memesan, silakan <a href="auth/login.php?role=user" style="color: var(--primary-dark); font-weight: 900;">login sebagai user</a>.
              </div>
            <?php else: ?>
              <form method="POST" class="toko-order-form">
                <input type="hidden" name="toko_id" value="<?php echo (int)$tokoId; ?>" />

                <div class="toko-order-grid">
                  <div>
                    <label style="display:block; font-weight: 900; margin-bottom: 6px;">Produk</label>
                    <select name="produk_id" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;">
                      <option value="">Pilih produk</option>
                      <?php foreach ($produkList as $p): ?>
                        <?php $pid = (int)($p['id'] ?? 0); ?>
                        <option value="<?php echo $pid; ?>" <?php echo ($selectedProduk && (int)($selectedProduk['id'] ?? 0) === $pid) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars((string)$p['nama']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div>
                    <label style="display:block; font-weight: 900; margin-bottom: 6px;">Qty</label>
                    <input type="number" name="qty" min="1" value="1" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;" />
                  </div>
                </div>

                <div class="toko-order-grid" style="margin-top: 12px;">
                  <div>
                    <label style="display:block; font-weight: 900; margin-bottom: 6px;">Nama Penerima</label>
                    <input type="text" name="nama_penerima" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;" />
                  </div>
                  <div>
                    <label style="display:block; font-weight: 900; margin-bottom: 6px;">WhatsApp</label>
                    <input type="text" name="whatsapp" required placeholder="contoh: 08xxxxxxxxxx" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;" />
                  </div>
                </div>

                <div style="margin-top: 12px;">
                  <label style="display:block; font-weight: 900; margin-bottom: 6px;">Alamat Antar</label>
                  <input type="text" name="alamat_antar" required placeholder="Alamat lengkap" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;" />
                </div>

                <div style="margin-top: 12px;">
                  <label style="display:block; font-weight: 900; margin-bottom: 6px;">Keterangan</label>
                  <textarea name="keterangan" rows="3" placeholder="Catatan, patokan, dsb" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;"></textarea>
                </div>

                <div style="margin-top: 12px;">
                  <label style="display:block; font-weight: 900; margin-bottom: 6px;">Metode Pembayaran</label>
                  <select name="metode_pembayaran" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd;">
                    <option value="Transfer">Transfer</option>
                    <option value="COD">COD</option>
                  </select>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 14px;">Buat Pesanan</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <script>feather.replace();</script>
    <script src="js/script.js?v=20251230"></script>
  </body>
</html>
