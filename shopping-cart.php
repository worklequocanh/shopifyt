<?php

require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/product_functions.php';
require_once __DIR__ . '/includes/functions/cart_functions.php';

restrictToRoles($pdo, 'customer');

$page_title = 'Giỏ hàng của bạn';

$breadcrumbs = [
  ['title' => 'Trang chủ', 'url' => 'index.php'],
  ['title' => 'Giỏ hàng', 'url' => 'shopping-cart.php']
];

$cartData = getCartData($pdo);
$carts = $cartData['items'];
$total_amount = $cartData['total_amount'];

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
  <script>
    console.log(<?php echo json_encode($carts); ?>);
  </script>
</head>

<body class="bg-gray-100">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>
  <?php include __DIR__ . '/includes/layouts/breadcrumbs.php'; ?>

  <?php displayFlashMessage(); ?>

  <main class="container mx-auto px-4 lg:px-6 py-16">
    <h1 class="text-3xl font-extrabold text-center text-gray-900 mb-12">
      Giỏ hàng của bạn
    </h1>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
      <div id="cart-container" class=" lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <?php if (!empty($carts)): ?>
          <?php foreach ($carts as $item): ?>
            <div class="cart-item flex items-center gap-6 border-b pb-6 mb-6" data-product-id="<?= $item['id'] ?>">
              <img src="<?= htmlspecialchars($item['main_image']) ?>" class="w-24 h-24 object-cover rounded-md" />
              <div class="flex-grow">
                <h3 class="font-semibold text-lg"><?= htmlspecialchars($item['name']) ?></h3>
                <p class="text-sm text-gray-500">Đơn giá: <span
                    class="item-price"><?= format_currency((int)$item['price']) ?></span></p>
              </div>

              <div class="flex items-center border rounded-md">
                <button class="btn-decrease p-2 w-10 hover:bg-gray-100">-</button>
                <span class="quantity-display p-2 w-12 text-center" data-quantity="<?= $item['quantity'] ?>">
                  <?= $item['quantity'] ?>
                </span>
                <button class="btn-increase p-2 w-10 hover:bg-gray-100">+</button>
              </div>

              <div class="flex items-center gap-4">
                <p class="font-bold text-lg w-28 text-right item-subtotal">
                  <?= format_currency((int)$item['price'] * (int)$item['quantity']) ?>
                </p>
                <button class="btn-delete text-gray-400 hover:text-red-500 text-2xl">&times;</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-6 border-b pb-4">Tóm tắt đơn hàng</h2>
        <div class="space-y-4 text-gray-600">
          <div class="flex justify-between">
            <span>Tạm tính</span>
            <span id="cart-subtotal" class="font-medium"><?php echo format_currency($total_amount); ?></span>
          </div>
          <div class="flex justify-between">
            <span>Phí vận chuyển</span>
            <span class="font-medium">Miễn phí</span>
          </div>
        </div>
        <div class="flex justify-between font-bold text-xl text-gray-900 border-t pt-4 mt-6">
          <span>Tổng cộng</span>
          <span id="cart-grand-total"><?php echo format_currency($total_amount); ?></span>
        </div>
        <a href="/checkout.php"
          class="block w-full mt-6 bg-gray-900 text-white font-bold py-3 px-6 rounded-lg hover:bg-gray-800 transition-colors text-center">
          Tiến hành thanh toán
        </a>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>
  <script src="/assets/js/cart.js" defer></script>
</body>

</html>