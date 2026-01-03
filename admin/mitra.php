<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/util.php';
require_role(['admin']);

$conn = db();

$flash = null;
if (isset($_SESSION['flash_admin_mitra'])) {
    $flash = (string)$_SESSION['flash_admin_mitra'];
    unset($_SESSION['flash_admin_mitra']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string)$_POST['action'] : '';

    if ($action === 'approve') {
        $mitraId = (int)($_POST['mitra_id'] ?? 0);
        if ($mitraId > 0) {
            $stmt = $conn->prepare('SELECT id, nama, email, status_pengajuan FROM mitra WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $mitraId);
            $stmt->execute();
            $res = $stmt->get_result();
            $mitra = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if ($mitra && (string)$mitra['status_pengajuan'] !== 'approved') {
                $stmt = $conn->prepare('SELECT id FROM mitra_accounts WHERE mitra_id = ? LIMIT 1');
                $stmt->bind_param('i', $mitraId);
                $stmt->execute();
                $res = $stmt->get_result();
                $exists = $res ? $res->fetch_assoc() : null;
                $stmt->close();

                if (!$exists) {
                    $username = unique_username($conn, 'mitra_accounts', (string)$mitra['nama']);
                    $plain = generate_temp_password(10);
                    $hash = $plain;

                    $conn->begin_transaction();
                    try {
                        $stmt = $conn->prepare("UPDATE mitra SET status_pengajuan = 'approved', status_aktif = 1 WHERE id = ?");
                        $stmt->bind_param('i', $mitraId);
                        $stmt->execute();
                        $stmt->close();

                        $email = $mitra['email'] ?? null;
                        if ($email === '') {
                            $email = null;
                        }

                        $stmt = $conn->prepare('INSERT INTO mitra_accounts (mitra_id, username, email, password_hash, status_aktif) VALUES (?, ?, ?, ?, 1)');
                        $stmt->bind_param('isss', $mitraId, $username, $email, $hash);
                        $stmt->execute();
                        $stmt->close();

                        $conn->commit();
                        $_SESSION['flash_admin_mitra'] = 'Mitra disetujui. Akun dibuat: username=' . $username . ' | password=' . $plain;
                    } catch (Throwable $e) {
                        $conn->rollback();
                        $_SESSION['flash_admin_mitra'] = 'Gagal approve mitra: ' . $e->getMessage();
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE mitra SET status_pengajuan = 'approved', status_aktif = 1 WHERE id = ?");
                    $stmt->bind_param('i', $mitraId);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['flash_admin_mitra'] = 'Mitra disetujui. Akun sudah ada.';
                }
            }
        }

        header('Location: mitra.php');
        exit();
    }

    if ($action === 'reject') {
        $mitraId = (int)($_POST['mitra_id'] ?? 0);
        if ($mitraId > 0) {
            $stmt = $conn->prepare("UPDATE mitra SET status_pengajuan = 'rejected', status_aktif = 0 WHERE id = ?");
            $stmt->bind_param('i', $mitraId);
            $stmt->execute();
            $stmt->close();
            $_SESSION['flash_admin_mitra'] = 'Mitra ditolak.';
        }

        header('Location: mitra.php');
        exit();
    }

    if ($action === 'reset_password') {
        $accountId = (int)($_POST['account_id'] ?? 0);
        $password = (string)($_POST['password'] ?? '');

        if ($accountId > 0 && $password !== '') {
            $hash = $password;
            $stmt = $conn->prepare('UPDATE mitra_accounts SET password_hash = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $accountId);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: mitra.php');
        exit();
    }

    if ($action === 'toggle_active') {
        $accountId = (int)($_POST['account_id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        if ($active !== 0 && $active !== 1) {
            $active = 0;
        }

        if ($accountId > 0) {
            $mitraId = 0;
            $stmt = $conn->prepare('SELECT mitra_id FROM mitra_accounts WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $accountId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            $mitraId = (int)($row['mitra_id'] ?? 0);

            $stmt = $conn->prepare('UPDATE mitra_accounts SET status_aktif = ? WHERE id = ?');
            $stmt->bind_param('ii', $active, $accountId);
            $stmt->execute();
            $stmt->close();

            if ($mitraId > 0) {
                $stmt = $conn->prepare('UPDATE mitra SET status_aktif = ? WHERE id = ?');
                $stmt->bind_param('ii', $active, $mitraId);
                $stmt->execute();
                $stmt->close();
            }

            $_SESSION['flash_admin_mitra'] = $active === 1 ? 'Mitra diaktifkan.' : 'Mitra dinonaktifkan.';
        }

        header('Location: mitra.php');
        exit();
    }
}

$sql = "
SELECT
  m.id AS mitra_id,
  m.nama AS nama_usaha,
  m.pemilik,
  m.whatsapp,
  m.notelepon,
  m.email,
  m.alamat,
  m.status_pengajuan,
  m.status_aktif AS mitra_status_aktif,
  a.id AS account_id,
  a.username,
  a.status_aktif AS account_status_aktif,
  a.last_login_at
FROM mitra m
LEFT JOIN mitra_accounts a ON a.mitra_id = m.id
ORDER BY m.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<?php
admin_page_start('Mitra', 'mitra');
?>

<?php if ($flash): ?>
  <div class="admin-card" style="margin-bottom: 16px;">
    <div class="admin-card-header">
      <div>
        <div class="admin-card-title">Info</div>
        <div class="admin-card-subtitle"><?php echo htmlspecialchars($flash); ?></div>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Pengajuan Mitra</div>
      <div class="admin-card-subtitle">Approve untuk membuat akun otomatis, atau reset password jika akun sudah ada.</div>
    </div>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Usaha</th>
          <th>Pemilik</th>
          <th>WhatsApp</th>
          <th>Telepon</th>
          <th>Email</th>
          <th>Alamat</th>
          <th>Status</th>
          <th>Akun Aktif</th>
          <th>Akun</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <?php
            $status = (string)($r['status_pengajuan'] ?? '-');
            $badgeClass = 'badge-neutral';
            if ($status === 'pending') {
                $badgeClass = 'badge-warning';
            } elseif ($status === 'approved') {
                $badgeClass = 'badge-success';
            } elseif ($status === 'rejected') {
                $badgeClass = 'badge-danger';
            }

            $accountActive = (int)($r['account_status_aktif'] ?? 0);
            $accountActiveText = '-';
            $accountActiveBadge = 'badge-neutral';
            if ($r['account_id']) {
                $accountActiveText = $accountActive === 1 ? 'Aktif' : 'Nonaktif';
                $accountActiveBadge = $accountActive === 1 ? 'badge-success' : 'badge-danger';
            }
          ?>
          <tr>
            <td><?php echo (int)$r['mitra_id']; ?></td>
            <td><?php echo htmlspecialchars((string)$r['nama_usaha']); ?></td>
            <td><?php echo htmlspecialchars((string)$r['pemilik']); ?></td>
            <td><?php echo htmlspecialchars((string)($r['whatsapp'] ?? '-')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['notelepon'] ?? '-')); ?></td>
            <td><?php echo htmlspecialchars((string)$r['email']); ?></td>
            <td><?php echo htmlspecialchars((string)($r['alamat'] ?? '-')); ?></td>
            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
            <td><span class="badge <?php echo $accountActiveBadge; ?>"><?php echo htmlspecialchars($accountActiveText); ?></span></td>
            <td>
              <?php if ($r['account_id']): ?>
                <?php echo htmlspecialchars((string)$r['username']); ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td>
              <?php if (($r['status_pengajuan'] ?? '') === 'pending'): ?>
                <form method="POST" class="form-inline">
                  <input type="hidden" name="mitra_id" value="<?php echo (int)$r['mitra_id']; ?>" />
                  <button type="submit" name="action" value="approve" class="btn btn-primary">Approve & Buat Akun</button>
                  <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                </form>
              <?php elseif ($r['account_id']): ?>
                <form method="POST" class="form-inline">
                  <input type="hidden" name="action" value="reset_password" />
                  <input type="hidden" name="account_id" value="<?php echo (int)$r['account_id']; ?>" />
                  <input type="password" name="password" placeholder="password baru" required class="input" />
                  <button type="submit" class="btn btn-secondary">Reset</button>
                </form>
                <form method="POST" class="form-inline" style="margin-top: 8px;">
                  <input type="hidden" name="action" value="toggle_active" />
                  <input type="hidden" name="account_id" value="<?php echo (int)$r['account_id']; ?>" />
                  <?php if ($accountActive === 1): ?>
                    <input type="hidden" name="active" value="0" />
                    <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                  <?php else: ?>
                    <input type="hidden" name="active" value="1" />
                    <button type="submit" class="btn btn-primary">Aktifkan</button>
                  <?php endif; ?>
                </form>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_page_end(); ?>
