<?php
declare(strict_types=1);
use Crystal\Components\View;
use App\Databases\CommonDb;
use App\Databases\MainDb;
use App\Services\AuthService;

/**
@var \Crystal\Core\Container $container
@var \Crystal\Core\Router $router
*/

// Load custom helpers
require_once __DIR__ . "/utils.php";

// Set base path for views
View::setBaseViewPath(__DIR__.'/Views');

$config = require_once __DIR__ . "/config.php";

$container->register(
    fn(): AuthService => new AuthService()
    );

// Register main database service
$container->registerAsSingleton(
    fn(): MainDb => new MainDb(
        $config["mainDb"]["host"],
        $config["mainDb"]["database"],
        $config["mainDb"]["user"],
        $config["mainDb"]["password"],
        $config["mainDb"]["port"],
        )
    );

// Register common database service
$container->registerAsSingleton(
    fn(): CommonDb => new CommonDb('192.168.1.102', 'common', 'nyxx@localhost', 'Chronos_1', 3306)
    );

// Dashboard Routes
$router->get("", App\Controllers\IndexController::class, 'dashboard');
$router->get("pages/{id}/{aux}", App\Controllers\IndexController::class, 'pages');

// Users routes
$userController = "App\Controllers\UserController";
$router->get("users", $userController, 'index');
$router->get("users/new", $userController, 'new');
$router->get("users/{uuid}", $userController, 'view');
$router->get("users/edit/{uuid}", $userController, 'edit');
$router->get("users/delete/{uuid}", $userController, 'delete');
$router->post("users/create", $userController, 'actionCreate');
$router->post("users/update/{uuid}", $userController, 'actionUpdate');
$router->post("users/delete/{uuid}", $userController, 'actionDelete');

// Article routes
$router->get("articles", App\Controllers\ArticleController::class, 'index');
$router->get("article/{id}", App\Controllers\ArticleController::class, 'article');
$router->post("article", App\Controllers\ArticleController::class, 'create');
$router->post("article/{id}", App\Controllers\ArticleController::class, 'update');
$router->post("article/delete/{id}", App\Controllers\ArticleController::class, 'delete');

// Auth Routes
$router->get("login", App\Controllers\AuthController::class, 'login');
$router->post("login", App\Controllers\AuthController::class, 'authenticate');
$router->get("logout", App\Controllers\AuthController::class, 'logout');
