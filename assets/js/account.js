document.addEventListener('DOMContentLoaded', function () {
  // Xử lý đổi mật khẩu
  const passwordForm = document.getElementById('accountPassword');
  if (passwordForm) {
    passwordForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(passwordForm);

      fetch('/actions/change-password.php', {
        method: 'POST',
        body: formData
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Server trả về lỗi: ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            showFlashMessage(data.message, 'success');
            passwordForm.reset();
          } else {
            showFlashMessage(data.message, 'error');
          }
        })
        .catch((error) => {
          console.error('Lỗi Fetch:', error);
          showFlashMessage('Có lỗi xảy ra, vui lòng thử lại.', error);
        });
    });
  }

  // Xử lý cập nhật thông tin: tên, số điện thoại, địa chỉ
  const profileForm = document.getElementById('accountProfile');
  if (profileForm) {
    profileForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(profileForm);

      fetch('/actions/update-profile.php', {
        method: 'POST',
        body: formData
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Server trả về lỗi: ' + response.status);
          }
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
          console.error('Lỗi Fetch:', error);
          showFlashMessage('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        });
    });
  }

});