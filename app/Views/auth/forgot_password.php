<div class="container mx-auto px-4 py-8">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Quên mật khẩu</h1>
    
    <p class="text-gray-600 mb-6 text-center">Nhập email của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu.</p>
    
    <form method="POST" action="/auth/forgot-password" id="forgotPasswordForm">
      <div class="mb-6">
        <label class="block text-gray-700 mb-2 font-medium">Email *</label>
        <input type="email" name="email" required
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="your@email.com">
      </div>
      
      <button type="submit" 
              class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Gửi link đặt lại mật khẩu
      </button>
    </form>
    
    <div class="text-center mt-6">
      <a href="/auth/login" class="text-blue-600 hover:underline">Quay lại đăng nhập</a>
    </div>
  </div>
</div>

<script>
document.getElementById('forgotPasswordForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('/auth/forgot-password', {
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
      this.reset();
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
