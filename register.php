<?php
require_once __DIR__ . '/includes/functions/auth_functions.php';

redirectIfLoggedIn();

$page_title = 'Đăng ký';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Lấy dữ liệu từ form
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm-password'] ?? '';
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');

  // Validate dữ liệu
  if (empty($name)) {
    $error_message = "Tên là bắt buộc";
  }
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Email không hợp lệ";
  }
  if (empty($password)) {
    $error_message = "Mật khẩu là bắt buộc";
  } elseif (strlen($password) < 6) {
    $error_message = "Mật khẩu phải dài ít nhất 6 ký tự";
  }
  if ($password !== $confirm_password) {
    $error_message = "Mật khẩu xác nhận không khớp";
  }
  if (!empty($phone) && !preg_match("/^[0-9]{10,11}$/", $phone)) {
    $error_message = "Số điện thoại không hợp lệ";
  }

  if (!isset($error_message)) {
    $result = registerUser($pdo, [
      'name' => $name,
      'email' => $email,
      'password' => $password,
      'phone' => $phone,
      'address' => $address
    ]);

    if ($result['success']) {
      $success_message = $result['message'];
    } else {
      $error_message = $result['message'];
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-100">
  <div class="flex items-center justify-center min-h-screen py-12 px-4">
    <div class="bg-white p-8 md:p-12 rounded-xl shadow-2xl w-full max-w-md">
      <div class="text-center mb-8">
        <a href="index.php" class="text-3xl font-bold text-gray-900">STYLEX</a>
        <h2 class="mt-4 text-2xl font-bold text-gray-800">
          Tạo tài khoản mới
        </h2>
        <p class="text-gray-500">Bắt đầu hành trình mua sắm của bạn.</p>
      </div>

      <?php include __DIR__ . '/includes/layouts/messages.php'; ?>

      <form class="space-y-6" method="POST">
        <div>
          <label for="name" class="font-medium text-sm text-gray-700">Họ và tên</label>
          <input type="text" id="name" required name="name"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <div>
          <label for="email" class="font-medium text-sm text-gray-700">Email</label>
          <input type="email" id="email" required name="email"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <div>
          <label for="password" class="font-medium text-sm text-gray-700">Mật khẩu</label>
          <input type="password" id="password" required name="password"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <div>
          <label for="confirm-password" class="font-medium text-sm text-gray-700">Xác nhận mật khẩu</label>
          <input type="password" id="confirm-password" required name="confirm-password"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <div>
          <label for="phone" class="font-medium text-sm text-gray-700">Số điện thoại
            <span class="text-gray-400">(Tùy chọn)</span></label>
          <input type="tel" id="phone" name="phone"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <div>
          <label for="address" class="font-medium text-sm text-gray-700">Địa chỉ <span class="text-gray-400">(Tùy
              chọn)</span></label>
          <input type="text" id="address" name="address"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <button class="w-full bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition-colors">
          Đăng ký
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-8">
        Đã có tài khoản?
        <a href="/login.php" class="font-medium text-blue-600 hover:underline">Đăng nhập</a>
      </p>
    </div>
  </div>
</body>

</html>