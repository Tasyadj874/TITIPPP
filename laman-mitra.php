<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['mitra']);

header('Location: mitra/index.php');
exit();
?>
