<?php
declare(strict_types=1);
namespace Crystal;
use Crystal\Core\Container;
use Crystal\Core\Router;

return function(string $appFolder, bool $debug = false) {

    $start = hrtime(true);

    // Application Context
    $appRoot = __DIR__ . "/../".$appFolder;

    // Error handling
    ini_set('display_errors', $debug ? '1' : '0');
    ini_set('display_startup_errors', $debug ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $appRoot . '/error.log');
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

    // Register Crystal using static class map
    $crystalClasses = [
        'Crystal\Core\Container' => __DIR__ . '/Core/Container.php',
        'Crystal\Core\Router' => __DIR__ . '/Core/Router.php',
        'Crystal\Components\Auth' => __DIR__ . '/Components/Auth.php',
        'Crystal\Components\Db' => __DIR__ . '/Components/Db.php',
        'Crystal\Components\HttpClient' => __DIR__ . '/Components/HttpClient.php',
        'Crystal\Components\Pager' => __DIR__ . '/Components/Pager.php',
        'Crystal\Components\Response' => __DIR__ . '/Components/Response.php',
        'Crystal\Components\View' => __DIR__ . '/Components/View.php',
        'Crystal\Components\Query\Query' => __DIR__ . '/Components/Query/Query.php',
        'Crystal\Components\Query\Statement' => __DIR__ . '/Components/Query/Statement.php',
        'Crystal\Components\Query\Select' => __DIR__ . '/Components/Query/Select.php',
    ];
    spl_autoload_register(static function (string $class) use ($crystalClasses): void {
        if (isset($crystalClasses[$class])) {
            require $crystalClasses[$class];
        }
    });

    // Register App class autoloader.
    $map = ["App\\" => $appRoot."/"];

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

    $elapsed = (hrtime(true) - $start) / 1_000_000;
    error_log("--------------");
    error_log("Autoloader processed at: {$elapsed} ms");

    // Initialize the container and router
    $container = new Container();
    $router = new Router();

    // Load app init script
    require_once $appRoot."/app.php";

    $elapsed = (hrtime(true) - $start) / 1_000_000;
    error_log("App.php processed at: {$elapsed} ms");

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

    $elapsed = (hrtime(true) - $start) / 1_000_000;
    error_log("Route matched at: {$elapsed} ms");


    // Make controller instance. Container will resolve and instantiate all constructor dependencies
    $controller_instance = $container->make($class);

    $elapsed = (hrtime(true) - $start) / 1_000_000;
    error_log("Controller create completed at: {$elapsed} ms");

    // Fire controller method and get response...
    $response = $controller_instance->$method(...$params);

    $elapsed = (hrtime(true) - $start) / 1_000_000;
    error_log("Controller fire completed at: {$elapsed} ms");

    // Emit depending on type of response
    match(true){
        is_callable($response) => $response(),
        is_array($response) || is_object($response) => print '<pre>' . htmlspecialchars(print_r($response, true)) . '</pre>',
        default => print($response)
    };

    $elapsed = (hrtime(true) - $start) / 1_000_000;
    error_log("Crystal formed itself in: {$elapsed} ms");

}; //end boot function
