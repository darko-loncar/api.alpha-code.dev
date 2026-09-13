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
$router->get("", [App\Controllers\IndexController::class, 'homepage']);
$router->get("about-us", [App\Controllers\IndexController::class, 'aboutUs']);
$router->get("view-outfit/{id}/{slug}", [App\Controllers\IndexController::class, 'viewOutfit']);

// API routes
$router->get("api/fx", [App\Controllers\IndexController::class, 'fxRates']);
