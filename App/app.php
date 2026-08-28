<?php
declare(strict_types=1);

use Core\Db;
use Core\Db\DbDriver;
use Components\Entity;
use Components\QueryBuilder;
use App\Services\ExchangeRateFetcher;

// Load custom helpers
require_once __DIR__ . "/../App/helpers.php";

// Register database service
$container->register(
    fn(): Db => 
        new Db('mysql', '192.168.1.102', 'alphacodedev_crystal', 'nyxx@localhost', 'Chronos_1', 3306),
        Db::class,
        true // Register as singleton
);

// Register query builder service
$container->register(
    fn(): QueryBuilder => new QueryBuilder(
        $container->get(Db::class)->getPdo()
    ), 
    QueryBuilder::class
);

// Register exchange rate fetcher
$container->register(
    fn(): ExchangeRateFetcher => new ExchangeRateFetcher("https://api.frankfurter.dev/v1/latest"), 
    ExchangeRateFetcher::class
);

// Page Routes
$router->get("", [App\Controllers\IndexController::class, 'homepage']);
$router->get("about-us", [App\Controllers\IndexController::class, 'aboutUs']);
$router->get("view-outfit/{id}/{slug}", [App\Controllers\IndexController::class, 'viewOutfit']);

// API routes
$router->get("api/fx", [App\Controllers\IndexController::class, 'fxRates']);

// Legacy, test routes
$router->get("outfits", [App\Controllers\OutfitController::class, "viewAll"]);
$router->get("outfit/create", ["OutfitController", "create"]);
$router->post("outfit", ["OutfitController", "store"]);
//$router->post("outfit/{id}", ["OutfitController", "update"]);
//$router->get("work-orders", ["WorkOrderController", "workOrders"]);
