<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['user']);

header('Location: user/index.php');
exit();
