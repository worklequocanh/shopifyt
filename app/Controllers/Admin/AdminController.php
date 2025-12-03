<?php

namespace Admin;

require_once __DIR__ . '/../BaseController.php';

use BaseController;
use Database;
use Permission;
use Order;
use Product;
use Account;
use PDO;

/**
 * Admin Dashboard Controller
 * Main admin panel with statistics and overview
 */
class AdminController extends BaseController
{
    private $orderModel;
    private $productModel;
    private $accountModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require admin or employee role
        $this->requireAnyRole(['admin', 'employee']);
        $this->requirePermission(Permission::VIEW_DASHBOARD);
        
        // Load models
        require_once __DIR__ . '/../../Models/Order.php';
        require_once __DIR__ . '/../../Models/Product.php';
        require_once __DIR__ . '/../../Models/Account.php';
        
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->accountModel = new Account();
    }

    /**
     * Admin dashboard - statistics and overview
     */
    public function index()
    {
        $page_title = 'Admin Dashboard';
        
        // Get statistics
        $stats = $this->getStatistics();
        
        // Get recent orders
        $recentOrders = $this->getRecentOrders(10);
        
        // Get top products
        $topProducts = $this->getTopProducts(5);
        
        // Get revenue chart data
        $revenueData = $this->getRevenueChartData();

        $this->view('admin/dashboard', [
            'page_title' => $page_title,
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
            'revenue_data' => $revenueData,
            'current_role' => $this->getRole()
        ], 'admin'); // Use admin layout
    }

    /**
     * Get dashboard statistics
     */
    private function getStatistics(): array
    {
        $pdo = Database::getInstance()->getConnection();
        
        // Total revenue
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
                             FROM orders WHERE status = 'accepted'");
        $totalRevenue = $stmt->fetch()['total_revenue'];
        
        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
        $totalOrders = $stmt->fetch()['total_orders'];
        
        // Pending orders
        $stmt = $pdo->query("SELECT COUNT(*) as pending_orders 
                             FROM orders WHERE status = 'pending'");
        $pendingOrders = $stmt->fetch()['pending_orders'];
        
        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) as total_products 
                             FROM products WHERE is_active = 1");
        $totalProducts = $stmt->fetch()['total_products'];
        
        // Total customers
        $stmt = $pdo->query("SELECT COUNT(*) as total_customers 
                             FROM accounts WHERE role = 'customer'");
        $totalCustomers = $stmt->fetch()['total_customers'];
        
        // Revenue this month
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as month_revenue 
                             FROM orders 
                             WHERE status = 'accepted' 
                             AND MONTH(order_date) = MONTH(CURRENT_DATE())
                             AND YEAR(order_date) = YEAR(CURRENT_DATE())");
        $monthRevenue = $stmt->fetch()['month_revenue'];
        
        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers,
            'month_revenue' => $monthRevenue
        ];
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders(int $limit = 10): array
    {
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("
            SELECT o.*, a.name as customer_name, a.email as customer_email
            FROM orders o
            LEFT JOIN accounts a ON o.account_id = a.id
            ORDER BY o.order_date DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get top selling products
     */
    private function getTopProducts(int $limit = 5): array
    {
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price,
                   (SELECT image_url FROM product_images 
                    WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image,
                   COALESCE(SUM(od.quantity), 0) as total_sold,
                   COALESCE(SUM(od.quantity * od.unit_price), 0) as total_revenue
            FROM products p
            LEFT JOIN order_details od ON p.id = od.product_id
            LEFT JOIN orders o ON od.order_id = o.id AND o.status = 'accepted'
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get revenue chart data (last 6 months)
     */
    private function getRevenueChartData(): array
    {
        $pdo = Database::getInstance()->getConnection();
        
        $sql = "SELECT 
                    DATE_FORMAT(order_date, '%Y-%m') as month,
                    SUM(total_amount) as revenue
                FROM orders 
                WHERE status = 'accepted' 
                AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                ORDER BY month ASC";
                
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Fill missing months
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $data[$month] = $results[$month] ?? 0;
        }
        
        return [
            'labels' => array_keys($data),
            'values' => array_values($data)
        ];
    }
}
