<?php

/**
 * Base Controller
 * Parent class for all controllers
 */
class BaseController
{
    protected $db;
    protected $pdo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Render a view
     * 
     * @param string $view View file path (e.g., 'home/index')
     * @param array $data Data to pass to the view
     * @param bool $useLayout Whether to use main layout
     */
    protected function view(string $view, array $data = [], $useLayout = true)
    {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            die("View not found: {$view}");
        }

        include $viewFile;

        // Get the view content
        $content = ob_get_clean();

        // If using layout, wrap content in layout
        if ($useLayout) {
            // Check for custom layout
            if (is_string($useLayout)) {
                $layoutFile = __DIR__ . '/../Views/layouts/' . $useLayout . '.php';
                if (file_exists($layoutFile)) {
                    include $layoutFile;
                } else {
                    include __DIR__ . '/../Views/layouts/main.php';
                }
            } else {
                include __DIR__ . '/../Views/layouts/main.php';
            }
        } else {
            echo $content;
        }
    }

    /**
     * Return JSON response
     */
    protected function json($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url)
    {
        redirect($url);
    }

    /**
     * Require user to be logged in
     */
    protected function requireAuth(): void
    {
        if (!Permission::isLoggedIn()) {
            setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('/auth/login');
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole(string $role): void
    {
        Permission::requireRole($role);
    }

    /**
     * Require any of the given roles
     */
    protected function requireAnyRole(array $roles): void
    {
        Permission::requireAnyRole($roles);
    }

    /**
     * Require specific permission
     */
    protected function requirePermission(string $permission): void
    {
        Permission::requirePermission($permission);
    }

    /**
     * Check if user has permission
     */
    protected function can(string $permission): bool
    {
        return Permission::can($permission);
    }

    /**
     * Check if user has role
     */
    protected function hasRole(string $role): bool
    {
        return Permission::hasRole($role);
    }

    /**
     * Get current user's role
     */
    protected function getRole(): string
    {
        return Permission::getCurrentRole();
    }

    /**
     * Check if user is staff (admin/employee)
     */
    protected function isStaff(): bool
    {
        return Permission::isStaff();
    }

    /**
     * Get POST data
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
