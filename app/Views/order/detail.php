<div class="container mx-auto px-4 py-8">
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <a href="/account/orders" class="text-blue-600 hover:underline mb-4 inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Quay lại lịch sử đơn hàng
      </a>
      <h1 class="text-3xl font-bold mt-4">Chi Tiết Đơn Hàng #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Order Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Status Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">Trạng Thái Đơn Hàng</h2>
            <span class="px-4 py-2 rounded-full text-sm font-semibold
              <?php 
                echo $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                     ($order['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
              ?>">
              <?php 
                echo $order['status'] === 'pending' ? '⏱️ Đang xử lý' : 
                     ($order['status'] === 'accepted' ? '✅ Đã giao' : '❌ Đã hủy');
              ?>
            </span>
          </div>

          <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b">
              <span class="text-gray-600">Ngày đặt hàng:</span>
              <span class="font-semibold"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="flex justify-between py-2 border-b">
              <span class="text-gray-600">Phương thức thanh toán:</span>
              <span class="font-semibold">COD - Thanh toán khi nhận hàng</span>
            </div>
            <div class="flex justify-between py-2">
              <span class="text-gray-600">Mã đơn hàng:</span>
              <span class="font-semibold font-mono">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
          </div>
        </div>

        <!-- Shipping Info -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold mb-4">Thông Tin Giao Hàng</h2>
          <div class="space-y-2 text-sm">
            <div class="flex gap-2">
              <span class="text-gray-600 w-32">Người nhận:</span>
              <span class="font-semibold"><?php echo e($order['customer_name']); ?></span>
            </div>
            <div class="flex gap-2">
              <span class="text-gray-600 w-32">Số điện thoại:</span>
              <span class="font-semibold"><?php echo e($order['shipping_phone']); ?></span>
            </div>
            <div class="flex gap-2">
              <span class="text-gray-600 w-32">Địa chỉ:</span>
              <span class="font-semibold"><?php echo e($order['shipping_address']); ?></span>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold mb-4">Sản Phẩm Đã Đặt</h2>
          <div class="space-y-4">
            <?php if (!empty($order['items'])): ?>
              <?php foreach ($order['items'] as $item): ?>
                <div class="flex gap-4 pb-4 border-b last:border-0">
                  <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                    <?php if (!empty($item['main_image'])): ?>
                      <img src="<?php echo e($item['main_image']); ?>" alt="<?php echo e($item['product_name']); ?>" 
                           class="w-full h-full object-cover">
                    <?php else: ?>
                      <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                      </div>
                    <?php endif; ?>
                  </div>
                  
                  <div class="flex-1">
                    <h3 class="font-semibold mb-1"><?php echo e($item['product_name']); ?></h3>
                    <p class="text-sm text-gray-600">Số lượng: <?php echo e($item['quantity']); ?></p>
                    <p class="text-sm text-gray-600">Đơn giá: <?php echo format_currency($item['unit_price']); ?></p>
                  </div>
                  
                  <div class="text-right">
                    <p class="font-bold text-lg"><?php echo format_currency($item['unit_price'] * $item['quantity']); ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-gray-500 text-center py-4">Không có sản phẩm nào</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Order Summary Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
          <h2 class="text-xl font-bold mb-4">Tổng Quan Đơn Hàng</h2>
          
          <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Tạm tính:</span>
              <span><?php echo format_currency($order['total_amount'] + ($order['discount_amount'] ?? 0)); ?></span>
            </div>
            
            <?php if (!empty($order['voucher_id'])): ?>
              <div class="bg-green-50 border border-green-200 rounded-lg p-3 -mx-1">
                <div class="flex items-center justify-between mb-1">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-green-800 uppercase"><?php echo htmlspecialchars($order['voucher_code']); ?></span>
                  </div>
                  <span class="text-sm font-bold text-green-700">-<?php echo format_currency($order['discount_amount']); ?></span>
                </div>
                <p class="text-xs text-green-700"><?php echo htmlspecialchars($order['voucher_name']); ?></p>
              </div>
            <?php endif; ?>
            
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Phí vận chuyển:</span>
              <span class="text-green-600">Miễn phí</span>
            </div>
            <div class="border-t pt-3 flex justify-between font-bold text-lg">
              <span>Tổng cộng:</span>
              <span class="text-blue-600"><?php echo format_currency($order['total_amount']); ?></span>
            </div>
          </div>

          <?php if ($order['status'] === 'pending'): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
              <p class="text-sm text-yellow-800">
                <strong>Lưu ý:</strong> Đơn hàng đang được xử lý. Chúng tôi sẽ liên hệ với bạn sớm!
              </p>
            </div>
          <?php elseif ($order['status'] === 'accepted'): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
              <p class="text-sm text-green-800">
                <strong>Thành công!</strong> Đơn hàng đã được giao thành công.
              </p>
            </div>
          <?php elseif ($order['status'] === 'cancelled'): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
              <p class="text-sm text-red-800">
                <strong>Đã hủy:</strong> Đơn hàng này đã bị hủy.
              </p>
            </div>
          <?php endif; ?>

          <div class="space-y-2">
            <a href="/account/orders" 
               class="block w-full text-center px-4 py-3 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-semibold">
              Lịch sử đơn hàng
            </a>
            <a href="/product" 
               class="block w-full text-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
              Tiếp tục mua sắm
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
