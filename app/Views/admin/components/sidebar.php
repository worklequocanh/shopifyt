<div class="sidebar p-3">
  <nav class="nav flex-column">
    <!-- Dashboard -->
    <a href="/admin/dashboard" 
       class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false || $_SERVER['REQUEST_URI'] === '/admin') ? 'active' : ''; ?>">
      <i class="bi bi-speedometer2"></i>
      Dashboard
    </a>

    <?php if (Permission::can(Permission::MANAGE_PRODUCTS)): ?>
      <!-- Products -->
      <a href="/admin/products" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/products') !== false ? 'active' : ''; ?>">
        <i class="bi bi-archive"></i>
        Danh sách sản phẩm
      </a>
    <?php endif; ?>

    <?php if (Permission::can(Permission::VIEW_ALL_ORDERS)): ?>
      <!-- Orders -->
      <a href="/admin/orders" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/orders') !== false ? 'active' : ''; ?>">
        <i class="bi bi-cart"></i>
        Xử lý đơn hàng
      </a>
    <?php endif; ?>

    <?php if (Permission::can(Permission::MANAGE_CATEGORIES)): ?>
      <!-- Categories -->
      <a href="/admin/categories" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/categories') !== false ? 'active' : ''; ?>">
        <i class="bi bi-box"></i>
        Quản lý danh mục
      </a>
    <?php endif; ?>

    <?php if (Permission::can(Permission::MANAGE_ACCOUNTS)): ?>
      <!-- Accounts -->
      <a href="/admin/accounts" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/accounts') !== false ? 'active' : ''; ?>">
        <i class="bi bi-people"></i>
        Quản lý tài khoản
      </a>
    <?php endif; ?>

    <?php if (Permission::can(Permission::MANAGE_VOUCHERS)): ?>
      <!-- Vouchers -->
      <a href="/admin/vouchers" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/vouchers') !== false ? 'active' : ''; ?>">
        <i class="bi bi-ticket-perforated"></i>
        Mã giảm giá
      </a>
    <?php endif; ?>

    <?php if (Permission::can(Permission::VIEW_REPORTS)): ?>
      <!-- Reports -->
      <hr class="my-2">
      <a href="/admin/reports/revenue" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/reports/revenue') !== false ? 'active' : ''; ?>">
        <i class="bi bi-graph-up"></i>
        Doanh thu & KPI
      </a>

      <a href="/admin/reports/stats" 
         class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/reports/stats') !== false ? 'active' : ''; ?>">
        <i class="bi bi-trophy"></i>
        Thống kê chi tiết
      </a>
    <?php endif; ?>
  </nav>
</div>
