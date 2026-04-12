<?php
require_once __DIR__ . '/config/env.php';

session_start();

$_SESSION = [];

session_destroy();

$base = hotel_tame_base_path();
$target = ($base === '' ? '' : $base) . '/login';
header('Location: ' . $target);
exit;
