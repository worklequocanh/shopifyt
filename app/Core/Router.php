<?php

/**
 * Router Class
 * Handles URL routing and dispatches requests to appropriate controllers
 */
class Router
{
    private $routes = [];
    private $basePath;

    public function __construct($basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Add a route
     * 
     * @param string $path Route path (e.g., '/product/detail/:id')
     * @param string $controller Controller name
     * @param string $action Action/method name
     */
    public function add($path, $controller, $action = 'index')
    {
        $this->routes[] = [
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Dispatch the current request
     */
    public function dispatch()
    {
        $uri = $this->getUri();
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Try to match routes
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Remove the full match
                array_shift($matches);
                
                // Load controller
                return $this->callController($route['controller'], $route['action'], $matches);
            }
        }

        // No route matched - 404
        $this->notFound();
    }

    /**
     * Get the current URI
     */
    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Convert route path to regex pattern
     */
    private function convertToRegex($path)
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);
        
        // Convert :param to regex capture groups
        $pattern = preg_replace('/:[a-zA-Z0-9_]+/', '([a-zA-Z0-9_-]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Call the controller and action
     */
    private function callController($controllerName, $action, $params = [])
    {
        // Handle namespaced controllers (e.g., Admin\Admin)
        if (strpos($controllerName, '\\') !== false) {
            // Namespaced controller
            $controllerClass = $controllerName . 'Controller';
            $controllerPath = str_replace('\\', '/', $controllerName);
            $controllerFile = __DIR__ . '/../Controllers/' . $controllerPath . 'Controller.php';
        } else {
            // Regular controller
            $controllerClass = $controllerName . 'Controller';
            $controllerFile = __DIR__ . '/../Controllers/' . $controllerClass . '.php';
        }

        if (!file_exists($controllerFile)) {
            die("Controller not found: {$controllerFile}");
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            die("Controller class not found: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            die("Action not found: {$controllerClass}::{$action}");
        }

        // Call the action with params
        return call_user_func_array([$controller, $action], $params);
    }

    /**
     * 404 Not Found handler
     */
    private function notFound()
    {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <p class="text-xl text-gray-600 mb-8">Không tìm thấy trang bạn yêu cầu</p>
        <a href="/" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
            Về trang chủ
        </a>
    </div>
</body>
</html>';
        exit;
    }
}
