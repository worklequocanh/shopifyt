<?php
require_once 'includes/config.php';
session_start();

// Thiết lập page title và breadcrumbs
$page_title = 'Giỏ hàng';
$breadcrumbs = [
    ['text' => 'Trang chủ', 'url' => 'index.php', 'icon' => 'fas fa-home'],
    ['text' => 'Giỏ hàng']
];

// Xử lý thêm/xóa/cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $product_id = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];

                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                break;

            case 'update':
                $product_id = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];

                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                break;

            case 'remove':
                $product_id = (int)$_POST['product_id'];
                unset($_SESSION['cart'][$product_id]);
                break;

            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }

        // Redirect để tránh resubmit
        header('Location: shopping-cart.php');
        exit;
    }
}

// Lấy thông tin sản phẩm trong giỏ hàng
$cart_items = [];
$total_amount = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

    try {
        $stmt = $pdo->prepare("SELECT p.*, pi.image_url FROM products p 
                               LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                               WHERE p.id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $quantity = $_SESSION['cart'][$product['id']];
            $subtotal = $product['price'] * $quantity;
            $total_amount += $subtotal;

            $cart_items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    } catch (Exception $e) {
        $error_message = "Lỗi tải giỏ hàng: " . $e->getMessage();
    }
}

// Include header
include 'includes/layouts/header.php';
?>

<!-- Main Content -->
<main class="section">
    <div class="container">
        <div class="text-center mb-8">
            <h1 class="page-title">Giỏ hàng của bạn</h1>
            <p class="page-subtitle">Kiểm tra và chỉnh sửa sản phẩm trước khi thanh toán</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Giỏ hàng trống</h2>
                <p>Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i>
                    Bắt đầu mua sắm
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item):
                        $product = $item['product'];
                        $quantity = $item['quantity'];
                        $subtotal = $item['subtotal'];
                        $image_url = $product['image_url'] ?: 'assets/img/product/product-1.jpg';
                    ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>

                            <div class="cart-item-details">
                                <h3 class="cart-item-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="cart-item-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="cart-item-price">
                                    <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>

                            <div class="cart-item-quantity">
                                <form method="POST" class="quantity-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="button" class="quantity-btn minus" onclick="updateQuantity(<?php echo $product['id']; ?>, <?php echo $quantity - 1; ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" max="99" class="quantity-input" onchange="updateQuantity(<?php echo $product['id']; ?>, this.value)">
                                    <button type="button" class="quantity-btn plus" onclick="updateQuantity(<?php echo $product['id']; ?>, <?php echo $quantity + 1; ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="cart-item-subtotal">
                                <span class="subtotal-amount"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                            </div>

                            <div class="cart-item-actions">
                                <button class="remove-btn" onclick="removeItem(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="summary-header">
                        <h3>Tóm tắt đơn hàng</h3>
                    </div>

                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span class="free-shipping">Miễn phí</span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                        </div>
                    </div>

                    <div class="summary-actions">
                        <a href="products.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i>
                            Tiếp tục mua sắm
                        </a>
                        <a href="checkout.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card"></i>
                            Thanh toán
                        </a>
                    </div>

                    <div class="summary-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Bảo hành chính hãng</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-truck"></i>
                            <span>Giao hàng miễn phí</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-undo"></i>
                            <span>Đổi trả trong 30 ngày</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clear Cart -->
            <div class="text-center mt-8">
                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm trong giỏ hàng?')">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash"></i>
                        Xóa tất cả
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
// Page-specific JavaScript
$page_scripts = '
    function updateQuantity(productId, quantity) {
        if (quantity < 1) {
            removeItem(productId);
            return;
        }
        
        const form = document.createElement("form");
        form.method = "POST";
        form.innerHTML = `
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="${productId}">
            <input type="hidden" name="quantity" value="${quantity}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
    
    function removeItem(productId) {
        if (confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="${productId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
';

// Include footer
include 'includes/layouts/footer.php';
?>