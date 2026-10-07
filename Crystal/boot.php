<?php
declare(strict_types=1);
namespace Crystal;
use Crystal\Core\Container;
use Crystal\Core\Router;

return function(string $appFolder, bool $debug = false) {

    //$start = hrtime(true);

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
    session_cache_limiter('');
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
        'Crystal\Components\Query\Insert' => __DIR__ . '/Components/Query/Insert.php',
        'Crystal\Support\UUID' => __DIR__ . '/Support/UUID.php',
        'Crystal\Support\Slug' => __DIR__ . '/Support/Slug.php',

    ];
    spl_autoload_register(static function (string $class) use ($crystalClasses): void {
        if (isset($crystalClasses[$class])) {
            require $crystalClasses[$class];
        }
    });

    // Register App class autoloader.
    spl_autoload_register(
        static function (string $class) use ($appRoot): void {
            if (!str_starts_with($class, 'App\\')) return;
            $file = $appRoot . '/' . str_replace('\\', '/', substr($class, strlen('App\\'))) . '.php';
            if (is_file($file)) require $file;
        }
    );

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

    // Make controller instance. Container will resolve and instantiate all constructor dependencies
    $controller_instance = $container->make($class);

    // Fire controller method and get response...
    $response = $controller_instance->$method(...$params);

    // Canonical weak ETag for the uncompressed representation.
    $etag = hash('xxh128', $response);
    $etagHeader = 'W/"' . $etag . '"';

    header('Cache-Control: no-cache');
    header('ETag: ' . $etagHeader);

    $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';

    if ($ifNoneMatch !== '') {
        $clientEtags = array_map('trim', explode(',', $ifNoneMatch));

        foreach ($clientEtags as $clientEtag) {
            // Wildcard matches any current representation.
            if ($clientEtag === '*') {
                http_response_code(304);
                exit;
            }

            // Accept:
            // W/"abc123"
            // W/"abc123-gzip"
            //
            // Cloudflare may append "-gzip" to the ETag.
            if (
                $clientEtag === $etagHeader ||
                $clientEtag === 'W/"' . $etag . '-gzip"'
            ) {
                http_response_code(304);
                exit;
            }
        }
    }

    echo $response;
    //$elapsed = (hrtime(true) - $start) / 1_000_000;
    //error_log("Crystal formed itself in: {$elapsed} ms");

}; //end boot function
