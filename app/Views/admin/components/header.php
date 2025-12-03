<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container-fluid">
    <div class="d-flex align-items-center">
      <h1 class="h3 mb-0">
        <i class="bi bi-speedometer2"></i>
        <?php echo ucfirst($current_role ?? 'Admin'); ?>
      </h1>
      <small class="text-muted ms-3">
        Xin chào, <?php echo e($_SESSION['name'] ?? 'Admin'); ?>
      </small>
    </div>

    <div class="d-flex align-items-center gap-3">
      <!-- View Site -->
      <a href="/" target="_blank" class="btn btn-outline-primary btn-sm" title="Xem trang web">
        <i class="bi bi-eye"></i>
        <span class="d-none d-md-inline">Xem trang</span>
      </a>

      <!-- User Info Dropdown (Optional) -->
      <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text"><strong><?php echo e($_SESSION['name'] ?? 'Admin'); ?></strong></span></li>
          <li><span class="dropdown-item-text text-muted small"><?php echo e($_SESSION['email'] ?? ''); ?></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="/account/info"><i class="bi bi-person"></i> Tài khoản</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
        </ul>
      </div>

      <!-- Quick Logout -->
      <a href="/auth/logout" class="btn btn-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i>
        <span class="d-none d-md-inline">Đăng xuất</span>
      </a>
    </div>
  </div>
</nav>

<?php
// Include flash message
require_once __DIR__ . '/../../components/flash-message.php';
?>
