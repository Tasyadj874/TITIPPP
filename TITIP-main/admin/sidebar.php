<?php 
// sidebar.php
// Hapus semua tag HTML dokumen, body, dan head.
?>

<div class="offcanvas offcanvas-start bg-dark text-white" 
     tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title text-primary fw-bold" id="offcanvasSidebarLabel">
            <i class="fas fa-cubes me-2"></i> TITIP Admin
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        
        <div class="list-group list-group-flush flex-grow-1">
            
            <div class="px-3 py-2 text-white-50 small text-uppercase">Menu Utama</div>

            <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white active border-0 py-3"><i class="fas fa-tachometer-alt fa-fw me-3"></i> Dashboard</a>
            <a href="umkm.php" class="list-group-item list-group-item-action bg-dark text-white border-0 py-3"><i class="fas fa-store fa-fw me-3"></i> Mitra UMKM</a>
            <a href="pasukan.php" class="list-group-item list-group-item-action bg-dark text-white border-0 py-3"><i class="fas fa-motorcycle fa-fw me-3"></i> Pasukan Titip</a>
            <a href="kategori.php" class="list-group-item list-group-item-action bg-dark text-white border-0 py-3"><i class="fas fa-tags fa-fw me-3"></i> Kategori</a>
            <a href="laporan.php" class="list-group-item list-group-item-action bg-dark text-white border-0 py-3"><i class="fas fa-chart-bar fa-fw me-3"></i> Laporan</a>
            
        </div>
        
        <div class="p-3 mt-auto border-top border-secondary">
            <a href="logout.php" class="btn btn-outline-danger w-100">
                <i class="fas fa-sign-out-alt fa-fw me-2"></i> Logout
            </a>
        </div>
    </div>
</div>