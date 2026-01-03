<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['kurir']);

header('Location: kurir/index.php');
exit();
