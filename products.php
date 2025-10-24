<?php

require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/product_functions.php';
require_once __DIR__ . '/includes/functions/category_functions.php';


$products_per_page = 12;

$total_products = countAllProducts($pdo);
$total_pages = ceil($total_products / $products_per_page);

$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

if ($current_page < 1) {
  $current_page = 1;
}
if ($current_page > $total_pages && $total_pages > 0) {
  $current_page = $total_pages;
}

$offset = ($current_page - 1) * $products_per_page;
$categoryId = $_GET['categoryId'] ?? 'all';

$products = getAllProducts($pdo, $products_per_page, $offset);
$categories = getAllCategory($pdo);

$page_title = 'Sản phẩm';


if ($categoryId !== 'all') {
  $breadcrumbs = [
    ['title' => 'Trang chủ', 'url' => 'index.php'],
    ['title' => 'Sản phẩm', 'url' => 'products.php'],
    ['title' => $categoryId, 'url' => '']
  ];

  $products = getProductByCategory($pdo, $categoryId, $products_per_page, $offset);
} else {
  $breadcrumbs = [
    ['title' => 'Trang chủ', 'url' => 'index.php'],
    ['title' => 'Sản phẩm', 'url' => 'products.php']
  ];
  $products = getAllProducts($pdo, $products_per_page, $offset);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
  <script>
  console.log(<?php echo json_encode($categories); ?>);
  console.log('<?php echo $categoryId; ?>');
  </script>
</head>

<body class="bg-gray-50 text-gray-800">

  <?php include __DIR__ . '/includes/layouts/header.php'; ?>

  <?php include __DIR__ . '/includes/layouts/breadcrumbs.php'; ?>

  <main x-data="{ filterOpen: false }">
    <div class="container mx-auto px-4 lg:px-6 py-12">
      <div class="text-center mb-12">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900">
          Tất Cả Sản Phẩm
        </h1>
        <p class="mt-4 text-lg text-gray-500">
          Khám phá bộ sưu tập mới nhất của chúng tôi.
        </p>
      </div>

      <div class="lg:hidden mb-6 text-right">
        <button @click="filterOpen = true"
          class="inline-flex items-center gap-2 bg-white px-4 py-2 border border-gray-300 rounded-lg shadow-sm font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
              clip-rule="evenodd" />
          </svg>
          Lọc sản phẩm
        </button>
      </div>

      <div class="flex gap-8">
        <aside :class="{'translate-x-0': filterOpen, '-translate-x-full': !filterOpen}"
          class="fixed lg:static top-0 left-0 h-full w-4/5 max-w-sm bg-white z-50 p-6 transform transition-transform duration-300 ease-in-out lg:transform-none lg:block lg:w-1/4 lg:p-0 lg:bg-transparent">
          <div class="flex justify-between items-center lg:hidden mb-6">
            <h3 class="text-xl font-bold">Bộ lọc</h3>
            <button @click="filterOpen = false">&times;</button>
          </div>

          <div class="mb-8">
            <h3 class="font-semibold text-lg mb-4">Danh mục</h3>
            <ul class="space-y-2">
              <li>
                <a href="/products.php?categoryId=all"
                  class="<?php echo htmlspecialchars('all' === $categoryId ? 'text-blue-600 font-bold' : 'text-gray-600 hover:text-blue-600'); ?>">
                  Tất cả sản phẩm
                </a>
              </li>
              </li>
              <?php foreach ($categories as $category): ?>
              <li>
                <a href="/products.php?categoryId=<?php echo htmlspecialchars($category['id']); ?>"
                  class="<?php echo htmlspecialchars($category['id'] == $categoryId ? 'text-blue-600 font-bold' : 'text-gray-600 hover:text-blue-600'); ?>">
                  <?php echo htmlspecialchars($category['name']); ?>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </aside>

        <div x-show="filterOpen" @click="filterOpen = false" class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>

        <div class="w-full lg:w-3/4">
          <div class="grid grid-cols-2 md:grid-cols-3 gap-6 lg:gap-8">
            <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <a href="/product-detail.php?id=<?php echo htmlspecialchars($product['id']) ?>" class="block group">
              <div
                class="group bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:-translate-y-2">
                <div class="relative overflow-hidden">
                  <img src="<?php echo htmlspecialchars($product['main_image']);  ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500" />
                </div>
                <div class="p-4">
                  <h3 class="font-semibold text-lg truncate">
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <p><?php echo htmlspecialchars($product['id']); ?></p>
                  <p class="text-gray-600 mt-2 font-bold text-xl">
                    <?php echo htmlspecialchars(format_currency($product['price'])); ?>đ
                  </p>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="text-center py-16 border rounded-lg bg-white">
              <p class="text-xl text-gray-500">Không tìm thấy sản phẩm nào.</p>
              <a href="index.php"
                class="inline-block mt-6 bg-gray-900 text-white font-bold py-3 px-6 rounded-lg hover:bg-gray-800 transition-colors">
                Quay về trang chủ
              </a>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/includes/layouts/panigation.php'; ?>

  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>
</body>

</html>