<div class="container mx-auto px-4 py-8">
  <div class="flex gap-8">
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 hidden lg:block">
      <div class="sticky top-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold mb-4">Danh Mục</h2>
          
          <nav class="space-y-2">
            <a href="/product" 
               class="flex items-center justify-between px-4 py-3 rounded-lg transition <?php echo empty($category_id) && empty($keyword) ? 'bg-blue-50 text-blue-600 font-semibold' : 'hover:bg-gray-50' ?>">
              <span>Tất cả sản phẩm</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
            
            <?php if (!empty($categories)): ?>
              <?php foreach ($categories as $cat): ?>
                <a href="/product?category=<?php echo e($cat['id']); ?>" 
                   class="flex items-center justify-between px-4 py-3 rounded-lg transition <?php echo ($category_id == $cat['id']) ? 'bg-blue-50 text-blue-600 font-semibold' : 'hover:bg-gray-50' ?>">
                  <span><?php echo e($cat['name']); ?></span>
                  <span class="text-sm bg-gray-100 px-2 py-1 rounded-full"><?php echo e($cat['product_count']); ?></span>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1">
      <!-- Mobile Category Filter -->
      <div class="lg:hidden mb-6">
        <select onchange="window.location.href='/product?category=' + this.value" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Tất cả sản phẩm</option>
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo e($cat['id']); ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                <?php echo e($cat['name']); ?> (<?php echo e($cat['product_count']); ?>)
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- Header -->
      <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold">
            <?php if (!empty($keyword)): ?>
              Kết quả tìm kiếm: "<?php echo e($keyword); ?>"
            <?php elseif (!empty($category_id)): ?>
              <?php 
                $currentCat = array_filter($categories ?? [], fn($c) => $c['id'] == $category_id);
                echo !empty($currentCat) ? e(reset($currentCat)['name']) : 'Sản phẩm';
              ?>
            <?php else: ?>
              Tất Cả Sản Phẩm
            <?php endif; ?>
          </h1>
          <p class="text-gray-600 mt-1"><?php echo $totalProducts ?? 0; ?> sản phẩm</p>
        </div>

        <!-- Search Box -->
        <form action="/product/search" method="GET" class="flex gap-2 w-full sm:w-auto">
          <input type="text" 
                 name="keyword" 
                 value="<?php echo e($keyword ?? ''); ?>" 
                 placeholder="Tìm kiếm sản phẩm..." 
                 class="flex-1 sm:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <button type="submit" 
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="hidden sm:inline">Tìm</span>
          </button>
        </form>
      </div>

      <!-- Products Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product): ?>
            <a href="/product/detail/<?php echo e($product['id']) ?>" class="block group">
              <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                <div class="relative overflow-hidden aspect-square">
                  <img src="<?php echo e($product['main_image'] ?? 'https://via.placeholder.com/300'); ?>"
                    alt="<?php echo e($product['name']); ?>"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                  <?php if ($product['is_featured'] ?? false): ?>
                    <div class="absolute top-4 left-4 bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                      Nổi bật
                    </div>
                  <?php endif; ?>
                  <?php if (($product['stock'] ?? 0) <= 5 && ($product['stock'] ?? 0) > 0): ?>
                    <div class="absolute top-4 right-4 bg-orange-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                      Còn <?php echo e($product['stock']); ?>
                    </div>
                  <?php elseif (($product['stock'] ?? 0) == 0): ?>
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                      <span class="text-white font-bold text-lg">Hết hàng</span>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="p-4">
                  <p class="text-xs text-gray-500 mb-1"><?php echo e($product['category_name'] ?? 'Uncategorized'); ?></p>
                  <h3 class="font-semibold text-lg mb-2 line-clamp-2 group-hover:text-blue-600 transition">
                    <?php echo e($product['name']); ?>
                  </h3>
                  <div class="flex items-center justify-between">
                    <p class="text-gray-900 font-bold text-xl">
                      <?php echo format_currency($product['price']); ?>
                    </p>
                    <?php if (($product['stock'] ?? 0) > 0): ?>
                      <button onclick="addToCartQuick(<?php echo e($product['id']); ?>, event)" 
                              class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-full text-center py-16">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 text-lg mb-4">Không tìm thấy sản phẩm.</p>
            <a href="/product" class="inline-block text-blue-600 hover:underline">Xem tất cả sản phẩm</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if (!empty($products) && ($totalPages ?? 0) > 1): ?>
        <div class="flex justify-center gap-2">
          <?php
            $queryParams = [];
            if (!empty($category_id)) $queryParams[] = 'category=' . $category_id;
            if (!empty($keyword)) $queryParams[] = 'keyword=' . urlencode($keyword);
            $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
          ?>
          
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i . $queryString; ?>" 
               class="px-4 py-2 rounded-lg <?php echo $i == ($current_page ?? 1) ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'; ?> transition">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function addToCartQuick(productId, event) {
  event.preventDefault();
  event.stopPropagation();
  
  <?php if (!isLoggedIn()): ?>
    Notification.warning('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
    setTimeout(() => window.location.href = '/auth/login', 1500);
    return;
  <?php endif; ?>
  
  fetch('/cart/add', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `product_id=${productId}&quantity=1`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Notification.success(data.message);
    } else {
      Notification.error(data.message);
    }
  })
  .catch(() => Notification.error('Có lỗi xảy ra, vui lòng thử lại'));
}
</script>
