<?php

require_once __DIR__ . '/includes/functions/auth_functions.php';

$page_title = 'Đăng nhập';

redirectIfLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';


  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Email không hợp lệ";
  }

  if (empty($password)) {
    $error_message = "Mật khẩu là bắt buộc";
  }

  $result = loginUser($pdo, $email, $password);
  if (!$result['success']) {
    $error_message = $result['message'];
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-100">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 md:p-12 rounded-xl shadow-2xl w-full max-w-md">
      <div class="text-center mb-8">
        <a href="index.php" class="text-3xl font-bold text-gray-900">STYLEX</a>
        <h2 class="mt-4 text-2xl font-bold text-gray-800">
          Chào mừng trở lại!
        </h2>
        <p class="text-gray-500">Đăng nhập để tiếp tục.</p>
      </div>

      <?php include __DIR__ . '/includes/layouts/messages.php'; ?>

      <form class="space-y-6" method="POST">
        <div>
          <label for="email" class="font-medium text-sm">Email</label>
          <input type="email" id="email" name="email"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="password" class="font-medium text-sm">Mật khẩu</label>
          <input type="password" id="password" name="password"
            class="w-full mt-2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <button class="w-full bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition-colors">
          Đăng nhập
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-8">
        Chưa có tài khoản?
        <a href="/register.php" class="font-medium text-blue-600 hover:underline">Đăng ký ngay</a>
      </p>
    </div>
  </div>
</body>

</html>