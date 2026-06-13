<?php
declare(strict_types=1);
namespace Core;

use Core\Container;

final class Kernel 
{
    private Container $container;

    public function run()
    {
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        header("Referrer-Policy: no-referrer");
        header("Access-Control-Allow-Origin: *");

        $this->registerAutoloader();
        $config = require __DIR__ . '/../App/config.php';
        $this->setErrorHandling($config["app_debug"]);

        $this->container = new Container();

        require_once __DIR__ . '/../App/helpers.php';
        require_once __DIR__ . '/../App/bootstrap.php';
        require_once __DIR__ . '/../App/routes.php';

        $match = Router::matchRequest(Request::getUri(), Request::getHttpMethod());
        $handler = $match["handler"];
        $params = $match["params"]; 

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller_instance = $this->container->make($class);
            return $controller_instance->$method(...$params);
        }

        throw new \RuntimeException("Invalid route handler");
    }

    private function registerAutoloader(): void
    {
        spl_autoload_register(function ($class) {

            $map = [
                'Core\\' => __DIR__ . '/',
                'Modules\\' => __DIR__ . '/../Modules/',
                'App\\' => __DIR__ . '/../App/',
            ];

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
    }

    private function bootHttp(){
    }

    /**
     * Set error handling based on debug mode
     * @param bool $debug Whether the application is in debug mode
     */
    private function setErrorHandling(bool $debug = false): void
    {
        // Base configuration (applies ALWAYS)
        ini_set('expose_php', 'Off');
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', __DIR__ . '/../error.log');

        // Set error reporting level
        error_reporting($debug ? E_ALL : 0);

        // In production (no error_reporting), do NOT register custom handlers
        if (!$debug) return;

        // ---- DEBUG MODE: Set custom handlers ---- 
        // Set Custom error and exception handlers if error reporting is true
        
        set_exception_handler(function ($exception) {
            error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
            if(ob_get_length()) ob_end_clean(); // Clear current output buffer if active
            http_response_code($exception->getCode() ?: 500);
            include  __DIR__.'/Pages/exception.php';
            exit;
        });

        // Set global error handler
        set_error_handler(function ($severity, $message, $file, $line) {
            error_log("Error: " . $message . " in " . $file . " on line " . $line);
            if(ob_get_length()) ob_end_clean(); // Clear current output buffer if active
            http_response_code(500);
            include __DIR__.'/Pages/error.php';
            exit;

        });
    }
}
