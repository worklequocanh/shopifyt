<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
  <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-3xl font-bold text-center mb-8">Đăng nhập</h2>
    
    <form method="POST" action="/auth/login">
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      
      <div class="mb-6">
        <label class="block text-gray-700 mb-2">Mật khẩu</label>
        <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Đăng nhập
      </button>
    </form>
    
    <p class="text-center mt-6 text-gray-600">
      Chưa có tài khoản? <a href="/auth/register" class="text-blue-600 hover:underline">Đăng ký ngay</a>
    </p>
  </div>
</div>
