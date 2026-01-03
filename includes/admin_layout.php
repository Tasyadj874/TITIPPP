<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function admin_page_start(string $pageTitle, string $activeNav): void
{
    $displayName = auth_display_name();
    if ($displayName === '') {
        $displayName = 'Admin';
    }

    $base = app_base_path();

    $nav = [
        'dashboard' => ['label' => 'Dashboard', 'href' => 'index.php', 'icon' => 'home'],
        'mitra' => ['label' => 'Mitra', 'href' => 'mitra.php', 'icon' => 'users'],
        'kurir' => ['label' => 'Kurir', 'href' => 'kurir.php', 'icon' => 'truck'],
        'toko' => ['label' => 'Toko', 'href' => 'toko.php', 'icon' => 'shopping-bag'],
        'pesanan' => ['label' => 'Pesanan', 'href' => 'pesanan.php', 'icon' => 'clipboard'],
    ];

    ?>
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php echo htmlspecialchars($pageTitle); ?> - Admin TITIP</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
          href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&family=Roboto:wght@400;500;700;900&display=swap"
          rel="stylesheet"
        />

        <script src="https://unpkg.com/feather-icons"></script>

        <link rel="stylesheet" href="../css/style.css" />
        <link rel="stylesheet" href="../css/admin.css?v=20251230" />
      </head>
      <body class="admin-body">
        <div class="admin-shell" id="admin-shell">
          <aside class="admin-sidebar" aria-label="Admin sidebar">
            <a class="admin-brand" href="index.php">
              <img src="../images/LOGO TITIP.png" alt="Logo TITIP" />
              <div class="admin-brand-title">
                <strong>TITIP</strong>
                <span>Admin Panel</span>
              </div>
            </a>

            <nav class="admin-nav">
              <?php foreach ($nav as $key => $item): ?>
                <a href="<?php echo htmlspecialchars($item['href']); ?>" class="<?php echo $key === $activeNav ? 'active' : ''; ?>">
                  <i data-feather="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                  <span><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
              <?php endforeach; ?>
            </nav>
          </aside>

          <div class="admin-main">
            <header class="admin-topbar">
              <div class="admin-topbar-inner">
                <div class="admin-topbar-left">
                  <button class="admin-mobile-toggle" type="button" id="admin-sidebar-toggle" aria-label="Menu">
                    <i data-feather="menu"></i>
                  </button>
                  <div class="admin-page-title"><?php echo htmlspecialchars($pageTitle); ?></div>
                </div>

                <div class="admin-topbar-right">
                  <div class="admin-user">
                    <a href="#login-options" id="login" class="admin-user-button">
                      <i data-feather="user"></i>
                      <span><?php echo htmlspecialchars($displayName); ?></span>
                      <i data-feather="chevron-down"></i>
                    </a>
                    <div class="admin-user-dropdown dropdown" id="login-options">
                      <a href="<?php echo htmlspecialchars($base . '/logout.php?role=admin'); ?>">Logout</a>
                    </div>
                  </div>
                </div>
              </div>
            </header>

            <main class="admin-content">
    <?php
}

function admin_page_end(): void
{
    ?>
            </main>
          </div>
        </div>

        <script>
          feather.replace();
        </script>

        <script src="../js/script.js?v=20251230"></script>
        <script>
          (function () {
            var shell = document.getElementById('admin-shell');
            var btn = document.getElementById('admin-sidebar-toggle');
            if (!shell || !btn) return;
            btn.addEventListener('click', function () {
              shell.classList.toggle('sidebar-open');
            });
            document.addEventListener('click', function (e) {
              if (!shell.classList.contains('sidebar-open')) return;
              var sidebar = shell.querySelector('.admin-sidebar');
              if (!sidebar) return;
              if (btn.contains(e.target) || sidebar.contains(e.target)) return;
              shell.classList.remove('sidebar-open');
            });
          })();
        </script>
      </body>
    </html>
    <?php
}
