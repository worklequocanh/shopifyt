<?php
require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/product_functions.php';


$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$product = getProductById($pdo, $product_id);
if (!$product) {
  header('Location: /');
  exit();
}

$images = get_product_images($pdo, $product_id);

$page_title = 'Chi tiết sản phẩm';
$breadcrumbs = [
  ['title' => 'Trang chủ', 'url' => 'index.php'],
  ['title' => 'Sản phẩm', 'url' => 'products.php'],
  ['title' => $product ? $product['name'] : 'Chi tiết sản phẩm', 'url' => '']
];

$productDetails = getProductDetails($pdo, $product_id);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-50 text-gray-800">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>

  <?php include __DIR__ . '/includes/layouts/breadcrumbs.php'; ?>

  <?php displayFlashMessage(); ?>

  <main>
    <div class="container mx-auto px-4 lg:px-6 py-12 lg:py-16">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">

        <div x-data="{ 
                    images: <?php echo htmlspecialchars(json_encode($images)); ?>,
                    selectedIndex: 0
                }">
          <div class="mb-4">
            <img :src="images[selectedIndex]" alt="<?php echo htmlspecialchars($product['name']); ?>"
              class="w-full h-auto object-cover rounded-lg shadow-lg aspect-[3/4]">
          </div>
          <div class="grid grid-cols-5 gap-3">
            <template x-for="(image, index) in images" :key="index">
              <div @click="selectedIndex = index"
                :class="{ 'border-blue-500 ring-2 ring-blue-300': selectedIndex === index }"
                class="cursor-pointer rounded-md border-2 border-transparent hover:border-blue-400 transition-all duration-200">
                <img :src="image" alt="Thumbnail" class="w-full h-full object-cover rounded aspect-[3/4]">
              </div>
            </template>
          </div>
        </div>

        <div>
          <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">
            <?php echo htmlspecialchars($product['name']); ?></h1>

          <div class="flex items-center mb-8">
            <span class="text-3xl font-bold text-blue-600"><?php echo format_currency($product['price']); ?></span>
          </div>

          <form id="addToCartForm">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <div class="mb-8">
              <label for="quantity" class="font-semibold mb-2 block text-lg">Số lượng</label>
              <input type="number" id="quantity" name="quantity" value="1" min="1"
                max="<?php echo $product['stock']; ?>" class="w-24 p-2 border border-gray-300 rounded-md text-center">
              <span class="ml-4 text-sm text-gray-500">(Còn <?php echo $product['stock']; ?> sản phẩm)</span>
            </div>

            <button type="submit"
              class="w-full bg-gray-900 text-white font-bold py-4 px-8 rounded-lg hover:bg-gray-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
              <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
              <?php echo ($product['stock'] > 0) ? 'Thêm vào giỏ hàng' : 'Hết hàng'; ?>
            </button>
          </form>

          <div class="mt-12 border-t pt-8">
            <h3 class="font-bold text-xl mb-4 text-gray-900">Mô tả sản phẩm</h3>
            <div class="prose max-w-none text-gray-600 space-y-4">
              <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>

  <script src="/assets/js/cart.js" defer></script>

</html>