<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-8">Giỏ Hàng</h1>

  <?php if (!empty($cart_items)): ?>
    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Cart Items -->
      <div class="lg:col-span-2">
        <!-- Master Checkbox -->
        <div class="flex items-center gap-3 mb-4 p-4 bg-gray-50 rounded-lg">
          <input type="checkbox" id="select-all" class="w-5 h-5 cursor-pointer" checked>
          <label for="select-all" class="font-semibold cursor-pointer select-none">Chọn tất cả</label>
        </div>

        <div id="cart-items">
          <?php foreach ($cart_items as $item): ?>
            <?php $outOfStock = $item['is_out_of_stock']; ?>
            <div class="cart-item flex items-center gap-4 border-b pb-4 mb-4 <?php echo $outOfStock ? 'opacity-60' : ''; ?>" 
                 data-id="<?php echo e($item['id']); ?>" 
                 data-price="<?php echo e($item['price']); ?>"
                 data-stock="<?php echo e($item['stock']); ?>">
              
              <!-- Checkbox -->
              <input type="checkbox" 
                     class="item-checkbox w-5 h-5 cursor-pointer" 
                     data-id="<?php echo e($item['id']); ?>"
                     <?php echo $outOfStock ? 'disabled' : 'checked'; ?>>
              
              <!-- Image -->
              <img src="<?php echo e($item['main_image']); ?>" alt="<?php echo e($item['name']); ?>" class="w-24 h-24 object-cover rounded">
              
              <!-- Product Info -->
              <div class="flex-1">
                <h3 class="font-semibold text-lg"><?php echo e($item['name']); ?></h3>
                <p class="text-gray-600">Đơn giá: <span class="font-semibold"><?php echo format_currency($item['price']); ?></span></p>
                <p class="text-sm text-gray-500">Tồn kho: <?php echo e($item['stock']); ?></p>
                <?php if ($outOfStock): ?>
                  <p class="text-red-600 font-semibold mt-1">⚠️ Hết hàng (Số lượng trong giỏ vượt quá tồn kho)</p>
                <?php endif; ?>
              </div>
              
              <!-- Quantity Controls -->
              <div class="flex items-center gap-2">
                <button onclick="Cart.updateQty(<?php echo e($item['id']); ?>, -1)" 
                        class="qty-btn w-8 h-8 border rounded hover:bg-gray-100 font-bold <?php echo $outOfStock ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                        <?php echo $outOfStock ? 'disabled' : ''; ?>>−</button>
                <input type="number" 
                       class="qty-input w-16 px-2 py-1 border rounded text-center" 
                       value="<?php echo e($item['quantity']); ?>" 
                       min="1" 
                       max="<?php echo e($item['stock']); ?>"
                       onchange="Cart.changeQty(<?php echo e($item['id']); ?>, this.value)"
                       data-qty="<?php echo e($item['quantity']); ?>"
                       <?php echo $outOfStock ? 'disabled' : ''; ?>>
                <button onclick="Cart.updateQty(<?php echo e($item['id']); ?>, 1)" 
                        class="qty-btn w-8 h-8 border rounded hover:bg-gray-100 font-bold <?php echo $outOfStock ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                        <?php echo $outOfStock ? 'disabled' : ''; ?>>+</button>
              </div>
              
              <!-- Subtotal -->
              <div class="w-32 text-right">
                <p class="item-subtotal font-bold text-lg"><?php echo format_currency($item['subtotal']); ?></p>
              </div>
              
              <!-- Remove Button -->
              <button onclick="Cart.remove(<?php echo e($item['id']); ?>)" 
                      class="text-red-600 hover:text-red-800 p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Cart Summary -->
      <div class="lg:col-span-1">
        <div class="bg-gray-50 rounded-lg p-6 sticky top-4">
          <h2 class="text-xl font-bold mb-4">Tổng Quan Đơn Hàng</h2>
          
          <div class="space-y-3 mb-6">
            <div class="flex justify-between">
              <span class="text-gray-600">Tạm tính (<span id="selected-count">0</span> sản phẩm):</span>
              <span id="cart-total" class="font-semibold"><?php echo format_currency($total_amount); ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Phí vận chuyển:</span>
              <span class="text-green-600">Miễn phí</span>
            </div>
            <div class="border-t pt-3 flex justify-between text-lg font-bold">
              <span>Tổng cộng:</span>
              <span id="cart-final-total" class="text-blue-600"><?php echo format_currency($total_amount); ?></span>
            </div>
          </div>

          <button onclick="Cart.checkout()" 
                  id="checkout-btn"
                  class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
            Tiến hành thanh toán
          </button>
          <a href="/product" class="block w-full mt-3 bg-gray-200 text-gray-800 text-center py-3 rounded-lg hover:bg-gray-300 transition">
            Tiếp tục mua sắm
          </a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      <p class="text-gray-600 text-lg mb-6">Giỏ hàng của bạn đang trống</p>
      <a href="/product" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Tiếp tục mua sắm
      </a>
    </div>
  <?php endif; ?>
</div>

<style>
/* Hide number input arrows */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.qty-input[type=number] {
  -moz-appearance: textfield;
}

/* Indeterminate checkbox style */
#select-all:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10h8'/%3e%3c/svg%3e");
}
</style>

<script>
const Cart = {
    // Update quantity
    updateQty(productId, delta) {
        const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
        const input = item.querySelector('.qty-input');
        const stock = parseInt(item.dataset.stock);
        const currentQty = parseInt(input.dataset.qty);
        const newQty = currentQty + delta;
        
        // Validate
        if (newQty < 1) {
            Notification.warning('Số lượng phải lớn hơn 0');
            return;
        }
        if (newQty > stock) {
            Notification.warning(`Kho chỉ còn ${stock} sản phẩm`);
            return;
        }
        
        // Update UI immediately
        input.value = newQty;
        input.dataset.qty = newQty;
        this.updateItemSubtotal(item, newQty);
        this.updateTotal();
        
        // Send to server
        this.sendUpdate(productId, newQty);
    },
    
    // Change quantity from input
    changeQty(productId, value) {
        const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
        const input = item.querySelector('.qty-input');
        const stock = parseInt(item.dataset.stock);
        const newQty = parseInt(value);
        const oldQty = parseInt(input.dataset.qty);
        
        if (newQty < 1 || newQty > stock) {
            input.value = oldQty;
            Notification.warning(newQty < 1 ? 'Số lượng phải lớn hơn 0' : `Kho chỉ còn ${stock} sản phẩm`);
            return;
        }
        
        input.dataset.qty = newQty;
        this.updateItemSubtotal(item, newQty);
        this.updateTotal();
        this.sendUpdate(productId, newQty);
    },
    
    // Send update to server
    sendUpdate(productId, quantity) {
        fetch('/cart/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Notification.success('Đã cập nhật');
            } else {
                Notification.error(data.message);
                location.reload();
            }
        })
        .catch(() => {
            Notification.error('Có lỗi xảy ra');
            location.reload();
        });
    },
    
    // Update item subtotal
    updateItemSubtotal(item, qty) {
        const price = parseFloat(item.dataset.price);
        const subtotal = price * qty;
        item.querySelector('.item-subtotal').textContent = this.formatCurrency(subtotal);
    },
    
    // Remove item
    remove(productId) {
        if (!confirm('Xóa sản phẩm khỏi giỏ hàng?')) return;
        
        fetch('/cart/remove', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Notification.success('Đã xóa');
                const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    this.updateTotal();
                    if (!document.querySelectorAll('.cart-item').length) {
                        location.reload();
                    }
                }, 300);
            } else {
                Notification.error(data.message);
            }
        })
        .catch(() => Notification.error('Có lỗi xảy ra'));
    },
    
    // Update total
    updateTotal() {
        let total = 0;
        let count = 0;
        
        document.querySelectorAll('.item-checkbox:checked:not(:disabled)').forEach(checkbox => {
            const item = checkbox.closest('.cart-item');
            const price = parseFloat(item.dataset.price);
            const qty = parseInt(item.querySelector('.qty-input').dataset.qty);
            total += price * qty;
            count++;
        });
        
        document.getElementById('selected-count').textContent = count;
        document.getElementById('cart-total').textContent = this.formatCurrency(total);
        document.getElementById('cart-final-total').textContent = this.formatCurrency(total);
        
        // Update checkout button state
        const checkoutBtn = document.getElementById('checkout-btn');
        if (count === 0) {
            checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
            checkoutBtn.disabled = true;
        } else {
            checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            checkoutBtn.disabled = false;
        }
    },
    
    // Checkout
    checkout() {
        const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked:not(:disabled)'))
            .map(cb => cb.dataset.id);
        
        if (selectedIds.length === 0) {
            Notification.warning('Vui lòng chọn ít nhất một sản phẩm');
            return;
        }
        
        // Create hidden form to POST selected IDs
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/checkout';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_items';
        input.value = JSON.stringify(selectedIds);
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    },
    
    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    },
    
    // Update master checkbox state
    updateMasterCheckbox() {
        const checkboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
        const checked = document.querySelectorAll('.item-checkbox:checked:not(:disabled)');
        const master = document.getElementById('select-all');
        
        if (checked.length === 0) {
            master.checked = false;
            master.indeterminate = false;
        } else if (checked.length === checkboxes.length) {
            master.checked = true;
            master.indeterminate = false;
        } else {
            master.checked = false;
            master.indeterminate = true;
        }
    }
};

// Master checkbox handler
document.getElementById('select-all')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = this.checked);
    Cart.updateTotal();
});

// Item checkbox handlers
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        Cart.updateMasterCheckbox();
        Cart.updateTotal();
    });
});

// Initialize
Cart.updateMasterCheckbox();
Cart.updateTotal();
</script>
