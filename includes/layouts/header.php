<?php $logined = isLoggedIn(); ?>
<?php include __DIR__ . '/flash-message.php'; ?>

<header x-data="{ mobileMenuOpen: false, userMenuOpen: false }"
  class="bg-white/80 backdrop-blur-lg sticky top-0 z-50 shadow-sm">
  <nav class="container mx-auto px-4 lg:px-6 py-3">
    <div class="flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold text-gray-900">STYLEX</a>

      <ul class="hidden lg:flex items-center space-x-8 font-medium">
        <li><a href="index.php"
            class="<?php $page_title === 'Trang chủ' ? 'text-gray-900' : 'text-gray-600' ?> hover:text-blue-600 transition-colors">Trang
            chủ</a></li>
        <li><a href="products.php"
            class="<?php $page_title === 'Sản phẩm' ? 'text-gray-900' : 'text-gray-600' ?> hover:text-blue-600 transition-colors">Sản
            phẩm</a></li>
        <li><a href="#"
            class="<?php $page_title === 'Về chúng tôi' ? 'text-gray-900' : 'text-gray-600' ?> hover:text-blue-600 transition-colors">Về
            chúng tôi</a></li>
      </ul>

      <div class="flex items-center space-x-4">
        <!-- <a href="#" class="text-gray-500 hover:text-gray-900 hidden sm:block">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </a> -->
        <a href="shopping-cart.php" class="text-gray-500 hover:text-gray-900 relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </a>

        <?php if ($logined): ?>
          <div class="relative hidden lg:block">
            <button @click="userMenuOpen = !userMenuOpen" class="text-gray-500 hover:text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </button>
            <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-transition
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50">
              <div class="px-4 py-2 text-sm text-gray-700">Chào, <span
                  class="font-medium"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Bạn'); ?></span></div>
              <a href="account-info.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tài khoản của
                tôi</a>
              <a href="../../actions/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Đăng xuất</a>
            </div>
          </div>
        <?php else: ?>
          <div class="hidden lg:flex items-center space-x-2">
            <a href="login.php"
              class="bg-gray-100 text-gray-800 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm">Đăng
              nhập</a>
            <a href="register.php"
              class="bg-gray-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors text-sm">Đăng
              ký</a>
          </div>
        <?php endif; ?>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-500 hover:text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
          </svg>
        </button>
      </div>
    </div>

    <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="lg:hidden mt-4 border-t pt-4">
      <a href="index.php"
        class="block px-4 py-2 rounded-md <?php $page_title === 'Trang chủ' ? 'text-gray-900' : 'text-gray-600' ?>  hover:bg-gray-100">Trang
        chủ</a>
      <a href="products.php"
        class="block px-4 py-2 rounded-md <?php $page_title === 'Sản phẩm' ? 'text-gray-900' : 'text-gray-600' ?>  hover:bg-gray-100">Sản
        phẩm</a>

      <div class="border-t mt-4 pt-4 space-y-2">
        <?php if ($logined): ?>
          <a href="account-info.php" class="block px-4 py-2 rounded-md text-gray-600 hover:bg-gray-100">Tài khoản của
            tôi</a>
          <a href="../../actions/logout.php" class="block px-4 py-2 rounded-md text-gray-600 hover:bg-gray-100">Đăng xuất</a>
        <?php else: ?>
          <a href="login.php"
            class="block text-center bg-gray-100 text-gray-800 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">Đăng
            nhập</a>
          <a href="register.php"
            class="block text-center bg-gray-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">Đăng
            ký</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>