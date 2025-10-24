<?php

require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/product_functions.php';

$page_title = 'Trang chủ';

$featured_products = getFeaturedProducts($pdo, 4);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-50 text-gray-800">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>

  <main>
    <section class="relative h-[50vh] md:h-[calc(100vh-80px)] bg-cover bg-center" style="
          background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop');
        ">
      <div class="absolute inset-0 bg-black/40"></div>
      <div class="relative h-full flex flex-col items-center justify-center text-center text-white px-4">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4 leading-tight">
          New Collection Is Here
        </h1>
        <p class="max-w-xl md:text-lg mb-8">
          Khám phá những xu hướng mới nhất và định hình phong cách của bạn.
        </p>
        <a href="/products.php"
          class="bg-white text-gray-900 font-bold py-3 px-8 rounded-full hover:bg-gray-200 transition-transform hover:scale-105">
          Shop Now
        </a>
      </div>
    </section>

    <section class="py-16 lg:py-24">
      <div class="container mx-auto px-4 lg:px-6">
        <h2 class="text-3xl lg:text-4xl font-bold text-center mb-12">
          Sản Phẩm Nổi Bật
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 lg:gap-8">
          <?php if (!empty($featured_products)): ?>
            <?php foreach ($featured_products as $product): ?>
              <a href="/product-detail.php?id=<?php echo htmlspecialchars($product['id']) ?>" class="block group">
                <div class="group bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:-translate-y-2">
                  <div class="relative overflow-hidden">
                    <img src="<?php echo htmlspecialchars($product['main_image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>"
                      class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500" />
                    <!-- <div class="absolute top-4 left-4 bg-white text-gray-900 text-xs font-semibold px-3 py-1 rounded-full">
                  New
                </div> -->
                  </div>
                  <div class="p-4">
                    <h3 class="font-semibold text-lg truncate">
                      <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    <p class="text-gray-600 mt-2 font-bold text-xl">
                      <?php echo htmlspecialchars(format_currency($product['price'])); ?></p>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="group bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:-translate-y-2">
              <div class="relative overflow-hidden">
                <img src="https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?q=80&w=1887&auto=format&fit=crop"
                  alt="Áo thun đen"
                  class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500" />
                <div class="absolute top-4 left-4 bg-white text-gray-900 text-xs font-semibold px-3 py-1 rounded-full">
                  New
                </div>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-lg truncate">
                  Áo Thun Cotton Basic
                </h3>
                <p class="text-gray-600 mt-2 font-bold text-xl">250.000đ</p>
              </div>
            </div>

            <div class="group bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:-translate-y-2">
              <div class="relative overflow-hidden">
                <img src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?q=80&w=1888&auto=format&fit=crop"
                  alt="Váy hoa"
                  class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500" />
                <div class="absolute top-4 left-4 bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                  -20%
                </div>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-lg truncate">Váy Hoa Vintage</h3>
                <p class="text-gray-600 mt-2 font-bold text-xl">950.000đ</p>
              </div>
            </div>

          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>
</body>

</html>