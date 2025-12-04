<h2>Xin chào <?php echo htmlspecialchars($name); ?>!</h2>

<p>Cảm ơn bạn đã đăng ký tài khoản tại <strong><?php echo $appName; ?></strong>.</p>

<p>Để hoàn tất đăng ký và kích hoạt tài khoản, vui lòng nhấn vào nút bên dưới:</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo $verifyUrl; ?>" class="btn">Xác nhận tài khoản</a>
</p>

<p>Hoặc copy link sau vào trình duyệt:</p>
<p style="background: #f4f4f4; padding: 10px; border-radius: 4px; word-break: break-all;">
    <?php echo $verifyUrl; ?>
</p>

<p><strong>Lưu ý:</strong> Link xác nhận sẽ hết hạn sau 24 giờ</p>

<p>Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email này.</p>

<p>Trân trọng,<br>
<strong><?php echo $appName; ?></strong></p>
