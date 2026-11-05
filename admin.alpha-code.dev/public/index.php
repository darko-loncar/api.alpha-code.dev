<?php
declare(strict_types=1);

// Answer CORS preflight requests for the public read-only API before the router runs.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (
    ($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS' &&
    str_starts_with($requestPath, '/api/')
) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Accept, Content-Type');
    if (($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_PRIVATE_NETWORK'] ?? '') === 'true') {
        header('Access-Control-Allow-Private-Network: true');
    }
    http_response_code(204);
    exit;
}

// Boot Crystal machine...
define('CRYSTAL_APP_ROOT', dirname(__DIR__));
define('CRYSTAL_DEBUG', false);

$crystal = require_once __DIR__ . '/../../Crystal/boot.php';
