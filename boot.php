<?php
use Core\Container;
use Core\Router;
use Core\Response;

// Set error handling based on debug mode
$debug = true;
ini_set('expose_php', 'Off');
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log');
error_reporting($debug ? E_ALL : 0);

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: no-referrer");
header("Access-Control-Allow-Origin: *");

// Register class autoloader.
$map = [
    'Core\\' => __DIR__ . '/Core/',
    'Components\\' => __DIR__ . '/Components/',
    'App\\' => __DIR__ . '/App/',
];

spl_autoload_register(static function (string $class) use ($map) {
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

// Initialize the container and router
$router = new Router();
$container = new Container();
$container->register(
    fn(): Container => $container,
    Container::class,
    true // Register as singleton
);

// Load app init script
require_once __DIR__ . "/App/app.php";

// Match route
$match = $router->matchRequest($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
if (null === $match) {
    http_response_code(404);
    exit;
}

$handler = $match["handler"];
$params = $match["params"]; 
[$class, $method] = $handler;
$controller_instance = $container->make($class);

$result = $controller_instance->$method(...$params);

if ($result instanceof Response) {
    $result->send();
} else {
    echo $result;
}
