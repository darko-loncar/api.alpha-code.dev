<?php
declare(strict_types=1);

// Boot Crystal machine...
define('CRYSTAL_APP_ROOT', dirname(__DIR__));
define('CRYSTAL_DEBUG', false);

$crystal = require_once __DIR__ . '/../../Crystal/boot.php';
