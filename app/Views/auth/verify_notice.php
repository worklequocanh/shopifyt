<div class="container mx-auto px-4 py-8">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
    <div class="text-yellow-600 mb-6">
      <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
      </svg>
    </div>
    
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Email chưa được xác nhận</h1>
    
    <?php if (!empty($email)): ?>
      <p class="text-gray-600 mb-6">
        Chúng tôi đã gửi email xác nhận đến<br>
        <strong class="text-blue-600"><?php echo htmlspecialchars($email); ?></strong>
      </p>
    <?php else: ?>
      <p class="text-gray-600 mb-6">
        Vui lòng xác nhận email để có thể đăng nhập.
      </p>
    <?php endif; ?>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
      <h3 class="font-semibold text-blue-900 mb-2">📧 Hướng dẫn:</h3>
      <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
        <li>Kiểm tra hộp thư đến (Inbox)</li>
        <li>Nếu không thấy, check thư mục Spam</li>
        <li>Mở email và click vào link xác nhận</li>
        <li>Quay lại trang này và đăng nhập</li>
      </ol>
    </div>
    
    <div class="space-y-3">
      <form method="POST" action="/auth/resend-verification" class="mb-3">
        <button type="submit" 
                class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
          📨 Gửi lại email xác nhận
        </button>
      </form>
      
      <a href="/auth/login" 
         class="inline-block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
        Về trang đăng nhập
      </a>
    </div>
    
    <div class="mt-6 pt-6 border-t text-sm text-gray-500">
      <p>Không nhận được email?</p>
      <p>Kiểm tra địa chỉ email hoặc thử gửi lại sau vài phút.</p>
    </div>
  </div>
</div>
