<?php
declare(strict_types=1);
namespace Crystal\Core;

use InvalidArgumentException;

final class Router
{
    // Store exact routes for direct lookup and dynamic routes for regex matching.
    private array $staticRoutes = ['GET' => [], 'POST' => []];
    private array $dynamicRoutes = ['GET' => [], 'POST' => []];

    /**
     * Register a route for GET requests.
     *
     * @param string $route     URL pattern with optional named parameters. Example: "/users/update/:uuid"
     * @param string $class     Handler class name
     * @param string $method    Handler method name
     */
    public function get(string $route, string $class, string $method): void
    {
        $this->addRoute($route, "GET", $class, $method);
    }

    /**
     * Register a route for POST requests.
     *
     * @param string $route     URL pattern with optional named parameters. Example: "/users/update/:uuid"
     * @param string $class     Handler class name
     * @param string $method    Handler method name
     */
    public function post(string $route, string $class, string $method): void
    {
        $this->addRoute($route, "POST", $class, $method);
    }

    /**
     * Register a route with its associated HTTP method and handler.
     *
     * @param string $route         URL pattern with optional named parameters. Example: "/users/update/:uuid"
     * @param string $httpMethod    HTTP method (GET, POST)
     * @param string $class         Handler class name
     * @param string $method        Handler method name
     */
    private function addRoute(string $route, string $httpMethod, string $class, string $method): void
    {
        // Normalize route
        $normalizedRoute = trim($route, '/');
        $routeData = [
            "class" => $class,
            "method" => $method,
            "regex" => self::compileRegex($normalizedRoute)
        ];

        // An empty regex means the route has no tokens, so it can be stored as an exact route for faster lookup.
        ($routeData["regex"] === "")
            ? $this->staticRoutes[$httpMethod][$normalizedRoute] = $routeData
            : $this->dynamicRoutes[$httpMethod][$normalizedRoute] = $routeData;
    }

    /**
     * Compile a route string into a regular expression pattern.
     *
     * @param string $routePattern Route pattern, for example `/user/{id}`.
     * @return string Compiled regular expression used for dynamic route matching.
     * @throws \InvalidArgumentException If a route parameter is invalid.
     */
    private static function compileRegex(string $routePattern): string
    {
        if(!str_contains($routePattern, ':')) return ""; // Route without tokens is static and we return empty regex

        // Split the route pattern into parts by slashes
        $parts = explode("/", $routePattern);

        // Compile each route segment into its regex representation.
        $patternArr = [];
        foreach ($parts as $part) {

            // Check if route segment starts with :, and extract the parameter name
            if (str_starts_with($part, ':')) {
                $paramName = substr($part, 1);

                // Check if is valid param name
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $paramName)) {
                    throw new InvalidArgumentException("Invalid route parameter: $part");
                }

                $patternArr[] = '(?P<'.$paramName.'>[a-zA-Z0-9_-]+)';
                continue;
            }

            $patternArr[] = preg_quote($part, '~');
        }

        return '^' . implode('/', $patternArr) . '$';
    }

    /**
    * Match a request URI and HTTP method against the registered routes.
    * Exact routes use direct lookup. Dynamic routes are tested using their compiled patterns in registration order.
    *
    * @param string $requestUri The URI of the incoming request, typically the path part of the URL.
    * @param string $httpMethod The HTTP method of the request (e.g., GET, POST, etc.).
    * @return array{array, params: array<string, int|string>}|null The matched handler and route parameters, or `null` when no route matches.
    */
    public function match(string $requestUri, string $httpMethod): ?array
    {
        // Normalize request Uri and Http method
        $normalizedUri = trim(strtok($requestUri, '?'), '/');
        $httpMethod = strtoupper($httpMethod);

        // First try to match fixed routes.
        if (($route = $this->staticRoutes[$httpMethod][$normalizedUri] ?? null) !== null) {
            return [
                "class" => $route["class"],
                "method" => $route["method"],
                "params" => [],
            ];
        }

        // Match dynamic routes
        $routes = $this->dynamicRoutes[$httpMethod];

        // Loop through each dynamic route and check for a match
        foreach ($routes as $route) {

            // Match the route regex against request URI
            if (preg_match("~{$route['regex']}~", $normalizedUri, $params) !== 1) continue;

            // Cleanup numerical indexes from preg_match $params array
            array_shift($params); // remove first one cheaply
            foreach ($params as $key => $value) {

                // Unset numeric indexes
                if (is_int($key)) {
                    unset($params[$key]);
                    continue;
                    }

                // Cast numeric params to int
                if (ctype_digit($value)) {
                    $params[$key] = (int)$value;
                    }
                }

            return [
                "class" => $route["class"],
                "method" => $route["method"],
                "params" => $params,
                ];
        }

        // If no routes matched...
        return null;
    }
}
