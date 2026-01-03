<?php
// navbar.php
// File ini tidak memerlukan tag <html>, <head>, atau <body>
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid">
        
        <button class="btn btn-primary me-3" type="button" 
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            <i class="fas fa-bars"></i>
        </button>
        
        <a class="navbar-brand fw-bold text-primary me-5" href="dashboard.php">
            <i class="fas fa-cubes me-1"></i> TITIP Control
        </a>
        
        <div class="d-flex align-items-center">
             <form class="d-none d-lg-flex me-3" role="search">
                <input class="form-control me-2" type="search" placeholder="Cari..." aria-label="Search" style="width: 200px;">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> 
                        <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>