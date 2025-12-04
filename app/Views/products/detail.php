<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-8"><?php echo e($product['name']); ?></h1>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Product Images Section -->
    <div>
      <?php if (!empty($product['images'])): ?>
        <!-- Main Image Display -->
        <div class="mb-4 bg-white rounded-lg shadow-lg overflow-hidden">
          <img id="mainProductImage" 
               src="<?php echo e($product['images'][0]['image_url']); ?>" 
               alt="<?php echo e($product['name']); ?>" 
               class="w-full h-auto object-cover">
        </div>
        
        <!-- Thumbnail Gallery (if multiple images) -->
        <?php if (count($product['images']) > 1): ?>
          <div class="grid grid-cols-4 gap-2">
            <?php foreach ($product['images'] as $index => $image): ?>
              <div class="cursor-pointer border-2 rounded-lg overflow-hidden hover:border-blue-500 transition <?php echo $index === 0 ? 'border-blue-500' : 'border-gray-200'; ?>"
                   onclick="switchImage('<?php echo e($image['image_url']); ?>', this)">
                <img src="<?php echo e($image['image_url']); ?>" 
                     alt="<?php echo e($product['name']); ?> - <?php echo $index + 1; ?>" 
                     class="w-full h-20 object-cover">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <!-- Placeholder if no images -->
        <div class="bg-gray-200 rounded-lg shadow-lg flex items-center justify-center h-96">
          <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Product Info Section -->
    <div>
      <p class="text-2xl font-bold text-gray-900 mb-4"><?php echo format_currency($product['price']); ?></p>
      <p class="text-gray-600 mb-6"><?php echo e($product['description']); ?></p>
      <p class="text-sm text-gray-500 mb-4">Còn lại: <?php echo e($product['stock']); ?> sản phẩm</p>
      
      <?php if (isLoggedIn()): ?>
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">Số lượng:</label>
          <div class="flex items-center gap-3">
            <button onclick="changeQty(-1)" class="w-10 h-10 border border-gray-300 rounded-lg hover:bg-gray-100 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
              </svg>
            </button>
            <input type="number" id="quantity" value="1" min="1" max="<?php echo e($product['stock']); ?>" 
                   class="w-20 px-4 py-2 border border-gray-300 rounded-lg text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button onclick="changeQty(1)" class="w-10 h-10 border border-gray-300 rounded-lg hover:bg-gray-100 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </button>
          </div>
        </div>
        
        <button onclick="addToCart(<?php echo e($product['id']); ?>)" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
          Thêm vào giỏ hàng
        </button>
      <?php else: ?>
        <a href="/auth/login" class="inline-block bg-gray-600 text-white px-8 py-3 rounded-lg hover:bg-gray-700 transition">
          Đăng nhập để mua hàng
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Switch main image when clicking thumbnail
function switchImage(imageUrl, thumbnail) {
  document.getElementById('mainProductImage').src = imageUrl;
  
  // Update active thumbnail border
  document.querySelectorAll('.grid > div').forEach(div => {
    div.classList.remove('border-blue-500');
    div.classList.add('border-gray-200');
  });
  thumbnail.classList.remove('border-gray-200');
  thumbnail.classList.add('border-blue-500');
}

function changeQty(delta) {
  const input = document.getElementById('quantity');
  const max = parseInt(input.max);
  const min = parseInt(input.min);
  let newValue = parseInt(input.value) + delta;
  
  if (newValue >= min && newValue <= max) {
    input.value = newValue;
  }
}

function addToCart(productId) {
  const quantity = document.getElementById('quantity')?.value || 1;
  
  fetch('/cart/add', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `product_id=${productId}&quantity=${quantity}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Notification.success(data.message);
      // Reset quantity
      if (document.getElementById('quantity')) {
        document.getElementById('quantity').value = 1;
      }
    } else {
      Notification.error(data.message);
    }
  })
  .catch(() => Notification.error('Có lỗi xảy ra, vui lòng thử lại'));
}
</script>
