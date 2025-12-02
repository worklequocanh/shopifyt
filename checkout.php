<?php
require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/cart_functions.php';
require_once __DIR__ . '/includes/functions/account_functions.php';
require_once __DIR__ . '/includes/functions/order_functions.php';

restrictToRoles($pdo, 'customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accountId = $_SESSION['id'];

  try {
    // Lấy thông tin phone và address của người dùng từ database
    $stmt = $pdo->prepare("SELECT phone, address FROM accounts WHERE id = ?");
    $stmt->execute([$accountId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra xem thông tin có bị thiếu không (NULL hoặc rỗng)
    if (empty($userInfo['phone']) || empty($userInfo['address'])) {
      // Tạo một "flash message" để hiển thị trên trang thông tin tài khoản
      $warning_message = "Vui lòng cập nhật đầy đủ số điện thoại và địa chỉ để tiếp tục đặt hàng.";
    } else {
      // Gọi hàm xử lý chính
      $orderId = createOrderFromCart($pdo);

      header('Location: /checkout-success.php?orderId=' . $orderId);
      exit();
    }
  } catch (PDOException $e) {
    $error_message = 'Lỗi máy chủ, không thể kiểm tra thông tin người dùng.' . $e->getMessage();
  }
}

$page_title = 'Hoàn tất đơn hàng';

$breadcrumbs = [
  ['title' => 'Trang chủ', 'url' => 'index.php'],
  ['title' => 'Thanh toán', 'url' => 'checkout.php']
];

$cartData = getCartData($pdo);
$carts = $cartData['items'];
$total_amount = $cartData['total_amount'];

$customer = getLoggedInUserInfo($pdo);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-100">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>
  <?php include __DIR__ . '/includes/layouts/breadcrumbs.php'; ?>
  <?php include __DIR__ . '/includes/layouts/messages.php'; ?>

  <main class="container mx-auto px-4 lg:px-6 py-12 lg:py-20">
    <div class="text-center mb-12">
      <a href="index.php" class="text-3xl font-bold text-gray-900">STYLEX</a>
      <h1 class="mt-4 text-3xl lg:text-4xl font-extrabold text-gray-900">
        Hoàn tất đơn hàng
      </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-16">
      <div class="lg:hidden bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-bold mb-4">Tóm tắt đơn hàng</h2>
        <?php if (!empty($carts)): ?>
          <?php foreach ($carts as $item): ?>
            <div class="flex justify-between items-center text-sm mb-2">
              <span class="text-gray-600">
                <?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?>
              </span>
              <span class="font-medium"><?php echo format_currency((int)$item['price'] * (int)$item['quantity']) ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <div class="border-t mt-4 pt-4 space-y-2">
          <div class="flex justify-between text-gray-600">
            <span>Tạm tính</span><span><?php echo format_currency($total_amount); ?></span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Phí vận chuyển</span><span>Miễn phí</span>
          </div>
          <div class="flex justify-between font-bold text-lg">
            <span>Tổng cộng</span><span><?php echo format_currency($total_amount); ?></span>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 lg:p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-8">Thông tin giao hàng</h2>

        <form method="POST">
          <div class="space-y-6">

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-8 text-center">
                <svg class="h-6 w-6 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
              </div>
              <div class="flex-grow">
                <p class="text-sm text-gray-500">Họ và tên</p>
                <p class="font-semibold text-gray-800 text-lg">
                  <?= htmlspecialchars($customer['name'] ?? 'Nguyễn Văn A') ?>
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-8 text-center">
                <svg class="h-6 w-6 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                </svg>
              </div>
              <div class="flex-grow">
                <p class="text-sm text-gray-500">Số điện thoại</p>
                <p class="font-semibold text-gray-800 text-lg">
                  <?= htmlspecialchars($customer['phone'] ?? '0901234567') ?>
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-8 text-center">
                <svg class="h-6 w-6 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
              </div>
              <div class="flex-grow">
                <p class="text-sm text-gray-500">Địa chỉ</p>
                <p class="font-semibold text-gray-800 text-lg">
                  <?= htmlspecialchars($customer['address'] ?? '123 Đường ABC, Phường X, Quận Y, TP. Z') ?>
                </p>
              </div>
            </div>

          </div>

          <div class="pt-8 mt-8 border-t">
            <button type="submit"
              class="w-full bg-gray-900 text-white font-bold py-4 rounded-lg hover:bg-gray-800 transition-colors">
              Xác nhận và Đặt hàng
            </button>
          </div>
        </form>
      </div>

      <div class="hidden lg:block bg-white p-8 rounded-lg shadow-md self-start">
        <h2 class="text-2xl font-bold mb-6">Tóm tắt đơn hàng</h2>
        <?php if (!empty($carts)): ?>
          <?php foreach ($carts as $item): ?>
            <div class="flex justify-between items-center mb-4">
              <div class="flex items-center gap-4">
                <img src="<?php echo htmlspecialchars($item['main_image']); ?>"
                  alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover rounded-md" />
                <div>
                  <p class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></p>
                  <p class="text-sm text-gray-500">Số lượng: <?php echo $item['quantity']; ?></p>
                </div>
              </div>
              <span class="font-medium"><?php echo format_currency((int)$item['price'] * (int)$item['quantity']); ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <div class="border-t mt-6 pt-6 space-y-3">
          <div class="flex justify-between text-gray-600">
            <span>Tạm tính</span><span><?php echo format_currency($total_amount); ?></span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Phí vận chuyển</span><span>Miễn phí</span>
          </div>
          <div class="flex justify-between font-bold text-xl">
            <span>Tổng cộng</span><span><?php echo format_currency($total_amount); ?></span>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>
</body>

</html>