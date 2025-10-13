<?php
require_once 'includes/config.php';
session_start();

// Thiết lập page title và breadcrumbs
$page_title = 'Thanh toán';
$breadcrumbs = [
    ['text' => 'Trang chủ', 'url' => 'index.php', 'icon' => 'fas fa-home'],
    ['text' => 'Giỏ hàng', 'url' => 'shopping-cart.php'],
    ['text' => 'Thanh toán']
];

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: shopping-cart.php');
    exit;
}

// Xử lý form thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    $errors = [];

    if (empty($customer_name)) {
        $errors[] = 'Vui lòng nhập họ và tên';
    }

    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vui lòng nhập email hợp lệ';
    }

    if (empty($customer_phone)) {
        $errors[] = 'Vui lòng nhập số điện thoại';
    }

    if (empty($customer_address)) {
        $errors[] = 'Vui lòng nhập địa chỉ giao hàng';
    }

    if (empty($payment_method)) {
        $errors[] = 'Vui lòng chọn phương thức thanh toán';
    }

    if (empty($errors)) {
        try {
            // Tính tổng tiền
            $total_amount = 0;
            $order_items = [];

            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                $quantity = $_SESSION['cart'][$product['id']];
                $subtotal = $product['price'] * $quantity;
                $total_amount += $subtotal;

                $order_items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }

            // Tạo đơn hàng
            $pdo->beginTransaction();

            // Insert vào bảng orders
            $order_stmt = $pdo->prepare("INSERT INTO orders (account_id, total_amount, status) VALUES (?, ?, 'pending')");
            $order_stmt->execute([1, $total_amount]); // account_id = 1 cho guest
            $order_id = $pdo->lastInsertId();

            // Insert vào bảng order_details
            $detail_stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            foreach ($order_items as $item) {
                $detail_stmt->execute([
                    $order_id,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price']
                ]);
            }

            $pdo->commit();

            // Lưu thông tin đơn hàng vào session
            $_SESSION['order_info'] = [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'customer_address' => $customer_address,
                'payment_method' => $payment_method,
                'total_amount' => $total_amount,
                'shipping_fee' => 0,
                'final_total' => $total_amount,
                'notes' => $notes
            ];

            // Xóa giỏ hàng
            unset($_SESSION['cart']);

            // Redirect đến trang thành công
            header('Location: checkout-success.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Lỗi xử lý đơn hàng: " . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Lấy thông tin sản phẩm trong giỏ hàng
$cart_items = [];
$total_amount = 0;

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
    $cart_items = [];
    $error_message = "Lỗi tải giỏ hàng: " . $e->getMessage();
}

// Include header
include 'includes/layouts/header.php';
?>

<!-- Main Content -->
<main class="section">
    <div class="container">
        <div class="text-center mb-8">
            <h1 class="page-title">Thanh toán</h1>
            <p class="page-subtitle">Hoàn tất thông tin để đặt hàng</p>
        </div>

        <div class="checkout-layout">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <form method="POST" class="checkout-form-content">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-user"></i>
                            Thông tin khách hàng
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_name">Họ và tên *</label>
                                <input type="text" id="customer_name" name="customer_name"
                                    value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>"
                                    required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="customer_email">Email *</label>
                                <input type="email" id="customer_email" name="customer_email"
                                    value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>"
                                    required class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_phone">Số điện thoại *</label>
                                <input type="tel" id="customer_phone" name="customer_phone"
                                    value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>"
                                    required class="form-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="customer_address">Địa chỉ giao hàng *</label>
                            <textarea id="customer_address" name="customer_address"
                                required class="form-textarea" rows="3"><?php echo htmlspecialchars($_POST['customer_address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-credit-card"></i>
                            Phương thức thanh toán
                        </h2>

                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cod"
                                    <?php echo ($_POST['payment_method'] ?? '') === 'cod' ? 'checked' : ''; ?>>
                                <div class="payment-method-content">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div>
                                        <h4>Thanh toán khi nhận hàng</h4>
                                        <p>Thanh toán bằng tiền mặt khi nhận hàng</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="bank_transfer"
                                    <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : ''; ?>>
                                <div class="payment-method-content">
                                    <i class="fas fa-university"></i>
                                    <div>
                                        <h4>Chuyển khoản ngân hàng</h4>
                                        <p>Chuyển khoản qua ngân hàng</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="momo"
                                    <?php echo ($_POST['payment_method'] ?? '') === 'momo' ? 'checked' : ''; ?>>
                                <div class="payment-method-content">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>
                                        <h4>Ví MoMo</h4>
                                        <p>Thanh toán qua ví điện tử MoMo</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="vnpay"
                                    <?php echo ($_POST['payment_method'] ?? '') === 'vnpay' ? 'checked' : ''; ?>>
                                <div class="payment-method-content">
                                    <i class="fas fa-credit-card"></i>
                                    <div>
                                        <h4>VNPay</h4>
                                        <p>Thanh toán qua VNPay</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-sticky-note"></i>
                            Ghi chú đơn hàng
                        </h2>

                        <div class="form-group">
                            <label for="notes">Ghi chú (tùy chọn)</label>
                            <textarea id="notes" name="notes" class="form-textarea" rows="3"
                                placeholder="Ghi chú thêm về đơn hàng..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="shopping-cart.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại giỏ hàng
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i>
                            Đặt hàng
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-header">
                    <h3>Tóm tắt đơn hàng</h3>
                </div>

                <div class="summary-items">
                    <?php foreach ($cart_items as $item):
                        $product = $item['product'];
                        $quantity = $item['quantity'];
                        $subtotal = $item['subtotal'];
                        $image_url = $product['image_url'] ?: 'assets/img/product/product-1.jpg';
                    ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p>Số lượng: <?php echo $quantity; ?></p>
                                <span class="item-price"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="total-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="total-row">
                        <span>Phí vận chuyển:</span>
                        <span class="free-shipping">Miễn phí</span>
                    </div>
                    <div class="total-row total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                    </div>
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
    </div>
</main>

<?php
// Page-specific JavaScript
$page_scripts = '
    // Form validation
    document.querySelector(".checkout-form-content").addEventListener("submit", function(e) {
        const requiredFields = this.querySelectorAll("[required]");
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add("error");
                isValid = false;
            } else {
                field.classList.remove("error");
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
    });
    
    // Payment method selection
    document.querySelectorAll("input[name=\"payment_method\"]").forEach(radio => {
        radio.addEventListener("change", function() {
            document.querySelectorAll(".payment-method").forEach(method => {
                method.classList.remove("selected");
            });
            this.closest(".payment-method").classList.add("selected");
        });
    });
    
    // Initialize selected payment method
    const selectedPayment = document.querySelector("input[name=\"payment_method\"]:checked");
    if (selectedPayment) {
        selectedPayment.closest(".payment-method").classList.add("selected");
    }
';

// Include footer
include 'includes/layouts/footer.php';
?>