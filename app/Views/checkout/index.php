<div class="container mx-auto px-4 py-8">
  <div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold">Thanh Toán</h1>
      
      <div class="flex gap-3">
        <a href="/cart" class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Quay lại giỏ hàng
        </a>
        <a href="/product" class="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          Tiếp tục mua sắm
        </a>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Left Column - Shipping Info -->
      <div class="lg:col-span-2">
        <form method="POST" id="checkoutForm">
          <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-6">Thông Tin Giao Hàng</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Họ tên *</label>
                <input type="text" name="name" value="<?php echo e($account['name'] ?? ''); ?>" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Số điện thoại *</label>
                  <input type="tel" name="phone" value="<?php echo e($account['phone'] ?? ''); ?>" required
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Email</label>
                  <input type="email" value="<?php echo e($account['email'] ?? ''); ?>" disabled
                         class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50">
                </div>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 font-medium">Địa chỉ giao hàng *</label>
                <textarea name="address" rows="3" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($account['address'] ?? ''); ?></textarea>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 font-medium">Ghi chú đơn hàng (tùy chọn)</label>
                <textarea name="note" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-6">Phương Thức Thanh Toán</h2>
            
            <div class="space-y-3">
              <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                <input type="radio" name="payment_method" value="cod" checked class="mt-1 mr-3">
                <div class="flex-1">
                  <div class="font-semibold mb-1">Thanh toán khi nhận hàng (COD)</div>
                  <div class="text-sm text-gray-600">Thanh toán bằng tiền mặt khi nhận hàng</div>
                </div>
              </label>

              <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition opacity-50">
                <input type="radio" name="payment_method" value="bank" disabled class="mt-1 mr-3">
                <div class="flex-1">
                  <div class="font-semibold mb-1">Chuyển khoản ngân hàng</div>
                  <div class="text-sm text-gray-600">Sắp ra mắt</div>
                </div>
              </label>
            </div>
          </div>

          <button type="submit" 
                  class="w-full mt-6 bg-green-600 text-white text-lg font-bold py-4 rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Đặt Hàng
          </button>
        </form>
      </div>

      <!-- Right Column - Order Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
          <h2 class="text-xl font-bold mb-6">Đơn Hàng Của Bạn</h2>

          <div class="space-y-4 mb-6">
            <?php if (!empty($cart_items)): ?>
              <?php foreach ($cart_items as $item): ?>
                <div class="flex gap-3">
                  <img src="<?php echo e($item['main_image']); ?>" alt="<?php echo e($item['name']); ?>" 
                       class="w-16 h-16 object-cover rounded">
                  <div class="flex-1">
                    <p class="font-medium text-sm"><?php echo e($item['name']); ?></p>
                    <p class="text-sm text-gray-600">Số lượng: <?php echo e($item['quantity']); ?></p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold"><?php echo format_currency($item['subtotal']); ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-center text-gray-500">Giỏ hàng trống</p>
            <?php endif; ?>
          </div>

          <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between text-gray-600">
              <span>Tạm tính</span>
              <span><?php echo format_currency($total_amount ?? 0); ?></span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Phí vận chuyển</span>
              <span>Miễn phí</span>
            </div>
            <div class="border-t pt-2 flex justify-between text-lg font-bold">
              <span>Tổng cộng</span>
              <span class="text-green-600"><?php echo format_currency($total_amount ?? 0); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
  const phone = this.querySelector('[name="phone"]').value;
  const address = this.querySelector('[name="address"]').value;

  if (!phone || !address) {
    e.preventDefault();
    window.toast.error('Vui lòng điền đầy đủ thông tin giao hàng');
  }
});
</script>
