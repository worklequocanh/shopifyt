<?php

require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/account_functions.php';

restrictToRoles('customer');

$page_title = 'Tài khoản của tôi';

$current_tab = $_GET['tab'] ?? 'profile';

$breadcrumbs = [
  ['title' => 'Trang chủ', 'url' => 'index.php'],
  ['title' => 'Tài khoản', 'url' => 'account-info.php']
];

$customer = getLoggedInUserInfo($pdo);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-50 text-gray-800">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>
  <?php include __DIR__ . '/includes/layouts/breadcrumbs.php'; ?>
  <?php include __DIR__ . '/includes/layouts/messages.php'; ?>


  <main class="container mx-auto px-4 lg:px-6 py-16 lg:py-20">
    <div class="mb-12">
      <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900">Tài khoản của tôi</h1>
      <p class="mt-2 text-lg text-gray-500">Quản lý thông tin cá nhân và lịch sử mua hàng của bạn.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
      <aside class="lg:col-span-1">
        <div class="bg-white p-4 rounded-lg shadow-md">
          <nav class="space-y-1">
            <a href="account-info.php?tab=profile" class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold
                <?php echo ($current_tab === 'profile')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
              <span>Thông tin cá nhân</span>
            </a>

            <a href="account-info.php?tab=orders" class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold
                <?php echo ($current_tab === 'orders')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd"
                  d="M4 5a2 2 0 012-2h8a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z"
                  clip-rule="evenodd" />
              </svg>
              <span>Lịch sử đơn hàng</span>
            </a>

            <!-- <a href="account-info.php?tab=wishlist" class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold
                <?php echo ($current_tab === 'wishlist')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?>"
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                  clip-rule="evenodd" />
              </svg>
              <span>Sản phẩm yêu thích</span>
            </a> -->

            <a href="account-info.php?tab=password" class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold
                <?php echo ($current_tab === 'password')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M18 8a6 6 0 11-12 0 6 6 0 0112 0zM7 8a1 1 0 11-2 0 1 1 0 012 0zm5 0a1 1 0 11-2 0 1 1 0 012 0zm5 0a1 1 0 11-2 0 1 1 0 012 0z"
                  clip-rule="evenodd" />
              </svg>
              <span>Đổi mật khẩu</span>
            </a>

            <a href="./actions/logout.php"
              class="flex items-center gap-3 px-4 py-3 rounded-md text-red-600 hover:bg-red-50 font-medium">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"
                  clip-rule="evenodd" />
              </svg>
              <span>Đăng xuất</span>
            </a>
          </nav>
        </div>
      </aside>

      <div class="lg:col-span-3">
        <?php
        // Lấy tab hiện tại từ URL, mặc định là 'profile'
        $tab = $_GET['tab'] ?? 'profile';

        if ($tab === 'profile') {
          include __DIR__ . '/includes/layouts/profile/account_profile.php';
        } elseif ($tab === 'orders') {
          include __DIR__ . '/includes/layouts/profile/account_orders.php';
          // } elseif ($tab === 'wishlist') {
          //   include __DIR__ . '/includes/layouts/profile/account_wishlist.php';
        } elseif ($tab === 'password') {
          include __DIR__ . '/includes/layouts/profile/account_password.php';
        }
        ?>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>

  <script src="/assets/js/account.js"></script>
</body>

</html>