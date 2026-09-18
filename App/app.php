<?php
declare(strict_types=1);
use Crystal\Components\View;
use App\Databases\CommonDb;
use App\Databases\MainDb;

// Load custom helpers
require_once __DIR__ . "/../App/helpers.php";

// Set base path for views
View::setBaseViewPath(__DIR__ . '/../App/Views/');

// Register common database service
$container->registerAsSingleton(
    fn(): CommonDb => new CommonDb('192.168.1.102', 'common', 'nyxx@localhost', 'Chronos_1', 3306)
    );

// Register main database service
$container->registerAsSingleton(
    fn(): MainDb => new MainDb('192.168.1.102', 'alphacodedev_crystal', 'nyxx@localhost', 'Chronos_1', 3306)
    );

// Dashboard Routes
$router->get("", App\Controllers\IndexController::class, 'dashboard');
$router->get("my-profile", App\Controllers\IndexController::class, 'myProfile');
$router->get("page/{id}/{slug}", App\Controllers\IndexController::class, 'page');

// Auth Routes
$router->get("login", App\Controllers\AuthController::class, 'loginPage');
$router->post("login", App\Controllers\AuthController::class, 'doWebAuth');
$router->get("logout", App\Controllers\AuthController::class, 'doLogout');
