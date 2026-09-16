<?php
declare(strict_types=1);

use Core\Db;
use Components\QueryBuilder;
use App\Services\ExchangeRateFetcher;

// Load custom helpers
require_once __DIR__ . "/../App/helpers.php";

// Register database service
$container->registerAsSingleton(
    fn(): Db => 
        new Db('mysql', '192.168.1.102', 'alphacodedev_crystal', 'nyxx@localhost', 'Chronos_1', 3306),
        //new Db('mysql', 'localhost', 'alphacodedev_crystal', 'root', 'Chronos_1', 3306),
        Db::class,
);

// Register exchange rate fetcher
$container->register(fn(): ExchangeRateFetcher => new ExchangeRateFetcher("https://api.frankfurter.dev/v1/latest"), ExchangeRateFetcher::class);

// Page Routes
$router->get("", [App\Controllers\IndexController::class, 'dashboard']);
$router->get("view-outfit/{id}/{slug}", [App\Controllers\IndexController::class, 'viewOutfit']);

// Auth Routes
$router->get("login", [App\Controllers\AuthController::class, 'loginPage']);
$router->post("login", [App\Controllers\AuthController::class, 'doWebAuth']);
