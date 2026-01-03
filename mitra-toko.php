<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['mitra']);

header('Location: mitra/toko.php');
exit();
