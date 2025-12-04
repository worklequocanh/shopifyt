<h2>Đặt lại mật khẩu</h2>

<p>Xin chào <strong><?php echo htmlspecialchars($name); ?></strong>,</p>

<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>

<p>Nếu đây là yêu cầu của bạn, vui lòng nhấn vào nút bên dưới để tạo mật khẩu mới:</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo $resetUrl; ?>" class="btn">Đặt lại mật khẩu</a>
</p>

<p>Hoặc copy link sau vào trình duyệt:</p>
<p style="background: #f4f4f4; padding: 10px; border-radius: 4px; word-break: break-all;">
    <?php echo $resetUrl; ?>
</p>

<p><strong>Lưu ý quan trọng:</strong></p>
<ul>
    <li>Link đặt lại mật khẩu chỉ có hiệu lực trong <strong>1 giờ</strong></li>
    <li>Link chỉ có thể sử dụng 1 lần duy nhất</li>
</ul>

<p>Nếu bạn <strong>không yêu cầu</strong> đặt lại mật khẩu, vui lòng bỏ qua email này. Tài khoản của bạn vẫn an toàn.</p>

<p>Trân trọng,<br>
<strong><?php echo $appName; ?></strong></p>
