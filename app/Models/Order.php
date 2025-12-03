<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Order Model
 * Handles order creation and management
 */
class Order extends BaseModel
{
    protected $table = 'orders';

    /**
     * Create order from cart
     */
    public function createFromCart(int $accountId, array $shippingData = [], array $selectedIds = []): array
    {
        try {
            $this->pdo->beginTransaction();

            // Load Cart model
            require_once __DIR__ . '/Cart.php';
            $cartModel = new Cart();

            // Get cart
            $cart = $cartModel->getCart($accountId);
            
            // Filter only selected items if specified
            if (!empty($selectedIds)) {
                $cart = array_filter($cart, function($qty, $productId) use ($selectedIds) {
                    return in_array($productId, $selectedIds);
                }, ARRAY_FILTER_USE_BOTH);
            }
            
            // Get shipping info - use provided data or fetch from account
            if (!empty($shippingData)) {
                $shippingInfo = [
                    'name' => $shippingData['customer_name'],
                    'phone' => $shippingData['shipping_phone'],
                    'address' => $shippingData['shipping_address']
                ];
            } else {
                $shippingInfo = $this->getShippingInfo($accountId);
            }

            // Validate
            $this->validateOrderData($cart, $shippingInfo);

            // Get products and calculate total
            $products = $this->getProductsInfo(array_keys($cart));
            $totalAmount = $this->calculateTotalAmount($cart, $products);

            // Create order
            $orderId = $this->createOrder($accountId, $shippingInfo, $totalAmount);
            
            // Create order details
            $this->createOrderDetails($orderId, $cart, $products);

            // Remove ordered items from cart (only selected ones)
            foreach (array_keys($cart) as $productId) {
                $cartModel->removeItem($accountId, $productId);
            }

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Đặt hàng thành công!', 'order_id' => $orderId];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get shipping information
     */
    private function getShippingInfo(int $accountId): array
    {
        $stmt = $this->pdo->prepare("SELECT name, phone, address FROM accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Validate order data
     */
    private function validateOrderData(array $cart, array $shippingInfo): void
    {
        if (empty($cart)) {
            throw new Exception("Giỏ hàng của bạn đang trống.");
        }
        if (empty($shippingInfo['phone']) || empty($shippingInfo['address'])) {
            throw new Exception("Vui lòng cập nhật đầy đủ thông tin giao hàng trước khi đặt hàng.");
        }
    }

    /**
     * Get products info with lock
     */
    private function getProductsInfo(array $productIds): array
    {
        if (empty($productIds)) return [];

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $stmt->execute($productIds);

        return $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Calculate total amount
     */
    private function calculateTotalAmount(array $cart, array $products): float
    {
        $total = 0;
        foreach ($cart as $productId => $quantity) {
            if (!isset($products[$productId])) {
                throw new Exception("Sản phẩm với ID {$productId} không tồn tại hoặc đã bị xóa.");
            }

            $product = $products[$productId];
            if ($quantity > $product['stock']) {
                throw new Exception("Sản phẩm '" . htmlspecialchars($product['name']) . "' không đủ số lượng tồn kho (chỉ còn " . $product['stock'] . ").");
            }

            $total += $product['price'] * $quantity;
        }
        return $total;
    }

    /**
     * Create order record
     */
    private function createOrder(int $accountId, array $shippingInfo, float $totalAmount): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (account_id, customer_name, shipping_address, shipping_phone, total_amount) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $accountId,
            $shippingInfo['name'],
            $shippingInfo['address'],
            $shippingInfo['phone'],
            $totalAmount
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Create order details
     */
    private function createOrderDetails(int $orderId, array $cart, array $products): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO order_details (order_id, product_id, product_name, quantity, unit_price) 
             VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($cart as $productId => $quantity) {
            $product = $products[$productId];
            $stmt->execute([$orderId, $productId, $product['name'], $quantity, $product['price']]);
        }
    }

    /**
     * Get order by ID
     */
    /**
     * Get order by ID
     */
    public function getOrderById(int $orderId, ?int $accountId = null): ?array
    {
        $sql = "SELECT o.*, a.name as customer_name, a.email as customer_email 
                FROM orders o
                LEFT JOIN accounts a ON o.account_id = a.id
                WHERE o.id = ?";
        $params = [$orderId];

        if ($accountId !== null) {
            $sql .= " AND o.account_id = ?";
            $params[] = $accountId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if ($order) {
            $order['items'] = $this->getOrderItems($orderId);
        }

        return $order ?: null;
    }

    /**
     * Get order items
     */
    private function getOrderItems(int $orderId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT od.*, 
                    (SELECT image_url FROM product_images 
                     WHERE product_id = od.product_id AND is_main = 1 
                     LIMIT 1) as main_image
             FROM order_details od
             WHERE od.order_id = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Get order summary for success page
     */
    public function getOrderSummary(int $orderId, int $accountId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, account_id, customer_name, shipping_address 
             FROM orders 
             WHERE id = ?"
        );
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        // Security check
        if (!$order || $order['account_id'] !== $accountId) {
            return null;
        }

        return [
            'order_code' => '#' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
            'shipping_summary' => $order['customer_name'] . ', ' . $order['shipping_address']
        ];
    }

    /**
     * Get orders by user
     */
    public function getByUser(int $accountId, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM orders 
             WHERE account_id = ? 
             ORDER BY order_date DESC 
             LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $accountId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $validStatuses = ['pending', 'accepted', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update($orderId, ['status' => $status]);
    }

    /**
     * Get revenue by month for a specific year
     */
    public function getRevenueByMonth(int $year): array
    {
        $sql = "SELECT MONTH(order_date) as month, 
                       SUM(total_amount) as revenue, 
                       COUNT(id) as order_count
                FROM {$this->table}
                WHERE YEAR(order_date) = ? 
                AND status IN ('paid', 'shipped', 'accepted')
                GROUP BY MONTH(order_date)
                ORDER BY month";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['month']] = $row;
        }
        return $result;
    }

    /**
     * Get KPI data for employees
     */
    public function getEmployeeKPI(int $month, int $year): array
    {
        $sql = "SELECT a.id, a.name, a.position,
                       COUNT(DISTINCT o.id) as orders_processed,
                       COALESCE(SUM(o.total_amount), 0) as total_sales,
                       COALESCE(AVG(o.total_amount), 0) as avg_order_value
                FROM accounts a
                LEFT JOIN orders o ON o.account_id = a.id 
                    AND MONTH(o.order_date) = ? 
                    AND YEAR(o.order_date) = ?
                    AND o.status IN ('paid', 'shipped', 'accepted')
                WHERE a.role IN ('admin', 'employee')
                GROUP BY a.id, a.name, a.position
                ORDER BY total_sales DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total sales for a specific month/year (for comparison)
     */
    public function getTotalSalesByMonth(int $month, int $year): array
    {
        $sql = "SELECT a.id,
                       COALESCE(SUM(o.total_amount), 0) as total_sales
                FROM accounts a
                LEFT JOIN orders o ON o.account_id = a.id 
                    AND MONTH(o.order_date) = ? 
                    AND YEAR(o.order_date) = ?
                    AND o.status IN ('paid', 'shipped', 'accepted')
                WHERE a.role IN ('admin', 'employee')
                GROUP BY a.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$month, $year]);
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['id']] = $row['total_sales'];
        }
        return $result;
    }

    /**
     * Get quick stats for dashboard
     */
    public function getQuickStats(): array
    {
        $stats = [];
        
        // Total Revenue
        $stmt = $this->pdo->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('paid', 'shipped', 'accepted')");
        $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
        
        // Total Orders
        $stats['total_orders'] = $this->count();
        
        // Pending Orders
        $stats['pending_orders'] = $this->count("status = 'pending'");
        
        // Total Products
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
        $stats['total_products'] = $stmt->fetchColumn();
        
        // Total Customers
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM accounts WHERE role = 'customer'");
        $stats['total_customers'] = $stmt->fetchColumn();
        
        return $stats;
    }
}
