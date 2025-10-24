<?php

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function saveCartToDb(PDO $pdo): void
{
    if (isLoggedIn()) {
        $userId = $_SESSION['id'];
        $cartDataJson = json_encode($_SESSION['cart']);

        $sql = "INSERT INTO user_carts (account_id, cart_data) 
                VALUES (:user_id, :cart_data)
                ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':cart_data' => $cartDataJson
        ]);
    }
}

function getCart(PDO $pdo): array
{
    $accountId = $_SESSION['id'];
    $stmt = $pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = :account_id");
    $stmt->execute([':account_id' => $accountId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['cart_data'])) {
        return json_decode($result['cart_data'], true);
    } else {
        $emptyCartJson = '[]';

        $insertStmt = $pdo->prepare(
            "INSERT INTO user_carts (account_id, cart_data)
                    VALUES (:account_id, :cart_data)"
        );
        $insertStmt->execute([
            ':account_id' => $accountId,
            ':cart_data' => $emptyCartJson
        ]);

        return [];
    }
}

function getCartData(PDO $pdo): array
{
    $response = [
        'items' => [],
        'total_amount' => 0.00
    ];

    if (!isLoggedIn()) {
        return $response;
    }
    $accountId = $_SESSION['id'];

    $stmt = $pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = :account_id");
    $stmt->execute([':account_id' => $accountId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $cart = ($result && $result['cart_data']) ? json_decode($result['cart_data'], true) : [];

    if (empty($cart)) {
        return $response;
    }

    $product_ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    $sql = "SELECT 
                p.id,
                p.name,
                p.price,
                pi.image_url AS main_image
            FROM 
                products AS p
            LEFT JOIN 
                product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
            WHERE
                p.id IN ($placeholders)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($product_ids);
    $products_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    $total_amount = 0.00;

    foreach ($products_from_db as $product) {
        $product_id = $product['id'];
        $quantity = $cart[$product_id];
        $price = (float)$product['price'];

        $items[] = [
            'id'             => $product_id,
            'name'           => $product['name'],
            'quantity'       => $quantity,
            'price'          => $price,
            'main_image' => $product['main_image']
        ];

        $total_amount += $price * $quantity;
    }

    $response['items'] = $items;
    $response['total_amount'] = $total_amount;

    return $response;
}