<?php
$orderNumber = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$orderDate = date('d/m/Y H:i', strtotime($order['order_date']));

// Determine status info
$statusInfo = [
    'accepted' => [
        'title' => 'Đơn hàng đã được xác nhận!',
        'color' => '#28a745',
        'bgcolor' => '#d4edda',
        'icon' => '✅',
        'message' => 'Đơn hàng của bạn đã được xác nhận và đang được chuẩn bị giao hàng.'
    ],
    'cancelled' => [
        'title' => 'Đơn hàng đã bị hủy',
        'color' => '#dc3545',
        'bgcolor' => '#f8d7da',
        'icon' => '❌',
        'message' => 'Rất tiếc, đơn hàng của bạn đã bị hủy.'
    ]
];

$info = $statusInfo[$newStatus] ?? [];
?>

<h2><?php echo $info['icon']; ?> <?php echo $info['title']; ?></h2>

<p>Xin chào <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>,</p>

<div style="background: <?php echo $info['bgcolor']; ?>; border-left: 4px solid <?php echo $info['color']; ?>; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0; color: <?php echo $info['color']; ?>;"><strong><?php echo $info['message']; ?></strong></p>
</div>

<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Thông tin đơn hàng</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0;"><strong>Mã đơn hàng:</strong></td>
            <td style="padding: 8px 0; text-align: right;">#<?php echo $orderNumber; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px 0;"><strong>Ngày đặt:</strong></td>
            <td style="padding: 8px 0; text-align: right;"><?php echo $orderDate; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px 0;"><strong>Trạng thái:</strong></td>
            <td style="padding: 8px 0; text-align: right;">
                <span style="background: <?php echo $info['color']; ?>; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px;">
                    <?php echo $newStatus === 'accepted' ? 'Đã xác nhận' : 'Đã hủy'; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0;"><strong>Tổng tiền:</strong></td>
            <td style="padding: 8px 0; text-align: right; font-size: 18px; color: <?php echo $info['color']; ?>;"><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</strong></td>
        </tr>
    </table>
</div>

<?php if ($newStatus === 'accepted'): ?>
<div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <p style="margin: 0;"><strong>📦 Giao hàng:</strong> Đơn hàng sẽ được giao trong vòng 3-5 ngày làm việc. Shipper sẽ liên hệ với bạn trước khi giao hàng.</p>
</div>
<?php else: ?>
<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <p style="margin: 0;"><strong>💳 Hoàn tiền:</strong> Nếu bạn đã thanh toán, số tiền sẽ được hoàn lại trong vòng 5-7 ngày làm việc.</p>
</div>
<?php endif; ?>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo $appUrl; ?>/order/detail/<?php echo $order['id']; ?>" class="btn">Xem chi tiết đơn hàng</a>
</p>

<p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi để được hỗ trợ.</p>

<p>Trân trọng,<br>
<strong><?php echo $appName; ?></strong></p>
