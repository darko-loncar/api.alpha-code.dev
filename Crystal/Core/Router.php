<?php
declare(strict_types=1);
namespace Crystal\Core;

final class Router
{
    // Store exact routes for direct lookup and dynamic routes for regex matching.
    private array $staticRoutes = [
        'GET' => [],
        'POST' => [],
    ];

    private array $dynamicRoutes = [
        'GET' => [],
        'POST' => [],
    ];

    /**
     * Register a route for GET requests.
     *
     * @param string $routeString Route path, optionally containing simple named parameters such as {id}.
     * @param string $handlerClass Route handler class name.
     * @param string $handlerMethod Route handler method name.
     */
    public function get(string $routeString, string $handlerClass, string $handlerMethod): void
    {
        $this->addRoute($routeString, "GET", $handlerClass, $handlerMethod);
    }

    /**
     * Register a route for POST requests.
     *
     * @param string $routeString Route path, optionally containing simple named parameters such as {id}.
     * @param string $handlerClass Route handler class name.
     * @param string $handlerMethod Route handler method name.
     */
    public function post(string $routeString, string $handlerClass, string $handlerMethod): void
    {
        $this->addRoute($routeString, "POST", $handlerClass, $handlerMethod);
    }

    /**
     * Register a route with its associated HTTP method and handler.
     *
     * @param string $routeString   URL pattern with optional simple named parameters such as {id}.
     * @param string $httpMethod    HTTP method supported by this router.
     * @param string $handlerClass  Route handler class name.
     * @param string $handlerMethod Route handler method name.
     */
    private function addRoute(string $routeString, string $httpMethod, string $handlerClass, string $handlerMethod): void
    {
        // Normalize route
        $normalizedRoute = trim($routeString, '/');
        $route = [
            "class" => $handlerClass,
            "method" => $handlerMethod,
            "regex" => $this->compileRegex($normalizedRoute)
        ];

        // An empty regex means the route has no tokens, so it can be stored as an exact route for faster lookup.
        if ($route["regex"] === "") {
            $this->staticRoutes[$httpMethod][$normalizedRoute] = $route;
        } else {
            $this->dynamicRoutes[$httpMethod][$normalizedRoute] = $route;
        }
    }

    /**
    * Compile a route string into a regular expression pattern.
     *
    * Only simple named parameters such as `{id}`, `{slug}`, and `{uuid}` are
    * supported. Inline parameter constraints are not supported.
     *
    * @param string $routePattern Route pattern, for example `/user/{id}`.
    * @return string Compiled regular expression used for dynamic route matching.
    * @throws \InvalidArgumentException If a route parameter is invalid.
     */
    private function compileRegex(string $routePattern): string
    {
        if(!str_contains($routePattern, '{')) return ""; // Route without tokens is static and we return empty regex

        // Split the route pattern into parts by slashes
        $parts = explode("/", $routePattern);

        // Compile each route segment into its regex representation.
        $patternParts = [];

        foreach ($parts as $part) {

            // Check if route segment starts and ends with {}, and extract the text between as param name
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $paramName = substr($part, 1, -1);

                if (!ctype_alpha($paramName)) {
                    throw new \InvalidArgumentException("Invalid route parameter: $part");
                    }
                $patternParts[] = '(?P<'.$paramName.'>[a-zA-Z0-9_-]+)';
                continue;
            }

            $patternParts[] = preg_quote($part, '~');
        }

        return '^' . implode('/', $patternParts) . '$';
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
        // Normalize request Uri
        $normalizedUri = strtok($requestUri, '?') ?: ""; // `strtok` splits the URI at the `?` and keeps the part before it.
        $normalizedUri = trim($normalizedUri, '/');
        $httpMethod = strtoupper($httpMethod);

        // First try to match fixed routes.
        $exactRoute = $this->staticRoutes[$httpMethod][$normalizedUri] ?? null;
        if ($exactRoute !== null) {
            return [
                "class" => $exactRoute["class"],
                "method" => $exactRoute["method"],
                "params" => []
                ];
        }

        // Match dynamic routes
        $routes = $this->dynamicRoutes[$httpMethod];

        // Loop through each dynamic route and check for a match
        foreach ($routes as $route) {

            // Match the route regex to the request URI
            if (preg_match("~{$route['regex']}~", $normalizedUri, $params) !== 1) {
                continue;
                }

            // Cleanup numerical indexes from $params array
            foreach ($params as $key => $value) {
                // Unset numeric indexes
                if (is_int($key)) {
                    unset($params[$key]);
                    continue;
                    }
                // Cast numeric params to int
                if (ctype_digit($value)) {
                    $params[$key] = (int) $value;
                    }
                }

            return [
                "class" => $route["class"],
                "method" => $route["method"],
                "params" => $params
                ];
        }

        // If no routes matched...
        return null;
    }
}
