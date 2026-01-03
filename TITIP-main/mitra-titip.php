<?php 
    // mitra-titip.php
    
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

        
    require "config.php"; // Memastikan koneksi database tersedia ($con)
    
    // 1. Mengambil Daftar Semua Profil UMKM
    // Mengambil semua data dari tabel umkm_profile, diurutkan berdasarkan tanggal pendaftaran terbaru
    $kategoriParam = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
    $kategoriParamSafe = $kategoriParam !== '' ? mysqli_real_escape_string($con, $kategoriParam) : '';
    
    // Pagination setup
    $perPage = 12;
    $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;
    
    $where = '';
    if ($kategoriParamSafe !== '') {
        $where = "WHERE bidang_usaha = '" . $kategoriParamSafe . "'";
    }
    
    // Hitung jumlah total (untuk pagination)
    $countSql = "SELECT COUNT(*) AS total FROM umkm_profile " . $where;
    $countRes = mysqli_query($con, $countSql);
    $countRow = $countRes ? mysqli_fetch_assoc($countRes) : ['total' => 0];
    $jumlahUMKM = (int)($countRow['total'] ?? 0);
    $totalPages = $jumlahUMKM > 0 ? (int)ceil($jumlahUMKM / $perPage) : 1;
    
    // Query data dengan limit
    $sqlUMKM = "SELECT * FROM umkm_profile " . $where . " ORDER BY created_at DESC LIMIT $offset, $perPage";
    $queryUMKM = mysqli_query($con, $sqlUMKM);

    // 2. Mengambil Daftar Kategori (Untuk filter Bidang Usaha)
    $queryKategori = mysqli_query($con, "SELECT * FROM kategori");

    // 3. Fungsi Utility: Generate Random String (Dibiarkan, berguna untuk utilitas)
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Mitra TITIP</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap"
            rel="stylesheet"
        />

        <script src="https://unpkg.com/feather-icons"></script>

        <link rel="stylesheet" href="css/mitra.css" />
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
                    <a href="index.php#gabung-mitra" >Gabung Mitra </a>
                    <a href="mitra-titip.php" >Mitra </a>
                    <a href="index.php#contact">Kontak</a>
                </div>
                
                <div class="navbar-extra">
                    <a href="#" id="login" class="account-trigger" aria-haspopup="true" aria-expanded="false">
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
                                <a href="user/logout.php?redirect=<?php echo urlencode('../mitra-titip.php'); ?>">Logout</a>
                            <?php else: ?>
                                <a href="admin/logout.php?redirect=<?php echo urlencode('../mitra-titip.php'); ?>">Logout</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
        <div class="container-fluid banner-produk d-flex align-items-center justify-content-center">
            <div class="container">
                <h1 class="text-white text-center">Daftar Mitra UMKM TITIP</h1>
            </div>
        </div>

        <div class="container py-5">
            <div class="row align-items-center mb-4">
                <div class="col-lg-3">
                    <h3>Bidang Usaha</h3>
                </div>
                <div class="col-lg-9">
                    <h3 class="text-center">Mitra UMKM</h3>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-3 mb-5">
                    <ul class="list-group">
                        <a class="no-decoration" href="mitra-titip.php">
                            <li class="list-group-item<?php echo ($kategoriParam === '' ? ' active' : ''); ?>">Semua</li>
                        </a>
                        <?php while($kategori = mysqli_fetch_array($queryKategori)){ ?>
                        <a class="no-decoration" href="mitra-titip.php?kategori=<?php echo urlencode($kategori['nama']);?>">
                            <li class="list-group-item<?php echo ($kategoriParam === ($kategori['nama'] ?? '') ? ' active' : ''); ?>"><?php echo htmlspecialchars($kategori['nama']);?></li>
                        </a>
                        <?php } ?>
                    </ul>
                </div>

                <div class="col-lg-9">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php
                            if($jumlahUMKM < 1){
                        ?>
                            <div class="col-12">
                                <h4 class="text-center my-5">Belum ada Mitra UMKM yang terdaftar saat ini.</h4>
                            </div>
                        <?php        
                            }
                        ?>
                        
                        <?php 
                        // Looping melalui hasil query UMKM
                        while($mitra = mysqli_fetch_assoc($queryUMKM)) { 
                            // Format jam operasi
                            $jamBuka = !empty($mitra['jam_buka']) ? date('H:i', strtotime($mitra['jam_buka'])) : '08:00';
                            $jamTutup = !empty($mitra['jam_tutup']) ? date('H:i', strtotime($mitra['jam_tutup'])) : '23:00';
                            $jamOperasi = $jamBuka . ' - ' . $jamTutup;
                            
                            // Ambil rating (jika ada)
                            $rating = isset($mitra['rating']) ? floatval($mitra['rating']) : 4.5;
                            $ratingRounded = round($rating * 2) / 2; // Bulatkan ke 0.5 terdekat
                        ?>
                        <div class="col">
                            <div class="card mitra-card">
                                <div class="card-img-container">
                                    <?php 
                                        $rawLogo = $mitra['logo'] ?? '';
                                        if (!empty($rawLogo)) {
                                            if (strpos($rawLogo, 'uploads') !== false || strpos($rawLogo, '/') !== false) {
                                                $logo_path = $rawLogo;
                                            } else {
                                                $logo_path = 'uploads/logo/' . $rawLogo;
                                            }
                                        } else {
                                            $logo_path = 'images/default_logo.png';
                                        }
                                    ?>
                                    <img 
                                        src="<?php echo htmlspecialchars($logo_path); ?>" 
                                        alt="<?php echo htmlspecialchars($mitra['nama_usaha']); ?>"
                                    >
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($mitra['nama_usaha']); ?></h5>
                                    <p class="card-category"><?php echo htmlspecialchars($mitra['bidang_usaha']); ?></p>
                                    <p class="card-hours">
                                        <i class="feather-clock"></i> Jam Operasi: <?php echo $jamOperasi; ?>
                                    </p>
                                    <div class="rating">
                                        <?php
                                            // Tampilkan bintang rating
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $ratingRounded) {
                                                    echo '<i class="feather-star star" data-feather="star" fill="#ffc107"></i>';
                                                } elseif ($i - 0.5 <= $ratingRounded) {
                                                    echo '<i class="feather-star star" data-feather="star" fill="#ffc107"></i>';
                                                } else {
                                                    echo '<i class="feather-star star" data-feather="star"></i>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <a href="mitra-detail.php?id=<?php echo $mitra['id']; ?>" class="btn btn-detail">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php if ($totalPages > 1) { ?>
                    <div class="pagination">
                        <?php 
                            $baseUrl = 'mitra-titip.php';
                            $queryBase = [];
                            if ($kategoriParam !== '') { $queryBase['kategori'] = $kategoriParam; }
                            // Prev
                            if ($page > 1) {
                                $queryPrev = http_build_query(array_merge($queryBase, ['page' => $page - 1]));
                                echo '<a class="page-item" href="' . $baseUrl . '?' . $queryPrev . '">&laquo; Prev</a>';
                            }
                            // Pages
                            for ($p = 1; $p <= $totalPages; $p++) {
                                $queryP = http_build_query(array_merge($queryBase, ['page' => $p]));
                                $active = $p === $page ? ' active' : '';
                                echo '<a class="page-item' . $active . '" href="' . $baseUrl . '?' . $queryP . '">' . $p . '</a>';
                            }
                            // Next
                            if ($page < $totalPages) {
                                $queryNext = http_build_query(array_merge($queryBase, ['page' => $page + 1]));
                                echo '<a class="page-item" href="' . $baseUrl . '?' . $queryNext . '">Next &raquo;</a>';
                            }
                        ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <script>
        feather.replace();
    </script>

    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    </body>
</html>