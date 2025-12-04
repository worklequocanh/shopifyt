<?php
$orderNumber = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$orderDate = date('d/m/Y H:i', strtotime($order['order_date']));
$subtotal = $order['total_amount'] + ($order['discount_amount'] ?? 0);
?>

<h2>Đơn hàng đã được đặt thành công!</h2>

<p>Xin chào <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>,</p>

<p>Cảm ơn bạn đã đặt hàng tại <strong><?php echo $appName; ?></strong>. Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>

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
            <td style="padding: 8px 0; text-align: right;"><span style="background: #ffc107; color: #000; padding: 4px 12px; border-radius: 4px; font-size: 12px;">Đang xử lý</span></td>
        </tr>
    </table>
</div>

<div style="background: #fff; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Địa chỉ giao hàng</h3>
    <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
    <p style="margin: 5px 0;"><?php echo htmlspecialchars($order['shipping_phone']); ?></p>
    <p style="margin: 5px 0;"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
</div>

<?php if (!empty($order['items'])): ?>
<div style="margin: 20px 0;">
    <h3>Sản phẩm đã đặt</h3>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Sản phẩm</th>
                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">SL</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Giá</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;"><?php echo $item['quantity']; ?></td>
                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?>đ</td>
                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;"><?php echo number_format($item['quantity'] * $item['unit_price'], 0, ',', '.'); ?>đ</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="padding: 12px; text-align: right; border-top: 2px solid #dee2e6;">Tạm tính:</td>
                <td style="padding: 12px; text-align: right; border-top: 2px solid #dee2e6;"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
            </tr>
            <?php if (!empty($order['voucher_id']) && $order['discount_amount'] > 0): ?>
            <tr style="background: #d4edda;">
                <td colspan="3" style="padding: 12px; text-align: right;">
                    <strong>🎟️ Voucher (<?php echo htmlspecialchars($order['voucher_code']); ?>):</strong>
                </td>
                <td style="padding: 12px; text-align: right; color: #155724;"><strong>-<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>đ</strong></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="3" style="padding: 12px; text-align: right;">Phí vận chuyển:</td>
                <td style="padding: 12px; text-align: right; color: #28a745;">Miễn phí</td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td colspan="3" style="padding: 15px; text-align: right; font-size: 18px;"><strong>Tổng cộng:</strong></td>
                <td style="padding: 15px; text-align: right; font-size: 18px; color: #dc3545;"><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo $appUrl; ?>/order/detail/<?php echo $order['id']; ?>" class="btn">Xem chi tiết đơn hàng</a>
</p>

<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <p style="margin: 0;"><strong>📦 Lưu ý:</strong> Đơn hàng sẽ được giao trong vòng 3-5 ngày làm việc. Chúng tôi sẽ liên hệ với bạn sớm nhất!</p>
</div>

<p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.</p>

<p>Trân trọng,<br>
<strong><?php echo $appName; ?></strong></p>
