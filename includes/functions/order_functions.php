<?php
require_once __DIR__ . '/cart_functions.php';

// Hàm tạo đơn hàng từ giỏ hàng hiện tại
function createOrderFromCart(PDO $pdo): int
{
    $accountId = $_SESSION['id'];

    try {
        $pdo->beginTransaction();

        // Lấy thông tin giỏ hàng và tài khoản
        $cart = getCart($pdo);
        $shippingInfo = getShippingInfo($pdo, $accountId);

        validateOrderData($cart, $shippingInfo);

        // Xử lý đơn hàng
        $products = getProductsInfo($pdo, array_keys($cart));
        $totalAmount = calculateTotalAmount($cart, $products);

        $orderId = createOrder($pdo, $accountId, $shippingInfo, $totalAmount);
        createOrderDetails($pdo, $orderId, $cart, $products);

        clearCart($pdo, $accountId);

        $pdo->commit();
        return $orderId;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// Hàm lấy giỏ hàng của người dùng
// function getCart(PDO $pdo, int $accountId): array
// {
//     $stmt = $pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = ?");
//     $stmt->execute([$accountId]);
//     $cartJson = $stmt->fetchColumn();

//     return $cartJson ? json_decode($cartJson, true) : [];
// }

// Hàm lấy thông tin giao hàng của người dùng
function getShippingInfo(PDO $pdo, int $accountId): array
{
    $stmt = $pdo->prepare("SELECT name, phone, address FROM accounts WHERE id = ?");
    $stmt->execute([$accountId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// Hàm xác thực dữ liệu đơn hàng
function validateOrderData(array $cart, array $shippingInfo): void
{
    if (empty($cart)) {
        throw new Exception("Giỏ hàng của bạn đang trống.");
    }
    if (empty($shippingInfo['phone']) || empty($shippingInfo['address'])) {
        throw new Exception("Vui lòng cập nhật đầy đủ thông tin giao hàng trước khi đặt hàng.");
    }
}

// Hàm lấy thông tin sản phẩm từ database
function getProductsInfo(PDO $pdo, array $productIds): array
{
    if (empty($productIds)) return [];

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $stmt->execute($productIds);

    return $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
}

// Hàm tính tổng tiền đơn hàng
function calculateTotalAmount(array $cart, array $products): float
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

// Hàm tạo đơn hàng
function createOrder(PDO $pdo, int $accountId, array $shippingInfo, float $totalAmount): int
{
    $stmt = $pdo->prepare(
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

    return (int)$pdo->lastInsertId();
}

// Hàm tạo chi tiết đơn hàng
function createOrderDetails(PDO $pdo, int $orderId, array $cart, array $products): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO order_details (order_id, product_id, product_name, quantity, unit_price) 
         VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($cart as $productId => $quantity) {
        $product = $products[$productId];
        $stmt->execute([$orderId, $productId, $product['name'], $quantity, $product['price']]);
    }
}

// Hàm làm rỗng giỏ hàng của người dùng
function clearCart(PDO $pdo, int $accountId): void
{
    $stmt = $pdo->prepare("DELETE FROM user_carts WHERE account_id = ?");
    $stmt->execute([$accountId]);
}

// Hàm lấy thông tin tóm tắt đơn hàng để hiển thị trên trang thành công
function getOrderSummary(PDO $pdo, int $orderId): ?array
{
    $accountId = $_SESSION['id'];
    // Truy vấn chỉ lấy các trường cần thiết từ bảng `orders`
    $stmt = $pdo->prepare(
        "SELECT id, account_id, customer_name, shipping_address 
         FROM orders 
         WHERE id = ?"
    );
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // KIỂM TRA BẢO MẬT: Nếu không tìm thấy đơn hàng hoặc đơn hàng không thuộc về người dùng này -> trả về null
    if (!$order || $order['account_id'] !== $accountId) {
        return null;
    }

    // Trả về một mảng đã được định dạng sẵn sàng để hiển thị
    return [
        'order_code'       => '#' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
        'shipping_summary' => $order['customer_name'] . ', ' . $order['shipping_address']
    ];
}
