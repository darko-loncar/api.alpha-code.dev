<?php
declare(strict_types=1);
use Crystal\Components\View;
use AdminWeb\Databases\CommonDb;
use AdminWeb\Databases\MainDb;
use AdminWeb\Services\AuthService;

// Load custom helpers
require_once __DIR__ . "/utils.php";

// Set base path for views
View::setBaseViewPath(__DIR__.'/Views');

$container->register(
    fn(): AuthService => new AuthService()
    );

// Register common database service
//$container->registerAsSingleton(
    //fn(): CommonDb => new CommonDb('192.168.1.102', 'common', 'nyxx@localhost', 'Chronos_1', 3306)
    //);

// Register main database service
$container->registerAsSingleton(
    fn(): MainDb => new MainDb('192.168.1.102', 'alphacodedev_crystal', 'nyxx@localhost', 'Chronos_1', 3306)
    //fn(): MainDb => new MainDb('localhost', 'alphacodedev_crystal', 'alphacodedev_crystal', 'ChronosPlant_25', 3306)
    );

// Dashboard Routes
$router->get("", AdminWeb\Controllers\IndexController::class, 'dashboard');

// Users routes
$router->get("users", AdminWeb\Controllers\UserController::class, 'users');
$router->get("user-profile/{uuid}", AdminWeb\Controllers\UserController::class, 'user');

// Auth Routes
$router->get("login", AdminWeb\Controllers\AuthController::class, 'loginPage');
$router->post("login", AdminWeb\Controllers\AuthController::class, 'doWebAuth');
$router->get("logout", AdminWeb\Controllers\AuthController::class, 'doLogout');
