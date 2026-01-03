<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function portal_nav_items(string $role, string $base): array
{
    if ($role === 'mitra') {
        return [
            ['label' => 'Dashboard', 'href' => $base . '/mitra/index.php', 'key' => 'dashboard'],
            ['label' => 'Toko Saya', 'href' => $base . '/mitra/toko.php', 'key' => 'toko'],
            ['label' => 'Produk', 'href' => $base . '/mitra/produk.php', 'key' => 'produk'],
            ['label' => 'Pesanan', 'href' => $base . '/mitra/pesanan.php', 'key' => 'pesanan'],
            ['label' => 'Profil', 'href' => $base . '/mitra/profil.php', 'key' => 'profil'],
        ];
    }

    if ($role === 'kurir') {
        return [
            ['label' => 'Dashboard', 'href' => $base . '/kurir/index.php', 'key' => 'dashboard'],
            ['label' => 'Pesanan', 'href' => $base . '/kurir/pesanan.php', 'key' => 'pesanan'],
        ];
    }

    if ($role === 'user') {
        return [
            ['label' => 'Dashboard', 'href' => $base . '/user/index.php', 'key' => 'dashboard'],
            ['label' => 'Pesanan Saya', 'href' => $base . '/user/pesanan.php', 'key' => 'pesanan'],
        ];
    }

    return [
        ['label' => 'Dashboard', 'href' => $base . '/index.php', 'key' => 'dashboard'],
    ];
}

function portal_page_start(string $role, string $title, string $activeKey = ''): void
{
    auth_start();

    $base = app_base_path();
    $css = $base . '/css/style.css';
    $js = $base . '/js/script.js?v=20251230';
    $logo = $base . '/images/LOGO TITIP.png';
    $logoutHref = $base . '/logout.php?role=' . urlencode($role);

    $navItems = portal_nav_items($role, $base);

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8" />';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com" />';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />';
    echo '<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />';
    echo '<script src="https://unpkg.com/feather-icons"></script>';
    echo '<link rel="stylesheet" href="' . htmlspecialchars($css) . '" />';
    echo '</head>';
    echo '<body class="portal-body portal-role-' . htmlspecialchars($role) . '">';

    echo '<div class="red-navbar">';
    echo '<nav class="navbar">';

    echo '<div class="logo-container">';
    echo '<img src="' . htmlspecialchars($logo) . '" alt="Logo TITIP" class="logo" />';
    echo '</div>';

    echo '<div class="navbar-nav">';
    foreach ($navItems as $it) {
        $cls = '';
        if ($activeKey !== '' && $activeKey === (string)($it['key'] ?? '')) {
            $cls = ' class="active"';
        }
        echo '<a href="' . htmlspecialchars((string)$it['href']) . '"' . $cls . '>' . htmlspecialchars((string)$it['label']) . '</a>';
    }
    echo '</div>';

    echo '<div class="navbar-extra">';
    echo '<a href="#login-options" id="login" style="display: inline-flex; align-items: center; gap: 10px;">';
    echo '<i data-feather="users"></i>';
    echo '<span style="font-size: 16px; font-weight: 800; color: #fff; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' . htmlspecialchars(auth_display_name()) . '</span>';
    echo '</a>';
    echo '<a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>';
    echo '<div class="dropdown" id="login-options">';
    echo '<a href="' . htmlspecialchars($logoutHref) . '">Sign Out</a>';
    echo '</div>';
    echo '</div>';

    echo '</nav>';
    echo '</div>';

    echo '<main class="portal-main">';
}

function portal_page_end(): void
{
    $base = app_base_path();
    $js = $base . '/js/script.js?v=20251230';

    echo '</main>';
    echo '<script>feather.replace();</script>';
    echo '<script src="' . htmlspecialchars($js) . '"></script>';
    echo '</body>';
    echo '</html>';
}
