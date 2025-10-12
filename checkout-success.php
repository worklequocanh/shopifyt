<?php
require_once 'includes/config.php';
session_start();

// Thiết lập page title và breadcrumbs
$page_title = 'Đặt hàng thành công';
$breadcrumbs = [
    ['text' => 'Trang chủ', 'url' => 'index.php', 'icon' => 'fas fa-home'],
    ['text' => 'Đặt hàng thành công']
];

// Kiểm tra thông tin đơn hàng
if (!isset($_SESSION['order_info'])) {
    header('Location: index.php');
    exit;
}

$order_info = $_SESSION['order_info'];

// Include header
include 'includes/layouts/header.php';
?>

<!-- Main Content -->
<main class="section">
    <div class="container">
        <div style="max-width: 1000px; margin: 0 auto;">
            <!-- Success Message -->
            <div class="text-center mb-12">
                <div style="background-color: #d1fae5; width: 6rem; height: 6rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-check" style="font-size: 2.5rem; color: var(--success-color);"></i>
                </div>
                <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;">Đặt hàng thành công!</h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; color: var(--gray-600);">
                    Cảm ơn bạn đã mua sắm tại ShopifyT. Đơn hàng của bạn đã được xử lý.
                </p>
            </div>

            <!-- Order Information -->
            <div class="card mb-8">
                <div class="card-body">
                    <h2 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                        <i class="fas fa-receipt text-primary"></i>
                        Thông tin đơn hàng
                    </h2>

                    <div class="grid grid-2" style="gap: 2rem;">
                        <!-- Order Details -->
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Chi tiết đơn hàng</h3>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="d-flex justify-between">
                                    <span style="color: var(--gray-600);">Mã đơn hàng:</span>
                                    <span style="font-weight: 600; color: var(--primary-color);">#<?php echo str_pad($order_info['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="d-flex justify-between">
                                    <span style="color: var(--gray-600);">Ngày đặt:</span>
                                    <span style="font-weight: 600;"><?php echo date('d/m/Y H:i'); ?></span>
                                </div>
                                <div class="d-flex justify-between">
                                    <span style="color: var(--gray-600);">Trạng thái:</span>
                                    <span style="background-color: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: var(--border-radius); font-size: 0.875rem; font-weight: 600;">Đang xử lý</span>
                                </div>
                                <div class="d-flex justify-between">
                                    <span style="color: var(--gray-600);">Phương thức thanh toán:</span>
                                    <span style="font-weight: 600;">
                                        <?php
                                        $payment_methods = [
                                            'cod' => 'Thanh toán khi nhận hàng',
                                            'bank_transfer' => 'Chuyển khoản ngân hàng',
                                            'momo' => 'Ví MoMo',
                                            'vnpay' => 'VNPay'
                                        ];
                                        echo $payment_methods[$order_info['payment_method']] ?? 'Không xác định';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Thông tin khách hàng</h3>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div>
                                    <span style="color: var(--gray-600);">Họ và tên:</span>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order_info['customer_name']); ?></div>
                                </div>
                                <div>
                                    <span style="color: var(--gray-600);">Email:</span>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order_info['customer_email']); ?></div>
                                </div>
                                <div>
                                    <span style="color: var(--gray-600);">Số điện thoại:</span>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order_info['customer_phone']); ?></div>
                                </div>
                                <div>
                                    <span style="color: var(--gray-600);">Địa chỉ giao hàng:</span>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order_info['customer_address']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Tóm tắt đơn hàng</h3>
                        <div style="background-color: var(--gray-100); border-radius: var(--border-radius-lg); padding: 1.5rem;">
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="d-flex justify-between">
                                    <span style="color: var(--gray-600);">Tạm tính:</span>
                                    <span style="font-weight: 600;"><?php echo number_format($order_info['total_amount'], 0, ',', '.'); ?>đ</span>
                                </div>
                                <div class="d-flex justify-between">
                                    <span style="color: var(--gray-600);">Phí vận chuyển:</span>
                                    <span style="font-weight: 600; color: <?php echo $order_info['shipping_fee'] == 0 ? 'var(--success-color)' : ''; ?>">
                                        <?php echo $order_info['shipping_fee'] == 0 ? 'Miễn phí' : number_format($order_info['shipping_fee'], 0, ',', '.') . 'đ'; ?>
                                    </span>
                                </div>
                                <hr style="border-color: var(--gray-300);">
                                <div class="d-flex justify-between" style="font-size: 1.25rem; font-weight: 700;">
                                    <span>Tổng cộng:</span>
                                    <span style="color: var(--danger-color);"><?php echo number_format($order_info['final_total'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($order_info['notes'])): ?>
                        <div style="margin-top: 1.5rem;">
                            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Ghi chú:</h3>
                            <p style="color: var(--gray-600); background-color: var(--gray-100); padding: 1rem; border-radius: var(--border-radius-lg);"><?php echo htmlspecialchars($order_info['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Next Steps -->
            <div style="background-color: #dbeafe; border-radius: var(--border-radius-lg); padding: 2rem; margin-bottom: 2rem;">
                <h2 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; color: #1e40af;">
                    <i class="fas fa-info-circle"></i>
                    Bước tiếp theo
                </h2>

                <div class="grid grid-3" style="gap: 1.5rem;">
                    <div class="text-center">
                        <div style="background-color: #bfdbfe; width: 4rem; height: 4rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-envelope" style="font-size: 1.5rem; color: #1e40af;"></i>
                        </div>
                        <h3 style="font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">Email xác nhận</h3>
                        <p style="color: #1e40af; font-size: 0.875rem;">Chúng tôi đã gửi email xác nhận đơn hàng đến địa chỉ của bạn</p>
                    </div>

                    <div class="text-center">
                        <div style="background-color: #bfdbfe; width: 4rem; height: 4rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-truck" style="font-size: 1.5rem; color: #1e40af;"></i>
                        </div>
                        <h3 style="font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">Giao hàng</h3>
                        <p style="color: #1e40af; font-size: 0.875rem;">Đơn hàng sẽ được giao trong 1-3 ngày làm việc</p>
                    </div>

                    <div class="text-center">
                        <div style="background-color: #bfdbfe; width: 4rem; height: 4rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-phone" style="font-size: 1.5rem; color: #1e40af;"></i>
                        </div>
                        <h3 style="font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">Liên hệ</h3>
                        <p style="color: #1e40af; font-size: 0.875rem;">Hotline: 1900-xxxx để được hỗ trợ</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center" style="display: flex; gap: 1rem; justify-content: center;">
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i>
                    Tiếp tục mua sắm
                </a>
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-home"></i>
                    Về trang chủ
                </a>
            </div>
        </div>
    </div>
</main>

<?php
// Page-specific JavaScript
$page_scripts = '
    // Clear order info after 5 minutes
    setTimeout(() => {
        // This would clear the session in a real application
        console.log("Order info will be cleared after 5 minutes");
    }, 300000);

    // Success animation
    document.addEventListener("DOMContentLoaded", function() {
        const successIcon = document.querySelector(".fa-check");
        successIcon.style.transform = "scale(0)";
        successIcon.style.transition = "transform 0.5s ease-out";
        
        setTimeout(() => {
            successIcon.style.transform = "scale(1)";
        }, 200);
    });
';

// Include footer
include 'includes/layouts/footer.php';
?>