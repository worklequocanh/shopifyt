document.addEventListener('DOMContentLoaded', function () {
  // XỬ LÝ SỰ KIỆN SUBMIT FORM THÊM VÀO GIỎ HÀNG
  const addToCartForm = document.getElementById('addToCartForm');
  if (addToCartForm) {
    addToCartForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(addToCartForm);

      fetch('/actions/add-to-cart.php', {
        method: 'POST',
        body: formData
      })
        .then(response => {
          // Kiểm tra xem response có thành công không (status 200-299)
          if (!response.ok) {
            // Nếu không, ném ra một lỗi để nhảy xuống khối .catch
            throw new Error('Server trả về lỗi: ' + response.status);
          }
          // Nếu thành công, mới tiến hành parse JSON
          return response.json();
        })
        .then(data => {
          if (data.success) {
            showFlashMessage(data.message, 'success');
          } else {
            showFlashMessage(data.message, 'error');
          }
        })
        .catch((error) => {
          // Bây giờ khối catch sẽ bắt cả lỗi mạng và lỗi từ server
          console.error('Lỗi Fetch:', error);
          showFlashMessage('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        });
    });
  }

  // XỬ LÝ SỰ KIỆN TĂNG, GIẢM, XÓA TRONG GIỎ HÀNG
  const cartContainer = document.getElementById('cart-container');
  if (!cartContainer) return;

  // Sử dụng kỹ thuật "Event Delegation"
  cartContainer.addEventListener('click', function (event) {
    const target = event.target;
    const cartItem = target.closest('.cart-item');
    if (!cartItem) return;

    const productId = cartItem.dataset.productId;
    const quantityEl = cartItem.querySelector('.quantity-display');
    let currentQuantity = parseInt(quantityEl.dataset.quantity, 10);

    if (target.matches('.btn-increase')) {
      updateCart(productId, currentQuantity + 1, 'update', cartItem);
    } else if (target.matches('.btn-decrease')) {
      if (currentQuantity > 1) {
        updateCart(productId, currentQuantity - 1, 'update', cartItem);
      }
    } else if (target.matches('.btn-delete')) {
      // Hỏi xác nhận trước khi xóa
      if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        updateCart(productId, 0, 'delete', cartItem);
      }
    }
  });
});

async function updateCart(productId, quantity, action, cartItemElement) {
  try {
    const response = await fetch('/actions/update-cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        // Thêm header CSRF token nếu bạn có sử dụng
      },
      body: JSON.stringify({
        product_id: productId,
        quantity: quantity,
        action: action,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      // Ném lỗi với thông báo từ server
      throw new Error(data.message || 'Có lỗi xảy ra.');
    }

    // Cập nhật giao diện người dùng
    if (action === 'delete') {
      cartItemElement.remove();
    } else {
      // Cập nhật số lượng và thành tiền của item
      const quantityEl = cartItemElement.querySelector('.quantity-display');
      quantityEl.textContent = quantity;
      quantityEl.dataset.quantity = quantity; // Cập nhật lại data-attribute
      cartItemElement.querySelector('.item-subtotal').textContent = data.newItemSubtotal;
    }

    // Cập nhật tổng tiền
    document.getElementById('cart-subtotal').textContent = data.newGrandTotal;
    document.getElementById('cart-grand-total').textContent = data.newGrandTotal;

    // Xử lý khi giỏ hàng trống
    if (data.isEmpty) {
      document.getElementById('cart-container').innerHTML = '<p class="text-center text-gray-500">Giỏ hàng của bạn đang trống.</p>';
    }

    showFlashMessage(data.message, 'success');

  } catch (error) {
    console.error('Lỗi cập nhật giỏ hàng:', error);
    showFlashMessage(error.message, 'error');
  }
}