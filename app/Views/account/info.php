<div class="container mx-auto px-4 py-8">
  <div class="max-w-6xl mx-auto">
    <div class="grid lg:grid-cols-4 gap-8">
      <!-- Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-center mb-6">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-3xl font-bold">
              <?php echo strtoupper(substr($account['name'] ?? 'U', 0, 1)); ?>
            </div>
            <h3 class="font-bold text-lg"><?php echo e($account['name'] ?? 'User'); ?></h3>
            <p class="text-sm text-gray-600"><?php echo e($account['email'] ?? ''); ?></p>
          </div>

          <nav class="space-y-2">
            <a href="/account/info" 
               class="<?php echo ($current_section ?? 'profile') === 'profile' ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-50'; ?> flex items-center gap-3 px-4 py-3 rounded-lg transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span>Thông tin cá nhân</span>
            </a>

            <a href="/account/password" 
               class="<?php echo ($current_section ?? '') === 'password' ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-50'; ?> flex items-center gap-3 px-4 py-3 rounded-lg transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              <span>Đổi mật khẩu</span>
            </a>

            <a href="/account/orders" 
               class="<?php echo ($current_section ?? '') === 'orders' ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-50'; ?> flex items-center gap-3 px-4 py-3 rounded-lg transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <span>Lịch sử đơn hàng</span>
            </a>

            <a href="/auth/logout" 
               class="hover:bg-red-50 hover:text-red-600 flex items-center gap-3 px-4 py-3 rounded-lg transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              <span>Đăng xuất</span>
            </a>
          </nav>
        </div>
      </div>

      <!-- Main Content -->
      <div class="lg:col-span-3">
        <?php if (($current_section ?? 'profile') === 'profile'): ?>
          <!-- Profile Section -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-6">Thông Tin Cá Nhân</h2>

            <form id="profileForm">
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Họ tên</label>
                  <input type="text" name="name" value="<?php echo e($account['name']); ?>" required 
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Email</label>
                  <input type="email" value="<?php echo e($account['email']); ?>" disabled 
                         class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50">
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Số điện thoại</label>
                  <input type="tel" name="phone" value="<?php echo e($account['phone'] ?? ''); ?>" required 
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Ngày tạo</label>
                  <input type="text" value="<?php echo date('d/m/Y', strtotime($account['created_at'] ?? 'now')); ?>" disabled 
                         class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50">
                </div>
              </div>

              <div class="mt-6">
                <label class="block text-gray-700 mb-2 font-medium">Địa chỉ</label>
                <textarea name="address" rows="3" required 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($account['address'] ?? ''); ?></textarea>
              </div>

              <button type="submit" 
                      class="mt-6 bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                Cập nhật thông tin
              </button>
            </form>
          </div>

        <?php elseif (($current_section ?? '') === 'password'): ?>
          <!-- Password Section -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-6">Đổi Mật Khẩu</h2>

            <form id="passwordForm" class="max-w-md">
              <div class="space-y-4">
                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Mật khẩu hiện tại</label>
                  <input type="password" name="old_password" required 
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Mật khẩu mới</label>
                  <input type="password" name="new_password" required minlength="6"
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <p class="text-sm text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Xác nhận mật khẩu mới</label>
                  <input type="password" name="confirm_password" required minlength="6"
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>

              <button type="submit" 
                      class="mt-6 bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                Đổi mật khẩu
              </button>
            </form>
          </div>

        <?php elseif (($current_section ?? '') === 'orders'): ?>
          <!-- Orders Section -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-6">Lịch Sử Đơn Hàng</h2>

            <?php if (!empty($orders)): ?>
              <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                  <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                      <div>
                        <p class="font-bold text-lg">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p class="text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                      </div>
                      <span class="px-4 py-2 rounded-full text-sm font-semibold
                        <?php 
                          echo $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($order['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
                        ?>">
                        <?php 
                          echo $order['status'] === 'pending' ? 'Đang xử lý' : 
                               ($order['status'] === 'accepted' ? 'Đã giao' : 'Đã hủy');
                        ?>
                      </span>
                    </div>

                    <div class="text-sm text-gray-600 mb-2">
                      <p><strong>Người nhận:</strong> <?php echo e($order['customer_name']); ?></p>
                      <p><strong>Địa chỉ:</strong> <?php echo e($order['shipping_address']); ?></p>
                      <p><strong>SĐT:</strong> <?php echo e($order['shipping_phone']); ?></p>
                    </div>

                    <div class="flex justify-between items-center mt-4 pt-4 border-t">
                      <p class="font-bold text-lg text-green-600"><?php echo format_currency($order['total_amount']); ?></p>
                      <a href="/order/detail/<?php echo $order['id']; ?>" class="text-blue-600 hover:underline">
                        Xem chi tiết →
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-600 text-lg mb-4">Bạn chưa có đơn hàng nào</p>
                <a href="/product" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                  Mua sắm ngay
                </a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
// Profile Form
document.getElementById('profileForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('/account/update-profile', {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Notification.success(data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      Notification.error(data.message);
    }
  })
  .catch(() => Notification.error('Có lỗi xảy ra'));
});

// Password Form
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  if (formData.get('new_password') !== formData.get('confirm_password')) {
    Notification.error('Mật khẩu mới không khớp');
    return;
  }
  
  fetch('/account/change-password', {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Notification.success(data.message);
      this.reset();
    } else {
      Notification.error(data.message);
    }
  })
  .catch(() => Notification.error('Có lỗi xảy ra'));
});
</script>
