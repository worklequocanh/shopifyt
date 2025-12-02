<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles($pdo, ['admin', 'employee']);

$role = $_SESSION['role'];
$account_id = $_SESSION['id'];

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
        <h1 class="mb-0"><i class="bi bi-box-seam"></i> Danh sách sản phẩm
        </h1>
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
  <!-- Danh sách sản phẩm -->
  <?php if ($role === 'admin' || $role === 'employee'): ?>
    <div class="card mb-4">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">


        <?php if ($role === 'admin'): ?>
          <a href="add.php" class="btn bg-success">
            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
          </a>
        <?php endif; ?>

      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Hình</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá (VNĐ)</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <?php if ($role === 'admin'): ?>
                  <th>Hành động</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($result):
                $products = $result->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($products)):
                  foreach ($products as $row):
              ?>
                    <tr>
                      <td><?= $row['id'] ?></td>
                      <td>
                        <img src="<?= htmlspecialchars(getProductImage($pdo, $row['id'])) ?>"
                          alt="<?= htmlspecialchars($row['name']) ?>"
                          class="product-img">
                      </td>
                      <td><?= htmlspecialchars($row['name']) ?></td>
                      <td><span class="badge bg-secondary"><?= htmlspecialchars($row['category_name'] ?? 'Chưa phân loại') ?></span></td>
                      <td class="fw-bold text-success"><?= number_format($row['price']) ?>đ</td>
                      <td>
                        <span class="badge <?= $row['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                          <?= $row['stock'] ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                          <?= $row['is_active'] ? 'Đang bán' : 'Ngừng bán' ?>
                        </span>
                      </td>
                      <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                      <?php if ($role === 'admin'): ?>
                        <td>
                          <a href="update.php?id=<?= $row['id'] ?>"
                            class="btn btn-sm btn-warning" title="Sửa">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <a href="delete.php?delete=<?= $row['id'] ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Xóa sản phẩm này?')" title="Xóa">
                            <i class="bi bi-trash"></i>
                          </a>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php
                  endforeach;
                else:
                  ?>
                  <tr>
                    <td colspan="9" class="text-center text-muted">Chưa có sản phẩm nào</td>
                  </tr>
              <?php
                endif;
              endif;
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

</body>

</html>