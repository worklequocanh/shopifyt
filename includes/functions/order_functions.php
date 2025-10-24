<?php

function createOrderFromCart(PDO $pdo): int
{
    // CẢI THIỆN: Việc kiểm tra đăng nhập đã được chuyển ra ngoài file gọi hàm này.
    $accountId = $_SESSION['id'];

    try {
        // 1. BẮT ĐẦU TRANSACTION
        $pdo->beginTransaction();

        // 2. LẤY GIỎ HÀNG VÀ THÔNG TIN KHÁCH HÀNG
        $cartStmt = $pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = ?");
        $cartStmt->execute([$accountId]);
        $cartJson = $cartStmt->fetchColumn();

        $accountStmt = $pdo->prepare("SELECT name, phone, address FROM accounts WHERE id = ?");
        $accountStmt->execute([$accountId]);
        $shippingInfo = $accountStmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartJson || empty(json_decode($cartJson, true))) {
            throw new Exception("Giỏ hàng của bạn đang trống.");
        }
        if (empty($shippingInfo['phone']) || empty($shippingInfo['address'])) {
            throw new Exception("Vui lòng cập nhật đầy đủ thông tin giao hàng trước khi đặt hàng.");
        }
        $cart = json_decode($cartJson, true);

        // 3. KIỂM TRA TỒN KHO, TÍNH TỔNG TIỀN
        $productIds = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $productStmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $productStmt->execute($productIds);

        // SỬA LỖI: Dùng FETCH_UNIQUE | FETCH_ASSOC để lấy id làm key và giữ lại tất cả các cột
        $productsInDb = $productStmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $totalAmount = 0;
        foreach ($cart as $productId => $quantity) {
            if (!isset($productsInDb[$productId])) {
                throw new Exception("Sản phẩm với ID {$productId} không tồn tại hoặc đã bị xóa.");
            }
            $product = $productsInDb[$productId];
            if ($quantity > $product['stock']) {
                throw new Exception("Sản phẩm '" . htmlspecialchars($product['name']) . "' không đủ số lượng tồn kho (chỉ còn " . $product['stock'] . ").");
            }
            $totalAmount += $product['price'] * $quantity;
        }

        // 4. TẠO ĐƠN HÀNG MỚI
        $orderStmt = $pdo->prepare(
            "INSERT INTO orders (account_id, customer_name, shipping_address, shipping_phone, total_amount) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $orderStmt->execute([$accountId, $shippingInfo['name'], $shippingInfo['address'], $shippingInfo['phone'], $totalAmount]);
        $orderId = $pdo->lastInsertId();

        // 5. THÊM CHI TIẾT ĐƠN HÀNG VÀ CẬP NHẬT KHO
        $orderDetailStmt = $pdo->prepare(
            "INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)"
        );
        $updateStockStmt = $pdo->prepare(
            "UPDATE products SET stock = stock - ? WHERE id = ?"
        );

        foreach ($cart as $productId => $quantity) {
            $product = $productsInDb[$productId];
            $orderDetailStmt->execute([$orderId, $productId, $quantity, $product['price']]);
            $updateStockStmt->execute([$quantity, $productId]);
        }

        // 6. LÀM RỖNG GIỎ HÀNG
        $deleteCartStmt = $pdo->prepare("DELETE FROM user_carts WHERE account_id = ?");
        $deleteCartStmt->execute([$accountId]);

        // 7. COMMIT TRANSACTION
        $pdo->commit();

        return (int)$orderId;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}


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
