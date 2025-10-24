// 1. KHAI BÁO BIẾN TIMER Ở BÊN NGOÀI
let flashTimer;

function showFlashMessage(message, type) {
  const flashMessageEl = document.getElementById('flashMessage');
  if (!flashMessageEl) {
    console.error('Không tìm thấy element #flashMessage trong DOM.');
    return;
  }

  const flashMessageText = document.getElementById('flashMessageText');
  const successIcon = document.getElementById('flash-icon-success');
  const errorIcon = document.getElementById('flash-icon-error');

  // Hủy bộ đếm thời gian cũ nếu có
  if (flashTimer) {
    clearTimeout(flashTimer);
  }

  // Cập nhật nội dung và kiểu
  flashMessageText.textContent = message;
  flashMessageEl.classList.remove('bg-green-100', 'text-green-800', 'border-green-400', 'bg-red-100', 'text-red-800', 'border-red-400');
  successIcon.classList.add('hidden');
  errorIcon.classList.add('hidden');

  if (type === 'success') {
    flashMessageEl.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-400');
    successIcon.classList.remove('hidden');
  } else {
    flashMessageEl.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-400');
    errorIcon.classList.remove('hidden');
  }

  // Hiển thị thông báo (chạy animation)
  flashMessageEl.classList.remove('opacity-0', 'translate-y-[-100%]', 'sm:translate-x-full');

  // Tự động ẩn sau 5 giây
  flashTimer = setTimeout(() => {
    flashMessageEl.classList.add('opacity-0', 'translate-y-[-100%]', 'sm:translate-x-full');
  }, 5000);
}


