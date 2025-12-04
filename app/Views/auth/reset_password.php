<div class="container mx-auto px-4 py-8">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Đặt lại mật khẩu</h1>
    
    <p class="text-gray-600 mb-6 text-center">Nhập mật khẩu mới cho tài khoản của bạn.</p>
    
    <form method="POST" action="/auth/reset-password" id="resetPasswordForm">
      <input type="hidden" name="token" value="<?php echo e($token ?? ''); ?>">
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Mật khẩu mới *</label>
        <input type="password" name="password" required minlength="6"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Tối thiểu 6 ký tự">
      </div>
      
      <div class="mb-6">
        <label class="block text-gray-700 mb-2 font-medium">Xác nhận mật khẩu *</label>
        <input type="password" name="confirm_password" required minlength="6"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Nhập lại mật khẩu mới">
      </div>
      
      <button type="submit" 
              class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Đặt lại mật khẩu
      </button>
    </form>
    
    <div class="text-center mt-6">
      <a href="/auth/login" class="text-blue-600 hover:underline">Quay lại đăng nhập</a>
    </div>
  </div>
</div>

<script>
document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  if (formData.get('password') !== formData.get('confirm_password')) {
    if (window.Notification) {
      window.Notification.error('Mật khẩu xác nhận không khớp');
    } else {
      alert('Mật khẩu xác nhận không khớp');
    }
    return;
  }
  
  fetch('/auth/reset-password', {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      if (window.Notification) {
        window.Notification.success(data.message);
      } else {
        alert(data.message);
      }
      setTimeout(() => window.location.href = '/auth/login', 1500);
    } else {
      if (window.Notification) {
        window.Notification.error(data.message);
      } else {
        alert(data.message);
      }
    }
  })
  .catch(() => {
    if (window.Notification) {
      window.Notification.error('Có lỗi xảy ra');
    } else {
      alert('Có lỗi xảy ra');
    }
  });
});
</script>
