<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Cart Model
 * Handles shopping cart operations
 */
class Cart extends BaseModel
{
    protected $table = 'user_carts';

    /**
     * Get cart for a user
     */
    public function getCart(int $accountId): array
    {
        $stmt = $this->pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = ?");
        $stmt->execute([$accountId]);
        $result = $stmt->fetch();

        if ($result && isset($result['cart_data'])) {
            return json_decode($result['cart_data'], true) ?: [];
        }

        // Create empty cart if doesn't exist
        $this->createEmptyCart($accountId);
        return [];
    }

    /**
     * Create empty cart for user
     */
    private function createEmptyCart(int $accountId): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_carts (account_id, cart_data) VALUES (?, ?)"
        );
        $stmt->execute([$accountId, '[]']);
    }

    /**
     * Get cart with product details
     */
    public function getCartWithDetails(int $accountId): array
    {
        $response = [
            'items' => [],
            'total_amount' => 0.00
        ];

        $cart = $this->getCart($accountId);

        if (empty($cart)) {
            return $response;
        }

        $productIds = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.stock,
                    pi.image_url AS main_image
                FROM 
                    products AS p
                LEFT JOIN 
                    product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
                WHERE
                    p.id IN ($placeholders) AND p.is_active = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($productIds);
        $productsFromDb = $stmt->fetchAll();

        $items = [];
        $totalAmount = 0.00;

        foreach ($productsFromDb as $product) {
            $productId = $product['id'];
            $quantity = $cart[$productId];
            $price = (float)$product['price'];

            $items[] = [
                'id' => $productId,
                'name' => $product['name'],
                'quantity' => $quantity,
                'price' => $price,
                'stock' => $product['stock'],
                'main_image' => $product['main_image'],
                'subtotal' => $price * $quantity
            ];

            $totalAmount += $price * $quantity;
        }

        $response['items'] = $items;
        $response['total_amount'] = $totalAmount;

        return $response;
    }

    /**
     * Add item to cart
     */
    public function addItem(int $accountId, int $productId, int $quantity): array
    {
        try {
            $this->pdo->beginTransaction();

            // Check product exists and has stock
            $stmt = $this->pdo->prepare("SELECT name, stock, is_active FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product || $product['is_active'] != 1) {
                return ['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.'];
            }

            // Get current cart
            $cart = $this->getCart($accountId);
            $currentQty = $cart[$productId] ?? 0;
            $newTotalQty = $currentQty + $quantity;

            if ($newTotalQty > $product['stock']) {
                return ['success' => false, 'message' => 'Số lượng yêu cầu vượt quá số hàng tồn kho (' . $product['stock'] . ').'];
            }

            // Update cart
            $cart[$productId] = $newTotalQty;
            $this->saveCart($accountId, $cart);

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Đã thêm "' . htmlspecialchars($product['name']) . '" vào giỏ hàng!'];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu, vui lòng thử lại sau.'];
        }
    }

    /**
     * Update item quantity
     */
    public function updateItem(int $accountId, int $productId, int $quantity): array
    {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($accountId, $productId);
            }

            // Check stock
            $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                return ['success' => false, 'message' => 'Sản phẩm không tồn tại.'];
            }

            if ($quantity > $product['stock']) {
                return ['success' => false, 'message' => 'Số lượng vượt quá tồn kho (' . $product['stock'] . ').'];
            }

            // Update cart
            $cart = $this->getCart($accountId);
            $cart[$productId] = $quantity;
            $this->saveCart($accountId, $cart);

            return ['success' => true, 'message' => 'Đã cập nhật giỏ hàng!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi khi cập nhật giỏ hàng.'];
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $accountId, int $productId): array
    {
        try {
            $cart = $this->getCart($accountId);
            
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $this->saveCart($accountId, $cart);
                return ['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!'];
            }

            return ['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi khi xóa sản phẩm.'];
        }
    }

    /**
     * Clear cart
     */
    public function clearCart(int $accountId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM user_carts WHERE account_id = ?");
            return $stmt->execute([$accountId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Save cart to database
     */
    private function saveCart(int $accountId, array $cart): void
    {
        $cartJson = json_encode($cart);
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_carts (account_id, cart_data) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data)"
        );
        $stmt->execute([$accountId, $cartJson]);
    }

    /**
     * Get cart item count
     */
    public function getItemCount(int $accountId): int
    {
        $cart = $this->getCart($accountId);
        return array_sum($cart);
    }
}
