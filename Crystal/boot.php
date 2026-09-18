<?php
declare(strict_types=1);
namespace Crystal;
use Crystal\Core\Container;
use Crystal\Core\Router;

return function(string $appFolder = "App", bool $debug = false) {

    $start = hrtime(true);

    // Application Context
    $appRoot = __DIR__ . "/../".$appFolder;
    $appNamespace = $appFolder . '\\';

    // Error handling
    ini_set('display_errors', $debug ? '1' : '0');
    ini_set('display_startup_errors', $debug ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $appRoot . 'error.log');
    error_reporting(E_ALL);

    // Security headers
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Referrer-Policy: no-referrer');

    // Session
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !$debug,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    // Register class autoloader.
    $map = [
        'Crystal\\' => __DIR__ . '/',
        $appNamespace => $appRoot."/",
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
    $container = new Container();
    $router = new Router();

    // Load app init script
    require_once $appRoot."/app.php";

    // Match route
    $match = $router->match($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    if (null === $match) {
        http_response_code(404);
        exit;
    }

    // Get handler and params from router match
    $class = $match["class"];
    $method = $match["method"];
    $params = $match["params"];

    // Make controller instance
    $controller_instance = $container->make($class);

    // Fire controller method and get response...
    $response = $controller_instance->$method(...$params);

    // Emit
    match(true){
        is_callable($response) => $response(),
        is_array($response) || is_object($response) => print '<pre>' . htmlspecialchars(print_r($response, true)) . '</pre>',
        default => print($response)
    };

    //$elapsed = (hrtime(true) - $start) / 1_000_000;
    //error_log("Crystal formed itself in: {$elapsed} ms");

}; //end boot function
