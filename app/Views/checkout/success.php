<div class="container mx-auto px-4 py-8 text-center">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
    <div class="text-green-600 mb-4">
      <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
    </div>
    
    <h1 class="text-3xl font-bold mb-4">Đặt hàng thành công!</h1>
    <p class="text-gray-600 mb-8">Cảm ơn bạn đã mua hàng tại STYLEX</p>
    
    <div class="text-left mb-6">
      <p class="mb-2"><strong>Mã đơn hàng:</strong> <?php echo e($order_summary['order_code']); ?></p>
      <p><strong>Địa chỉ giao hàng:</strong> <?php echo e($order_summary['shipping_summary']); ?></p>
    </div>
    
    <a href="/" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700">
      Về trang chủ
    </a>
  </div>
</div>
