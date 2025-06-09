<?php

declare(strict_types=1);

namespace Cfms\Core;


class Router
{
    public const POST = "POST";

    public const GET = "GET";

    private array $routes = [];

    public function post(string $path, array $controller): Router
    {
        $this->add(self::POST, $path, $controller);
        return $this;
    }
    public function get(string $path, array $controller): Router
    {
        $this->add(self::GET, $path, $controller);
        return $this;
    }
    private function add(string $method, string $path, array $controller)
    {
        $path = $this->normalizePath($path);
        $this->routes[] = [
            'path' => $path,
            'method' => strtoupper($method),
            'controller' => $controller,
            'middlewares' => []
        ];
    }
    private function normalizePath($path)
{
    // Remove query strings (if any)
    $path = parse_url($path, PHP_URL_PATH);

    // Ensure consistent slashes 
    return rtrim($path, '/') ?: '/';
}

    public function dispatch()
{
    $original_path = $_SERVER['REQUEST_URI'];
    $path = $this->normalizePath($original_path);
    $method = strtoupper($_SERVER['REQUEST_METHOD']);

    foreach ($this->routes as $route) {
        if (preg_match("#^{$route['path']}$#", $path) && $route['method'] === $method) {
            [$class, $function] = $route['controller'];

            if (!class_exists($class)) {
                throw new \Exception("Controller class {$class} not found.");
            }

            $controllerInstance = new $class();

            if (!method_exists($controllerInstance, $function)) {
                throw new \Exception("Method {$function} not found in {$class}.");
            }

            $controllerInstance->{$function}();
            return;
        }
    }
    
    // Load error controller if no route matches
    $this->loadErrorPage();
}

private function loadErrorPage()
{
    http_response_code(404);
    
    $errorPage = __DIR__ . '/../Views/error.phtml';

    if (file_exists($errorPage)) {
        require $errorPage;
    } else {
        echo "<h1>404 Not Found</h1><p>The requested page could not be found.</p>";
    }
}
}