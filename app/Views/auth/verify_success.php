<div class="container mx-auto px-4 py-8">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
    <div class="text-green-600 mb-6">
      <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
    
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Email đã được xác nhận!</h1>
    <p class="text-gray-600 mb-8"><?php echo e($message ?? 'Tài khoản của bạn đã được kích hoạt thành công.'); ?></p>
    
    <a href="/auth/login" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
      Đăng nhập ngay
    </a>
  </div>
</div>
