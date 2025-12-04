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
    public function createFromCart(int $accountId, array $shippingData = [], array $selectedIds = [], ?string $voucherCode = null): array
    {
        try {
            $this->pdo->beginTransaction();

            // Load Cart and Voucher models
            require_once __DIR__ . '/Cart.php';
            require_once __DIR__ . '/Voucher.php';
            $cartModel = new Cart();
            $voucherModel = new Voucher();

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

            // Handle voucher if provided
            $voucherId = null;
            $discountAmount = 0;
            
            if (!empty($voucherCode)) {
                error_log("Voucher code provided: " . $voucherCode);
                $voucherValidation = $voucherModel->validate($voucherCode, $totalAmount);
                
                if ($voucherValidation['valid']) {
                    $voucher = $voucherValidation['voucher'];
                    $voucherId = $voucher['id'];
                    $discountAmount = $voucherModel->calculateDiscount($voucher, $totalAmount);
                    error_log("Voucher valid - ID: $voucherId, Discount: $discountAmount");
                } else {
                    error_log("Voucher validation failed: " . $voucherValidation['message']);
                    // Don't throw exception, just continue without voucher
                    // throw new Exception($voucherValidation['message']);
                }
            }

            // Create order with voucher
            $orderId = $this->createOrder($accountId, $shippingInfo, $totalAmount, $voucherId, $discountAmount);
            
            // Create order details
            $this->createOrderDetails($orderId, $cart, $products);

            // Deduct stock from inventory (NEW)
            if (!class_exists('Product')) {
                require_once __DIR__ . '/Product.php';
            }
            $productModel = new Product();
            
            foreach ($cart as $productId => $quantity) {
                // Stock was already checked in calculateTotalAmount, but double-check for safety
                if (!$productModel->checkStock($productId, $quantity)) {
                    throw new Exception("Sản phẩm ID {$productId} không đủ hàng trong kho");
                }
                // Deduct stock
                $productModel->decreaseStock($productId, $quantity);
            }

            // Track voucher usage if applied
            if ($voucherId) {
                $this->trackVoucherUsage($voucherId, $orderId, $accountId, $discountAmount);
            }

            // Remove ordered items from cart (only selected ones)
            foreach (array_keys($cart) as $productId) {
                $cartModel->removeItem($accountId, $productId);
            }

            $this->pdo->commit();
            
            // Send order confirmation email (after commit, non-blocking)
            try {
                $orderDetails = $this->getOrderById($orderId);
                if ($orderDetails) {
                    require_once __DIR__ . '/../Helpers/email_helpers.php';
                    $emailService = getEmailService();
                    $emailService->sendOrderConfirmation($orderDetails);
                    error_log("Order confirmation email sent for order #$orderId");
                }
            } catch (Exception $e) {
                // Log but don't fail the order if email fails
                error_log("Failed to send order confirmation email: " . $e->getMessage());
            }

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
    private function createOrder(int $accountId, array $shippingInfo, float $totalAmount, ?int $voucherId = null, float $discountAmount = 0): int
    {
        $finalAmount = $totalAmount - $discountAmount;
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (account_id, customer_name, shipping_address, shipping_phone, total_amount, voucher_id, discount_amount) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $accountId,
            $shippingInfo['name'],
            $shippingInfo['address'],
            $shippingInfo['phone'],
            $finalAmount,
            $voucherId,
            $discountAmount
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
    public function getOrderById(int $orderId, ?int $accountId = null): ?array
    {
        $sql = "SELECT o.*, 
                       a.name as customer_name, 
                       a.email as customer_email,
                       v.code as voucher_code,
                       v.name as voucher_name,
                       v.discount_type as voucher_discount_type
                FROM orders o
                LEFT JOIN accounts a ON o.account_id = a.id
                LEFT JOIN vouchers v ON o.voucher_id = v.id
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
            'order_id' => $order['id'],
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
            "SELECT o.*, 
                    v.code as voucher_code, 
                    v.name as voucher_name
             FROM orders o
             LEFT JOIN vouchers v ON o.voucher_id = v.id
             WHERE o.account_id = ? 
             ORDER BY o.order_date DESC 
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

    /**
     * Get revenue by product category
     */
    public function getRevenueByCategory(int $year): array
    {
        $sql = "SELECT c.name as category_name, 
                       COALESCE(SUM(od.subtotal), 0) as revenue,
                       COUNT(DISTINCT o.id) as order_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                LEFT JOIN order_details od ON od.product_id = p.id
                LEFT JOIN orders o ON o.id = od.order_id 
                    AND YEAR(o.order_date) = ?
                    AND o.status IN ('paid', 'shipped', 'accepted')
                GROUP BY c.id, c.name
                ORDER BY revenue DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 10, int $year = null): array
    {
        $yearCondition = $year ? "AND YEAR(o.order_date) = ?" : "";
        $params = $year ? [$year] : [];
        
        $sql = "SELECT p.name, p.price,
                       SUM(od.quantity) as total_quantity,
                       COALESCE(SUM(od.subtotal), 0) as total_revenue,
                       COUNT(DISTINCT o.id) as order_count
                FROM products p
                INNER JOIN order_details od ON od.product_id = p.id
                INNER JOIN orders o ON o.id = od.order_id 
                    AND o.status IN ('paid', 'shipped', 'accepted')
                    {$yearCondition}
                GROUP BY p.id, p.name, p.price
                ORDER BY total_quantity DESC
                LIMIT ?";
        
        $params[] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get order status distribution
     */
    public function getOrderStatusDistribution(int $year = null): array
    {
        $yearCondition = $year ? "WHERE YEAR(order_date) = ?" : "";
        $params = $year ? [$year] : [];
        
        $sql = "SELECT status, COUNT(*) as count
                FROM orders
                {$yearCondition}
                GROUP BY status
                ORDER BY count DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get average order value and other aggregate metrics
     */
    public function getAggregateMetrics(int $year): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_revenue,
                    COALESCE(AVG(total_amount), 0) as avg_order_value,
                    COALESCE(SUM(discount_amount), 0) as total_discounts
                FROM orders
                WHERE YEAR(order_date) = ?
                AND status IN ('paid', 'shipped', 'accepted')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get daily revenue trend for last N days
     */
    public function getDailyRevenueTrend(int $days = 30): array
    {
        $sql = "SELECT DATE(order_date) as date,
                       COALESCE(SUM(total_amount), 0) as revenue,
                       COUNT(*) as order_count
                FROM orders
                WHERE order_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND status IN ('paid', 'shipped', 'accepted')
                GROUP BY DATE(order_date)
                ORDER BY date ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer statistics
     */
    public function getCustomerStats(): array
    {
        $stats = [];
        
        // Total customers
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM accounts WHERE role = 'customer'");
        $stats['total_customers'] = $stmt->fetchColumn();
        
        // Customers who made purchases
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT account_id) FROM orders");
        $stats['active_customers'] = $stmt->fetchColumn();
        
        // Average customer lifetime value
        $stmt = $this->pdo->query("
            SELECT COALESCE(AVG(customer_total), 0) as avg_lifetime_value
            FROM (
                SELECT account_id, SUM(total_amount) as customer_total
                FROM orders
                WHERE status IN ('paid', 'shipped', 'accepted')
                GROUP BY account_id
            ) as customer_totals
        ");
        $stats['avg_lifetime_value'] = $stmt->fetchColumn();
        
        return $stats;
    }

    /**
     * Get top customers by spending
     */
    public function getTopCustomers(int $limit = 5): array
    {
        $sql = "SELECT a.name, a.email,
                       COUNT(o.id) as total_orders,
                       COALESCE(SUM(o.total_amount), 0) as total_spent
                FROM accounts a
                INNER JOIN orders o ON o.account_id = a.id
                WHERE a.role = 'customer'
                AND o.status IN ('paid', 'shipped', 'accepted')
                GROUP BY a.id, a.name, a.email
                ORDER BY total_spent DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 10): array
    {
        $sql = "SELECT o.id, o.total_amount, o.status, o.order_date,
                       a.name as customer_name
                FROM orders o
                LEFT JOIN accounts a ON a.id = o.account_id
                ORDER BY o.order_date DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): array
    {
        $sql = "SELECT id, name, stock, price
                FROM products
                WHERE is_active = 1
                AND stock <= ?
                ORDER BY stock ASC
                LIMIT 10";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Track voucher usage
     */
    private function trackVoucherUsage(int $voucherId, int $orderId, int $accountId, float $discountAmount): void
    {
        // Insert into voucher_usage table
        $stmt = $this->pdo->prepare(
            "INSERT INTO voucher_usage (voucher_id, order_id, account_id, discount_amount) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$voucherId, $orderId, $accountId, $discountAmount]);
        
        // Increment used_count in vouchers table
        $stmt = $this->pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
        $stmt->execute([$voucherId]);
    }
}
