<?php
require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/order_functions.php';

restrictToRoles($pdo, 'customer');
// Thiết lập page title và breadcrumbs
$page_title = 'Đặt hàng thành công';
$breadcrumbs = [
  ['title' => 'Trang chủ', 'url' => 'index.php'],
  ['title' => 'Đặt hàng thành công', 'url' => '']
];

// Kiểm tra thông tin đơn hàng
if (!isset($_GET['orderId'])) {
  header('Location: index.php');
  exit;
}

$orderId = (int)$_GET['orderId'];
$order_info = getOrderSummary($pdo, $orderId);
if (!$order_info) {
  header('Location: index.php');
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>


<body class="bg-gray-100">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>
  <?php include __DIR__ . '/includes/layouts/breadcrumbs.php'; ?>
  <div class="flex flex-col items-center justify-center min-h-screen text-center px-4">
    <div class="bg-white p-8 md:p-12 rounded-xl shadow-2xl w-full max-w-lg">
      <div class="mx-auto mb-6 bg-green-100 h-20 w-20 rounded-full flex items-center justify-center">
        <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h1 class="text-3xl font-extrabold text-gray-900">
        Đặt hàng thành công!
      </h1>
      <p class="mt-4 text-gray-600">
        Cảm ơn bạn đã mua sắm tại STYLEX. Chúng tôi sẽ xử lý đơn hàng của bạn
        trong thời gian sớm nhất.
      </p>
      <div class="mt-8 text-left bg-gray-50 p-6 rounded-lg border">
        <p class="mb-2">
          <span class="font-semibold">Mã đơn hàng:</span> <?php echo htmlspecialchars($order_info['order_code']); ?>
        </p>
        <p>
          <span class="font-semibold">Giao đến:</span>
          <?php echo htmlspecialchars($order_info['shipping_summary']); ?>
        </p>
      </div>
      <a href="/index.php"
        class="inline-block mt-8 w-full bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition-colors">
        Tiếp tục mua sắm
      </a>
    </div>
  </div>
  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>
</body>

</html>