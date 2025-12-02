<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles($pdo, ['admin', 'employee']);

$role = $_SESSION['role'];
$account_id = $_SESSION['id'];

// Xử lý đơn hàng cho admin/employee
if (($role === 'admin' || $role === 'employee') && isset($_GET['process'])) {
  $order_id = intval($_GET['process']);
  try {
    $stmt = $pdo->prepare("UPDATE orders SET status='paid' WHERE id = ?");
    $stmt->execute([$order_id]);
    $_SESSION['success_message'] = 'Đã xử lý đơn hàng!';
  } catch (PDOException $e) {
    $_SESSION['error_message'] = 'Lỗi xử lý đơn hàng!';
    error_log("Error processing order: " . $e->getMessage());
  }
  header("Location: order-list.php");
  exit;
}

// Lấy danh sách sản phẩm
$result = null;
$order_result = null;

if ($role === 'admin' || $role === 'employee') {
  try {
    $sql = "SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
    $result = $pdo->query($sql);

    // Lấy đơn hàng cần xử lý
    $order_result = $pdo->query("
            SELECT o.*, a.name as customer_name
            FROM orders o
            LEFT JOIN accounts a ON o.account_id = a.id
            ORDER BY o.order_date DESC
            LIMIT 10
        ");
  } catch (PDOException $e) {
    error_log("Error fetching products/orders: " . $e->getMessage());
  }
}

// Lấy thông báo
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// ✅ SỬA: Lấy thông tin account
try {
  $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ?");
  $stmt->execute([$account_id]);
  $account = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$account) {
    $account = ['name' => 'Unknown'];
  }
} catch (PDOException $e) {
  error_log("Error fetching account: " . $e->getMessage());
  $account = ['name' => 'Unknown'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= ucfirst(htmlspecialchars($role)) ?> - Quản lý</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    .product-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .report-card {
      cursor: pointer;
      transition: all 0.3s ease;
      border-left: 4px solid;
    }

    .report-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .stat-card {
      border-left: 4px solid;
      min-height: 120px;
    }

    .stat-number {
      font-size: 2rem;
      font-weight: bold;
    }
  </style>
</head>

<body class="bg-light">
  <!-- header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
      <h1 class="h3 mb-0">
        <h1 class="mb-0"><i class="bi bi-cart-check"></i>Xử Lý Đơn Hàng
        </h1>
        <small class="text-muted">Xin chào, <?= htmlspecialchars($account['name']) ?> (<?= ucfirst($role) ?>)</small>
    </div>
    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Quay lại</span>
      </a>
    </div>
  </div>
  <!-- Thông báo -->
  <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <!-- Đơn hàng cần xử lý -->
  <?php if ($role === 'admin' || $role === 'employee'): ?>
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Đơn hàng cần xử lý</h5>
      </div>
      <div class="card-body">
        <?php
        if ($order_result):
          $orders = $order_result->fetchAll(PDO::FETCH_ASSOC);
          if (!empty($orders)):
        ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $order): ?>
                    <tr>
                      <td>#<?= $order['id'] ?></td>
                      <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                      <td class="fw-bold text-success"><?= number_format($order['total_amount']) ?>đ</td>
                      <td><span class="badge
                                                    <?php if ($order['status'] === 'accepted') {
                                                      echo htmlspecialchars("bg-success");
                                                    } elseif ($order['status'] === 'pending') {
                                                      echo htmlspecialchars("bg-warning");
                                                    } else {
                                                      echo htmlspecialchars("bg-danger");
                                                    } ?>">
                          <?= $order['status'] ?></span></td>
                      <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                      <td>
                        <a href="edit.php?id=<?= $order['id'] ?>"
                          class="btn btn-sm btn-primary">
                          <i class="bi bi-eye"></i> Chi tiết
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center mb-0">Không có đơn hàng cần xử lý</p>
        <?php
          endif;
        endif;
        ?>
      </div>
    </div>
  <?php endif; ?>

</body>

</html>