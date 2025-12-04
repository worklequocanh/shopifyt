<?php

namespace Admin;

require_once __DIR__ . '/../BaseController.php';

use BaseController;
use Database;
use Permission;
use Order;
use PDO;

/**
 * Admin Orders Controller  
 * Manage orders - view, update status
 */
class AdminOrderController extends BaseController
{
    private $orderModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require permission to view all orders
        $this->requireAnyRole(['admin', 'employee']);
        $this->requirePermission(Permission::VIEW_ALL_ORDERS);
        
        // Load model
        require_once __DIR__ . '/../../Models/Order.php';
        $this->orderModel = new Order();
    }

    /**
     * Order list
     */
    /**
     * Order list
     */
    public function index()
    {
        $page = max(1, (int)$this->get('page', 1));
        $status = $this->get('status', '');
        $search = $this->get('search', '');
        $startDate = $this->get('start_date', '');
        $endDate = $this->get('end_date', '');
        
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Build query
        $pdo = Database::getInstance()->getConnection();
        $where = [];
        $params = [];

        if (!empty($status)) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $where[] = "(a.name LIKE ? OR a.email LIKE ? OR o.id = ? OR o.shipping_phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $search;
            $params[] = $searchTerm;
        }

        if (!empty($startDate)) {
            $where[] = "DATE(o.order_date) >= ?";
            $params[] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = "DATE(o.order_date) <= ?";
            $params[] = $endDate;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get orders
        $stmt = $pdo->prepare("
            SELECT o.*, a.name as customer_name, a.email as customer_email
            FROM orders o
            LEFT JOIN accounts a ON o.account_id = a.id
            {$whereClause}
            ORDER BY o.order_date DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Count total
        $countParams = array_slice($params, 0, -2); // Remove limit & offset
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN accounts a ON o.account_id = a.id {$whereClause}");
        $stmt->execute($countParams);
        $totalOrders = $stmt->fetchColumn();
        $totalPages = ceil($totalOrders / $perPage);

        $this->view('admin/orders/index', [
            'page_title' => 'Quản lý đơn hàng',
            'orders' => $orders,
            'totalPages' => $totalPages,
            'current_page' => $page,
            'status' => $status,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'current_role' => $this->getRole()
        ], 'admin');
    }

    /**
     * Order detail
     */
    public function detail($id)
    {
        $order = $this->orderModel->getOrderById($id);
        
        if (!$order) {
            setFlashMessage('error', 'Đơn hàng không tồn tại.');
            $this->redirect('/admin/orders');
            return;
        }

        $this->view('admin/orders/detail', [
            'page_title' => 'Chi tiết đơn hàng #' . str_pad($id, 6, '0', STR_PAD_LEFT),
            'order' => $order,
            'current_role' => $this->getRole()
        ], 'admin');
    }

    /**
     * Update order status
     */
    public function updateStatus()
    {
        $id = $this->post('id');
        try {
            $this->requirePermission(Permission::MANAGE_ORDERS);
            
            $order = $this->orderModel->getOrderById($id);
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
                return;
            }

            $newStatus = $this->post('status');
            $currentStatus = $order['status'];
            
            if (!in_array($newStatus, ['pending', 'accepted', 'cancelled'])) {
                $this->json(['success' => false, 'message' => 'Trạng thái không hợp lệ'], 400);
                return;
            }

            if ($currentStatus === $newStatus) {
                $this->json(['success' => true, 'message' => 'Trạng thái không thay đổi']);
                return;
            }

            $this->pdo->beginTransaction();
            
            // Load Product model for stock updates
            if (!class_exists('Product')) {
                require_once __DIR__ . '/../../Models/Product.php';
            }
            $productModel = new \Product();

            // Logic: Cancelled -> Restore Stock
            if ($newStatus === 'cancelled' && $currentStatus !== 'cancelled') {
                foreach ($order['items'] as $item) {
                    $productModel->increaseStock($item['product_id'], $item['quantity']);
                }
            }
            // Logic: Cancelled -> Active (Pending/Accepted) -> Deduct Stock
            elseif ($currentStatus === 'cancelled' && $newStatus !== 'cancelled') {
                // Check stock first
                foreach ($order['items'] as $item) {
                    if (!$productModel->checkStock($item['product_id'], $item['quantity'])) {
                        throw new \Exception("Sản phẩm '{$item['product_name']}' không đủ hàng để khôi phục đơn.");
                    }
                }
                // Deduct stock
                foreach ($order['items'] as $item) {
                    $productModel->decreaseStock($item['product_id'], $item['quantity']);
                }
            }

            // Update status
            $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);

            $this->pdo->commit();
            
            // Send status update email (only for accepted/cancelled)
            if (in_array($newStatus, ['accepted', 'cancelled'])) {
                try {
                    // Refresh order data to get latest info
                    $updatedOrder = $this->orderModel->getOrderById($id);
                    if ($updatedOrder) {
                        require_once __DIR__ . '/../../Helpers/email_helpers.php';
                        $emailService = getEmailService();
                        $emailService->sendOrderStatusUpdate($updatedOrder, $currentStatus, $newStatus);
                        error_log("Order status email sent for order #$id: $currentStatus -> $newStatus");
                    }
                } catch (Exception $e) {
                    error_log("Failed to send order status email: " . $e->getMessage());
                }
            }
            
            $statusText = match($newStatus) {
                'accepted' => 'Đã chấp nhận',
                'cancelled' => 'Đã hủy',
                'pending' => 'Chờ xử lý',
                default => $newStatus
            };
            
            $this->json(['success' => true, 'message' => "Cập nhật trạng thái: {$statusText}"]);

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order Update Error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
