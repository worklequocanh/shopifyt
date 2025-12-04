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
        <span class="d-none d-md-inline">Trang mua sắm</span>
      </a>

      <!-- Logout Button -->
      <a href="/auth/logout" class="btn btn-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i>
        <span class="d-none d-md-inline">Đăng xuất</span>
      </a>
    </div>
  </div>
</nav>

<?php
// Include admin flash message
require_once __DIR__ . '/flash-message-admin.php';
?>
