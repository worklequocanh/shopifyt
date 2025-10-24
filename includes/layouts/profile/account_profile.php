<div class="bg-white p-8 rounded-lg shadow-md">
  <h2 class="text-2xl font-bold mb-6 text-gray-900">Thông tin cá nhân</h2>
  <form class="space-y-6" id="accountProfile">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label for="name" class="font-medium text-sm text-gray-700">Họ và tên</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>"
          class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label for="email" class="font-medium text-sm text-gray-700">Email</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" readonly
          class="w-full mt-2 px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
      </div>
    </div>
    <div>
      <label for="phone" class="font-medium text-sm text-gray-700">Số điện thoại</label>
      <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
        class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
      <label for="address" class="font-medium text-sm text-gray-700">Địa chỉ</label>
      <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>"
        class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div class="pt-4 border-t">
      <button type="submit"
        class="bg-gray-900 text-white font-bold py-3 px-8 rounded-lg hover:bg-gray-800 transition-colors">
        Lưu thay đổi
      </button>
    </div>
  </form>
</div>