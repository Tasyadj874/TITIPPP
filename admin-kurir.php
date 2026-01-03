<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/util.php';
require_role(['admin']);

header('Location: admin/kurir.php');
exit();
