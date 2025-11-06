<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles(['admin', 'employee']);

$role = $_SESSION['role'];
$account_id = $_SESSION['id'];

//  Lấy thông tin tài khoản bằng prepared statement
try {
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->execute([$account_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching account: " . $e->getMessage());
    $account = ['name' => 'Unknown', 'email' => '', 'phone' => '', 'address' => '', 'position' => '', 'salary' => 0];
}

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
    header("Location: index.php");
    exit;
}

// ✅ SỬA: Lấy thống kê cho admin
if ($role === 'admin') {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('accepted')");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_revenue = $result['total'] ?? 0;

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM accounts WHERE role='customer'");
        $total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (PDOException $e) {
        error_log("Error fetching stats: " . $e->getMessage());
        $total_products = 0;
        $total_orders = 0;
        $total_revenue = 0;
        $total_customers = 0;
    }
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
    <div class="container-fluid my-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">
                    <i class="bi bi-speedometer2"></i>
                    <?= ucfirst(htmlspecialchars($role)) ?>
                </h1>
                <small class="text-muted">Xin chào, <?= htmlspecialchars($account['name']) ?></small>
            </div>
            <div class="d-flex gap-2">
                <a href="/actions/logout.php" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
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

        <!-- Thông tin cá nhân -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-circle"></i> Thông tin cá nhân
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tên:</strong> <?= htmlspecialchars($account['name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($account['email']) ?></p>
                        <?php if ($role === 'admin' || $role === 'employee'): ?>
                            <p><strong>Chức vụ:</strong> <?= htmlspecialchars($account['position'] ?? '-') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>SĐT:</strong> <?= htmlspecialchars($account['phone'] ?? '-') ?></p>
                        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($account['address'] ?? '-') ?></p>
                        <?php if ($role === 'admin' || $role === 'employee'): ?>
                            <p><strong>Lương:</strong> <?= number_format($account['salary'] ?? 0) ?>đ</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Báo cáo cho Admin -->
        <?php if ($role === 'admin'): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <a href="doanhthu.php" class="text-decoration-none">
                        <div class="card report-card" style="border-color: #28a745;">
                            <div class="card-body text-center">
                                <i class="bi bi-graph-up text-success" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Doanh thu & KPI</h5>
                                <p class="text-muted mb-0">Xem báo cáo chi tiết</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="accounts.php" class="text-decoration-none">
                        <div class="card report-card" style="border-color: #007bff;">
                            <div class="card-body text-center">
                                <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Quản lý tài khoản</h5>
                                <p class="text-muted mb-0">Quản lý nhân viên & khách hàng</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="categories.php" class="text-decoration-none">
                        <div class="card report-card" style="border-color:rgb(242, 131, 13);">
                            <div class="card-body text-center">
                                <i class="bi bi-box text-warning" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Quản lý danh mục</h5>
                                <p class="text-muted mb-0">Xem chi tiết danh mục sản phẩm</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="thongke.php" class="text-decoration-none">
                        <div class="card report-card" style="border-color:rgb(246, 230, 5);">
                            <div class="card-body text-center">
                                <i class="bi bi-trophy text-warning" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Thống kê nhanh</h5>
                                <p class="text-muted mb-0">Xem tổng quan hệ thống</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Danh sách sản phẩm -->
        <?php if ($role === 'admin' || $role === 'employee'): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Danh sách sản phẩm</h5>

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

        <!-- Đơn hàng cần xử lý -->
        <?php if ($role === 'admin' || $role === 'employee'): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-cart-check"></i> Đơn hàng cần xử lý</h5>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>

</html>