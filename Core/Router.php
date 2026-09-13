<?php
declare(strict_types=1);
namespace Core;

final class Router
{
    // Store exact routes for direct lookup and dynamic routes for regex matching.
    private array $exactRoutes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    private array $dynamicRoutes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    /**
     * Register a route for GET requests.
     *
     * @param string $routeString Route path, optionally containing simple named parameters such as {id}.
     * @param array $handler Route handler.
     */
    public function get(string $routeString, array $handler): void
    {        
        $this->addRoute($routeString, "GET", $handler);
    }

    /**
     * Register a route for POST requests.
     *
     * @param string $routeString Route path, optionally containing simple named parameters such as {id}.
     * @param array $handler Route handler.
     */
    public function post(string $routeString, array $handler): void
    {
        $this->addRoute($routeString, "POST", $handler);
    }

    /**
     * Register a route for PUT requests.
     *
     * @param string $routeString Route path, optionally containing simple named parameters such as {id}.
     * @param array $handler Route handler.
     */
    public function put(string $routeString, array $handler): void
    {
        $this->addRoute($routeString, "PUT", $handler);
    }

    /**
     * Register a route for DELETE requests.
     *
     * @param string $routeString Route path, optionally containing simple named parameters such as {id}.
     * @param array $handler Route handler.
     */
    public function delete(string $routeString, array $handler): void
    {
        $this->addRoute($routeString, "DELETE", $handler);
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
        $exactRoute = $this->exactRoutes[$httpMethod][$normalizedUri] ?? null;
        if ($exactRoute !== null) {
            return [
                "handler" => $exactRoute["handler"],
                "params" => [],
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
                if (\is_int($key)) unset($params[$key]);
                }

            return [
                "handler" => $route["handler"],
                "params" => $params
                ];
        }

        // If no routes matched...
        return null;
    }

    /**
     * Register a route with its associated HTTP method and handler.
     *
     * @param string $routeString   URL pattern with optional simple named parameters such as {id}.
     * @param string $httpMethod    HTTP method supported by this router.
     * @param array  $handler       Route handler.
     */
    private function addRoute(string $routeString, string $httpMethod, array $handler): void
    {
        // Normalize route
        $normalizedRoute = trim($routeString, '/');
        $route = ["handler" => $handler];

        // If route contains "{", it's a dynamic route; otherwise, it's an exact route.}
        if(str_contains($normalizedRoute, '{')) {
            $route["regex"] = $this->compileRegex($routeString);
            $this->dynamicRoutes[$httpMethod][$normalizedRoute] = $route;
        } else {
            $this->exactRoutes[$httpMethod][$normalizedRoute] = $route;
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
        // Remove leading/trailing slashes from the route pattern
        $routePattern = trim($routePattern, "/");

        // Split the route pattern into parts by slashes
        $parts = explode("/", $routePattern);
    
        // Compile each route segment into its regex representation.
        $patternParts = [];
        foreach ($parts as $part) {

            // Check if route segment starts and ends with {}, and extract the text between as param name
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $paramName = substr($part, 1, -1);

               if (!preg_match('/^[a-zA-Z]+$/', $paramName)) {
                    throw new \InvalidArgumentException("Invalid route parameter: $part");
                    }
                $patternParts[] = '(?P<'.$paramName.'>[a-zA-Z0-9_-]+)';
                continue;
            }

            $patternParts[] = preg_quote($part, '/');
        }
        return '^' . implode('/', $patternParts) . '$';
    }
}