<div class="container mx-auto px-4 py-8">
  <div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold">Thanh Toán</h1>
      
      <div class="flex gap-3">
        <a href="/cart" class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Quay lại giỏ hàng
        </a>
        <a href="/product" class="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          Tiếp tục mua sắm
        </a>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Left Column - Shipping Info -->
      <div class="lg:col-span-2">
        <form method="POST" id="checkoutForm">
          <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-6">Thông Tin Giao Hàng</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-gray-700 mb-2 font-medium">Họ tên *</label>
                <input type="text" name="name" value="<?php echo e($account['name'] ?? ''); ?>" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Số điện thoại *</label>
                  <input type="tel" name="phone" value="<?php echo e($account['phone'] ?? ''); ?>" required
                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 font-medium">Email</label>
                  <input type="email" value="<?php echo e($account['email'] ?? ''); ?>" disabled
                         class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50">
                </div>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 font-medium">Địa chỉ giao hàng *</label>
                <textarea name="address" rows="3" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($account['address'] ?? ''); ?></textarea>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 font-medium">Ghi chú đơn hàng (tùy chọn)</label>
                <textarea name="note" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-6">Phương Thức Thanh Toán</h2>
            
            <div class="space-y-3">
              <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                <input type="radio" name="payment_method" value="cod" checked class="mt-1 mr-3">
                <div class="flex-1">
                  <div class="font-semibold mb-1">Thanh toán khi nhận hàng (COD)</div>
                  <div class="text-sm text-gray-600">Thanh toán bằng tiền mặt khi nhận hàng</div>
                </div>
              </label>

              <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition opacity-50">
                <input type="radio" name="payment_method" value="bank" disabled class="mt-1 mr-3">
                <div class="flex-1">
                  <div class="font-semibold mb-1">Chuyển khoản ngân hàng</div>
                  <div class="text-sm text-gray-600">Sắp ra mắt</div>
                </div>
              </label>
            </div>
          </div>

          <!-- HIDDEN INPUT FOR VOUCHER - MUST BE INSIDE FORM -->
          <input type="hidden" name="voucher_code" id="voucherCodeHidden" value="">

          <button type="submit" 
                  class="w-full mt-6 bg-green-600 text-white text-lg font-bold py-4 rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Đặt Hàng
          </button>
        </form>
      </div>

      <!-- Right Column - Order Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
          <h2 class="text-xl font-bold mb-6">Đơn Hàng Của Bạn</h2>

          <div class="space-y-4 mb-6">
            <?php if (!empty($cart_items)): ?>
              <?php foreach ($cart_items as $item): ?>
                <div class="flex gap-3">
                  <img src="<?php echo e($item['main_image']); ?>" alt="<?php echo e($item['name']); ?>" 
                       class="w-16 h-16 object-cover rounded">
                  <div class="flex-1">
                    <p class="font-medium text-sm"><?php echo e($item['name']); ?></p>
                    <p class="text-sm text-gray-600">Số lượng: <?php echo e($item['quantity']); ?></p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold"><?php echo format_currency($item['subtotal']); ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-center text-gray-500">Giỏ hàng trống</p>
            <?php endif; ?>
          </div>


          <!-- Voucher Section -->
          <div class="border-t border-gray-200 pt-5 pb-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                Mã giảm giá
              </h3>
            </div>
            
            <div class="flex gap-2 mb-2">
              <div class="relative flex-1">
                <input type="text" id="voucherInput" 
                       placeholder="VD: SUMMER2024" 
                       class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all uppercase font-medium text-sm placeholder:normal-case placeholder:font-normal">
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                </svg>
              </div>
              <button type="button" id="applyVoucherBtn" onclick="applyVoucher()"
                      class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <span>Áp dụng</span>
              </button>
            </div>
            
            <div id="voucherMessage" class="mt-2 text-sm hidden"></div>
            
            <div id="voucherApplied" class="mt-3 hidden animate-fade-in">
              <div class="relative overflow-hidden bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-lg p-4 shadow-sm">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-200 rounded-full -mr-16 -mt-16 opacity-20"></div>
                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="bg-green-500 rounded-full p-2">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-green-900 font-bold" id="voucherName"></p>
                      <p class="text-green-700 text-xs">Đã áp dụng thành công</p>
                    </div>
                  </div>
                  <button type="button" onclick="removeVoucher()" 
                          class="text-red-600 hover:text-red-800 hover:bg-red-100 rounded-full p-1.5 transition-colors"
                          title="Hủy mã giảm giá">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            
          </div>

          <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between text-gray-600">
              <span>Tạm tính</span>
              <span id="subtotal"><?php echo format_currency($total_amount ?? 0); ?></span>
            </div>
            <div class="flex justify-between text-gray-600" id="discountRow" style="display: none;">
              <span>Giảm giá</span>
              <span class="text-green-600" id="discountAmount">-0đ</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Phí vận chuyển</span>
              <span>Miễn phí</span>
            </div>
            <div class="border-t pt-2 flex justify-between text-lg font-bold">
              <span>Tổng cộng</span>
              <span class="text-green-600" id="finalTotal"><?php echo format_currency($total_amount ?? 0); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const originalTotal = <?php echo $total_amount ?? 0; ?>;
let appliedVoucher = null;

document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
  const phone = this.querySelector('[name="phone"]').value;
  const address = this.querySelector('[name="address"]').value;

  if (!phone || !address) {
    e.preventDefault();
    if (window.Notification) {
      window.Notification.error('Vui lòng điền đầy đủ thông tin giao hàng');
    }
  }
});

async function applyVoucher() {
  const voucherInput = document.getElementById('voucherInput');
  const code = voucherInput.value.trim().toUpperCase();
  const btn = document.getElementById('applyVoucherBtn');
  
  if (!code) {
    showVoucherMessage('Vui lòng nhập mã giảm giá', 'error');
    return;
  }
  
  btn.disabled = true;
  btn.textContent = 'Đang kiểm tra...';
  
  try {
    const response = await fetch('/api/voucher/validate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `code=${encodeURIComponent(code)}&order_amount=${originalTotal}`
    });
    
    const data = await response.json();
    
    if (data.success) {
      appliedVoucher = data.voucher;
      showVoucherApplied(data.voucher);
      updateTotals(data.voucher.discount_amount, data.voucher.final_amount);
      
      // IMPORTANT: Set the hidden input with UPPERCASE code
      const hiddenInput = document.getElementById('voucherCodeHidden');
      if (hiddenInput) {
        hiddenInput.value = code.toUpperCase();
        console.log('Voucher code set to hidden input:', hiddenInput.value);
      } else {
        console.error('Hidden input voucherCodeHidden not found!');
      }
      
      voucherInput.value = '';
      showVoucherMessage(data.message, 'success');
      
      if (window.Notification) {
        window.Notification.success(data.message);
      }
    } else {
      showVoucherMessage(data.message, 'error');
      if (window.Notification) {
        window.Notification.error(data.message);
      }
    }
  } catch (error) {
    showVoucherMessage('Có lỗi xảy ra khi kiểm tra mã giảm giá', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Áp dụng';
  }
}

function removeVoucher() {
  appliedVoucher = null;
  document.getElementById('voucherApplied').classList.add('hidden');
  
  // Clear hidden input
  const hiddenInput = document.getElementById('voucherCodeHidden');
  if (hiddenInput) {
    hiddenInput.value = '';
    console.log('Voucher code cleared from hidden input');
  }
  
  updateTotals(0, originalTotal);
  showVoucherMessage('Đã hủy mã giảm giá', 'info');
  
  if (window.Notification) {
    window.Notification.info('Đã hủy mã giảm giá');
  }
}

function showVoucherApplied(voucher) {
  const appliedDiv = document.getElementById('voucherApplied');
  const nameSpan = document.getElementById('voucherName');
  
  nameSpan.textContent = voucher.name + ' (' + voucher.code + ')';
  appliedDiv.classList.remove('hidden');
  
  document.getElementById('voucherMessage').classList.add('hidden');
}

function showVoucherMessage(message, type) {
  const messageDiv = document.getElementById('voucherMessage');
  messageDiv.textContent = message;
  messageDiv.className = 'mt-2 text-sm ' + 
    (type === 'success' ? 'text-green-600' : 
     type === 'error' ? 'text-red-600' : 'text-blue-600');
  messageDiv.classList.remove('hidden');
  
  setTimeout(() => {
    messageDiv.classList.add('hidden');
  }, 5000);
}

function updateTotals(discountAmount, finalAmount) {
  const discountRow = document.getElementById('discountRow');
  const discountAmountSpan = document.getElementById('discountAmount');
  const finalTotalSpan = document.getElementById('finalTotal');
  
  if (discountAmount > 0) {
    discountRow.style.display = 'flex';
    discountAmountSpan.textContent = '-' + new Intl.NumberFormat('vi-VN').format(discountAmount) + 'đ';
  } else {
    discountRow.style.display = 'none';
  }
  
  finalTotalSpan.textContent = new Intl.NumberFormat('vi-VN').format(finalAmount) + 'đ';
}

// Allow Enter key to apply voucher
document.getElementById('voucherInput')?.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    applyVoucher();
  }
});
</script>
