<?php
session_start();

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$namaLogin = $isLoggedIn ? (string) ($_SESSION['nama'] ?? '') : '';
$roleLogin = $isLoggedIn ? (string) ($_SESSION['role'] ?? 'user') : 'user';

$dashboardUrl = 'index.php';
if ($roleLogin === 'admin') {
    $dashboardUrl = 'admin/dashboard.php';
} elseif ($roleLogin === 'mitra') {
    $dashboardUrl = 'mitra-titip.php';
} elseif ($roleLogin === 'pasukan' || $roleLogin === 'kurir') {
    $dashboardUrl = 'admin/pasukan.php';
}

require 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'ID tidak valid';
    exit;
}

$stmt = mysqli_prepare($con, 'SELECT * FROM umkm_profile WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mitra = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$mitra) {
    http_response_code(404);
    echo 'Data mitra tidak ditemukan';
    exit;
}

$rawLogo = $mitra['logo'] ?? '';
if (!empty($rawLogo)) {
    if (strpos($rawLogo, 'uploads') !== false || strpos($rawLogo, '/') !== false) {
        $logoPath = $rawLogo;
    } else {
        $logoPath = 'uploads/logo/' . $rawLogo;
    }
} else {
    $logoPath = file_exists(__DIR__ . '/images/default_logo.png') ? 'images/default_logo.png' : 'images/LOGO TITIP.png';
}

$no_wa = preg_replace('/\D+/', '', $mitra['no_telepon_wa'] ?? '');
$wa_link = $no_wa ? 'https://wa.me/' . $no_wa : '#';
$no_wa_owner = preg_replace('/\D+/', '', $mitra['no_telepon_pemilik'] ?? '');
$wa_link_owner = $no_wa_owner ? 'https://wa.me/' . $no_wa_owner : '#';
$email = htmlspecialchars($mitra['email_pemilik'] ?? '-');

$raw_social = trim((string)($mitra['social_media'] ?? ''));
$ig_handle = '';
$ig_url = '';
if ($raw_social !== '') {
    $candidate = $raw_social;
    if (stripos($candidate, 'instagram.com') !== false) {
        if (stripos($candidate, 'http') !== 0) { $candidate = 'https://' . $candidate; }
        $path = parse_url($candidate, PHP_URL_PATH);
        $path = trim((string)$path, "/ \t\n\r\0\x0B");
        if ($path !== '') {
            $segments = explode('/', $path);
            $user = ltrim($segments[0] ?? '', '@');
            $user = preg_replace('/\s+/', '', $user);
            $ig_handle = $user;
            if ($ig_handle !== '') { $ig_url = 'https://instagram.com/' . $ig_handle; }
        } else {
            $ig_url = 'https://instagram.com/';
        }
    } else {
        $user = ltrim($candidate, '@');
        $user = preg_replace('/\s+/', '', $user);
        $user = trim($user, '/');
        $ig_handle = $user;
        if ($ig_handle !== '') { $ig_url = 'https://instagram.com/' . $ig_handle; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Mitra | <?php echo htmlspecialchars($mitra['nama_usaha'] ?? ''); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="css/mitra.css" />
    <style>
        .detail-wrapper{max-width:1040px;margin:8rem auto 4rem;padding:1rem}
        .detail-breadcrumb{margin-bottom:1rem}
        .detail-breadcrumb a{color:#666;text-decoration:none}
        .detail-breadcrumb a:hover{color:#000;text-decoration:underline}
        .detail-card{display:flex;flex-wrap:wrap;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:.75rem;box-shadow:0 6px 16px rgba(0,0,0,.08);overflow:hidden}
        .detail-image{flex:0 0 360px;max-width:360px;background:#fafafa;display:flex;align-items:center;justify-content:center}
        .detail-image img{width:100%;height:100%;object-fit:cover}
        .detail-body{flex:1;padding:1.5rem}
        .detail-body h1{font-size:2.2rem;margin-bottom:.5rem}
        .badge{display:inline-block;background:#fee2e2;color:#b91c1c;padding:.25rem .6rem;border-radius:999px;font-size:.85rem;margin-bottom:.75rem}
        .detail-meta-list{list-style:none;padding:0;margin:1rem 0 0;display:grid;grid-template-columns:1fr;gap:.45rem;font-size:1rem}
        .detail-meta-list li{display:flex;align-items:center;gap:.6rem;color:#444}
        .detail-meta-list i{width:18px;height:18px}
        .detail-actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.25rem}
        .btn-outline{border:1px solid #ddd;padding:.55rem 1rem;border-radius:.35rem;text-decoration:none;color:#333}
        .btn-outline.primary{border-color:var(--primary);color:var(--primary)}
        .btn-primary{background:var(--primary);color:#fff !important;border:none;padding:.6rem 1.05rem;border-radius:.35rem;text-decoration:none}
    </style>
</head>
<body>
    <div class="white-navbar">
        <div class="logo-container">
            <img src="images/LOGO TITIP.png" alt="Logo TITIP" class="logo" />
        </div>
    </div>
    <div class="red-navbar">
        <nav class="navbar">
            <div class="navbar-nav">
                <a href="index.php#home">Home</a>
                <a href="index.php#layanan">Layanan</a>
                <a href="index.php#about">Tentang Kami</a>
                <a href="index.php#gabung-mitra">Gabung Mitra</a>
                <a href="mitra-titip.php">Mitra</a>
                <a href="index.php#contact">Kontak</a>
            </div>
            <div class="navbar-extra">
                <a href="#" id="login" class="account-trigger">
                    <?php if ($isLoggedIn): ?>
                        <span class="account-name"><?php echo htmlspecialchars($namaLogin !== '' ? $namaLogin : 'Akun'); ?></span>
                    <?php endif; ?>
                    <i data-feather="users"></i>
                </a>
                <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
                <div class="dropdown" id="login-options">
                    <?php if (!$isLoggedIn): ?>
                        <a href="user/login.php">Login</a>
                        <a href="user/register.php">Daftar</a>
                    <?php else: ?>
                        <div class="dropdown-header">
                            <div class="dropdown-name"><?php echo htmlspecialchars($namaLogin !== '' ? $namaLogin : 'Akun'); ?></div>
                            <div class="dropdown-role"><?php echo htmlspecialchars($roleLogin); ?></div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo htmlspecialchars($dashboardUrl); ?>">Dashboard</a>
                        <?php if ($roleLogin === 'user'): ?>
                            <a href="user/logout.php?redirect=<?php echo urlencode('../mitra-detail.php?id=' . (string) $id); ?>">Logout</a>
                        <?php else: ?>
                            <a href="admin/logout.php?redirect=<?php echo urlencode('../mitra-detail.php?id=' . (string) $id); ?>">Logout</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>

    <div class="detail-wrapper">
        <div class="detail-breadcrumb"><a href="mitra-titip.php">&laquo; Kembali ke Daftar Mitra</a></div>
        <div class="detail-card">
            <div class="detail-image">
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo">
            </div>
            <div class="detail-body">
                <h1><?php echo htmlspecialchars($mitra['nama_usaha'] ?? '-'); ?></h1>
                <div class="badge">Bidang: <?php echo htmlspecialchars($mitra['bidang_usaha'] ?? '-'); ?></div>
                <ul class="detail-meta-list">
                    <li><i data-feather="map-pin"></i><span><?php echo htmlspecialchars($mitra['alamat'] ?? '-'); ?></span></li>
                    <li><i data-feather="user"></i><span>Pemilik: <?php echo htmlspecialchars($mitra['nama_pemilik'] ?? '-'); ?></span></li>
                    <li><i data-feather="phone"></i><span>Telepon/WA Toko: <?php echo htmlspecialchars($mitra['no_telepon_wa'] ?? '-'); ?></span></li>
                    <li><i data-feather="smartphone"></i><span>WA Pemilik: <?php echo htmlspecialchars($mitra['no_telepon_pemilik'] ?? '-'); ?></span></li>
                    <li><i data-feather="mail"></i><span>Email: <?php echo $email; ?></span></li>
                    <li><i data-feather="instagram"></i><span>Instagram: <?php echo $ig_handle !== '' ? '@' . htmlspecialchars($ig_handle) : '-'; ?></span></li>
                </ul>
                <div class="detail-actions">
                    <?php if ($no_wa): ?>
                    <a class="btn-primary" target="_blank" href="<?php echo $wa_link; ?>">Chat WA Toko</a>
                    <?php endif; ?>
                    <?php if ($no_wa_owner): ?>
                    <a class="btn-outline primary" target="_blank" href="<?php echo $wa_link_owner; ?>">Chat WA Pemilik</a>
                    <?php endif; ?>
                    <?php if ($email && $email !== '-'): ?>
                    <a class="btn-outline" href="mailto:<?php echo $email; ?>">Kirim Email</a>
                    <?php endif; ?>
                    <?php if (!empty($ig_url)): ?>
                    <a class="btn-outline" target="_blank" rel="noopener" href="<?php echo htmlspecialchars($ig_url); ?>">Buka Instagram</a>
                    <?php endif; ?>
                    <a class="btn-outline" href="mitra-titip.php">Kembali</a>
                </div>
            </div>
        </div>
    </div>
    <section id="contact" class="contact"><h2>KONTAK TITIP</h2><div class="row"><div class="info"><p>TITIP Indonesia adalah layanan kurir baru dan andal di Parepare. Kami menawarkan solusi pengiriman untuk individu dan bisnis di dalam kota.</p><div class="contact-details"><p><i data-feather="map-pin"></i>Bukit Ambassador C17 Kota Parepare, Sulawesi Selatan, Indonesia</p><p><i data-feather="phone"></i>0851 7990 2326</p><p><i data-feather="mail"></i>officialtitip@gmail.com</p></div></div><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3031.7130967431226!2d119.63935017352472!3d-4.000860644615813!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d95bafc4a389f07%3A0x5727e84f2eac3ff9!2sBukit%20Ambassador!5e1!3m2!1sen!2sid!4v1735952339916!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="maps"></iframe></div></section>
    <footer>Copyright © 2024 TITIP Indonesia</footer>

    <script>feather.replace();</script>
    <script src="js/script.js?v=2"></script>
</body>
</html>
