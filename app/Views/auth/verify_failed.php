<div class="container mx-auto px-4 py-8">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
    <div class="text-red-600 mb-6">
      <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
    
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Xác nhận thất bại</h1>
    <p class="text-gray-600 mb-6"><?php echo e($message ?? 'Token không hợp lệ hoặc đã hết hạn.'); ?></p>
    
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
      <p class="text-sm text-yellow-800">Link xác nhận chỉ có hiệu lực trong 24 giờ. Vui lòng yêu cầu gửi lại email xác nhận.</p>
    </div>
    
    <div class="flex flex-col gap-3">
      <a href="/auth/resend-verification" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Gửi lại email xác nhận
      </a>
      <a href="/auth/login" class="inline-block bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
        Về trang đăng nhập
      </a>
    </div>
  </div>
</div>
