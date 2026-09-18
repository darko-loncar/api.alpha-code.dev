<?php
declare(strict_types=1);
use Crystal\Components\View;
use AdminWeb\Databases\CommonDb;
use AdminWeb\Databases\MainDb;

// Load custom helpers
require_once __DIR__ . "/../AdminWeb/utils.php";

// Set base path for views
View::setBaseViewPath($appRoot.'/../AdminWeb/Views/');

// Register common database service
$container->registerAsSingleton(
    fn(): CommonDb => new CommonDb('192.168.1.102', 'common', 'nyxx@localhost', 'Chronos_1', 3306)
    );

// Register main database service
$container->registerAsSingleton(
    fn(): MainDb => new MainDb('192.168.1.102', 'alphacodedev_crystal', 'nyxx@localhost', 'Chronos_1', 3306)
    );

// Dashboard Routes
$router->get("", AdminWeb\Controllers\IndexController::class, 'dashboard');
$router->get("my-profile", AdminWeb\Controllers\IndexController::class, 'myProfile');
$router->get("image-library", AdminWeb\Controllers\IndexController::class, 'imageLibrary');
$router->get("page/{id}/{slug}", AdminWeb\Controllers\IndexController::class, 'page');

// Auth Routes
$router->get("login", AdminWeb\Controllers\AuthController::class, 'loginPage');
$router->post("login", AdminWeb\Controllers\AuthController::class, 'doWebAuth');
$router->get("logout", AdminWeb\Controllers\AuthController::class, 'doLogout');
