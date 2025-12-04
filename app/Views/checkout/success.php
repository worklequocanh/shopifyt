<div class="container mx-auto px-4 py-8 text-center">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
    <div class="text-green-600 mb-4">
      <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
    </div>
    
    <h1 class="text-3xl font-bold mb-4">Đặt hàng thành công!</h1>
    <p class="text-gray-600 mb-4">Cảm ơn bạn đã mua hàng tại STYLEX</p>
    <p class="text-sm text-gray-500 mb-6">📧 Email xác nhận đã được gửi đến hộp thư của bạn</p>
    
    <div class="text-left mb-6 bg-gray-50 p-4 rounded-lg">
      <p class="mb-2"><strong>Mã đơn hàng:</strong> <?php echo e($order_summary['order_code']); ?></p>
      <p><strong>Địa chỉ giao hàng:</strong> <?php echo e($order_summary['shipping_summary']); ?></p>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="/product" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Tiếp tục mua sắm
      </a>
      <a href="/order/detail/<?php echo $order_summary['order_id']; ?>" class="inline-flex items-center justify-center gap-2 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Xem chi tiết đơn hàng
      </a>
    </div>
  </div>
</div>
