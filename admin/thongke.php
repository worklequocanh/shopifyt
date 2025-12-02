<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles($pdo, ['admin', 'employee']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;
// Thiết lập thời gian bắt đầu thống kê
if (isset($_GET['starts']) && !empty($_GET['starts'])) {
    $starts = $_GET['starts'];
} else {
    // Mặc định từ đầu năm nay
    $starts = date('Y-01-01');
}

// Lấy thông tin account
$account_result = $pdo->query("SELECT * FROM accounts WHERE id = $account_id");
$account = $account_result->fetch();

// Lấy thống kê tổng quan
$stats = [];

// Tổng sản phẩm
$result = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $result->fetch()['count'];

// Tổng doanh thu
$result = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0) as total 
    FROM orders 
    WHERE status IN ('paid', 'shipped', 'accepted')
    AND order_date >= '$starts'
");
$stats['total_revenue'] = $result->fetch()['total'];

// Tổng đơn hàng
$result = $pdo->query("
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE status IN ('paid', 'shipped', 'accepted')
    AND order_date >= '$starts'
");
$stats['total_orders'] = $result->fetch()['count'];

// Tổng khách hàng
$result = $pdo->query("SELECT COUNT(*) as count FROM accounts WHERE role='customer'");
$stats['total_customers'] = $result->fetch()['count'];

// Đơn hàng chờ xử lý
$result = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status='pending'");
$stats['pending_orders'] = $result->fetch()['count'];

// Lấy dữ liệu cho biểu đồ doanh thu theo tháng
$revenue_query = "
    SELECT MONTH(order_date) as month, 
           SUM(total_amount) as revenue
    FROM orders
    WHERE order_date >= '$starts'
    AND status IN ('paid', 'shipped', 'accepted')
    GROUP BY MONTH(order_date)
    ORDER BY month
";
$monthly_revenue = $pdo->query($revenue_query);

$revenue_data = array_fill(1, 12, 0);
if ($monthly_revenue) {
    while ($row = $monthly_revenue->fetch()) {
        $revenue_data[$row['month']] = $row['revenue'];
    }
}

// Lấy dữ liệu cho modal nếu có request
$filter_type = $_GET['filter'] ?? null;
$modal_data = null;

if ($filter_type) {
    switch ($filter_type) {
        case 'products':
            $modal_data = $pdo->query("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC
            ");
            break;
        case 'orders':
            $modal_data = $pdo->query("
                SELECT o.*, a.name as customer_name 
                FROM orders o 
                LEFT JOIN accounts a ON o.account_id = a.id 
                WHERE o.status IN ('paid', 'shipped', 'accepted')
                AND o.order_date >= '$starts'
                ORDER BY o.order_date DESC
            ");
            break;
        case 'customers':
            $modal_data = $pdo->query("
                SELECT * FROM accounts 
                WHERE role='customer' 
                ORDER BY created_at DESC
            ");
            break;
        case 'pending':
            $modal_data = $pdo->query("
                SELECT o.*, a.name as customer_name 
                FROM orders o 
                LEFT JOIN accounts a ON o.account_id = a.id 
                WHERE o.status='pending' 
                ORDER BY o.order_date DESC
            ");
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê tổng quan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            min-height: 180px;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: bold;
            margin: 10px 0;
            word-break: break-word;
        }

        .stat-label {
            font-size: clamp(0.9rem, 2vw, 1.1rem);
            color: #fff;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .chart-container {
            position: relative;
            height: 350px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .stat-card {
                min-height: 150px;
            }

            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid my-3 my-md-5 px-2 px-md-3">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h3 mb-0">
                    <i class="bi bi-bar-chart-fill"></i> Thống kê tổng quan
                </h1>
                <small class="text-muted">Xin chào, <?= htmlspecialchars($account['name']) ?> (<?= ucfirst($role) ?>)</small>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Quay lại</span>
                </a>
            </div>
        </div>

        <!-- Bộ lọc thời gian -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="starts" class="form-label">
                            <i class="bi bi-calendar"></i> Thống kê từ ngày:
                        </label>
                        <input type="date" id="starts" name="starts"
                            value="<?= htmlspecialchars($starts) ?>"
                            class="form-control">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Xem thống kê
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="thongke.php" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Đặt lại
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Thẻ thống kê -->
        <div class="row g-3 g-md-4 mb-4 mb-md-5">
            <!-- Tổng sản phẩm -->
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-gradient-primary text-white" onclick="showModal('products')">
                    <div class="card-body text-center p-3 p-md-4">
                        <i class="bi bi-box-seam stat-icon"></i>
                        <div class="stat-number"><?= $stats['total_products'] ?></div>
                        <div class="stat-label">Sản phẩm</div>
                        <small class="text-white-50 d-none d-md-block mt-2">
                            <i class="bi bi-hand-index"></i> Click để xem chi tiết
                        </small>
                    </div>
                </div>
            </div>

            <!-- Tổng doanh thu -->
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-gradient-success text-white" onclick="showModal('orders')">
                    <div class="card-body text-center p-3 p-md-4">
                        <i class="bi bi-cash-coin stat-icon"></i>
                        <div class="stat-number"><?= number_format($stats['total_revenue']) ?>đ</div>
                        <div class="stat-label">Doanh thu</div>
                        <small class="text-white-50 d-none d-md-block mt-2">
                            <i class="bi bi-hand-index"></i> Click để xem đơn hàng
                        </small>
                    </div>
                </div>
            </div>

            <!-- Tổng đơn hàng -->
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-gradient-warning text-white" onclick="showModal('orders')">
                    <div class="card-body text-center p-3 p-md-4">
                        <i class="bi bi-cart-check stat-icon"></i>
                        <div class="stat-number"><?= $stats['total_orders'] ?></div>
                        <div class="stat-label">Đơn hàng</div>
                        <small class="text-white-50 d-none d-md-block mt-2">Đơn đã hoàn thành</small>
                    </div>
                </div>
            </div>

            <!-- Tổng khách hàng -->
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-gradient-info text-white" onclick="showModal('customers')">
                    <div class="card-body text-center p-3 p-md-4">
                        <i class="bi bi-people stat-icon"></i>
                        <div class="stat-number"><?= $stats['total_customers'] ?></div>
                        <div class="stat-label">Khách hàng</div>
                        <small class="text-white-50 d-none d-md-block mt-2">
                            <i class="bi bi-hand-index"></i> Click để xem danh sách
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng chờ xử lý -->
        <?php if ($stats['pending_orders'] > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 2rem;"></i>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-1">
                                    Có <?= $stats['pending_orders'] ?> đơn hàng chờ xử lý
                                </h5>
                                <p class="mb-0">Vui lòng kiểm tra và xử lý các đơn hàng đang chờ</p>
                            </div>
                            <button class="btn btn-warning" onclick="showModal('pending')">
                                <i class="bi bi-eye"></i> Xem ngay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Biểu đồ doanh thu -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i>
                            Biểu đồ doanh thu theo tháng (Năm <?= date('Y') ?>)
                        </h5>
                        <small>Từ ngày: <?= date('d/m/Y', strtotime($starts)) ?></small>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ và thống kê chi tiết -->
        <div class="row g-3 g-md-4">
            <!-- Top sản phẩm bán chạy -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-trophy"></i> Top 5 sản phẩm bán chạy
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            // Truy vấn top sản phẩm bán chạy (nếu có bảng order_details)
                            $sql = "
                                    SELECT p.name, p.price, SUM(od.quantity) AS sold
                                    FROM products p
                                    JOIN order_details od ON p.id = od.product_id
                                    JOIN orders o ON od.order_id = o.id

                                    WHERE o.status IN ('accepted')
                                    AND o.order_date >= :starts
                                    
                                    GROUP BY p.id, p.name, p.price
                                    ORDER BY sold DESC
                                    LIMIT 5
                                ";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([':starts' => $starts]);
                            $top_products = $stmt->fetchAll();
                        } catch (PDOException $e) {
                            // Nếu lỗi vì bảng không tồn tại hoặc lỗi SQL, gán mảng rỗng
                            $top_products = [];
                        }

                        if (!empty($top_products)): ?>
                            <div class="list-group list-group-flush">
                                <?php
                                $rank = 1;
                                $colors = ['warning', 'secondary', 'dark', 'info', 'primary'];
                                foreach ($top_products as $product): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-<?= $colors[$rank - 1] ?? 'primary' ?> me-2">
                                                #<?= $rank ?>
                                            </span>
                                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                                            <div class="text-success fw-bold mt-1">
                                                <?= number_format($product['price']) ?>đ
                                            </div>
                                        </div>
                                        <span class="badge bg-success rounded-pill">
                                            Đã bán: <?= $product['sold'] ?>
                                        </span>
                                    </div>
                                <?php
                                    $rank++;
                                endforeach;
                                ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">Chưa có dữ liệu</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Đơn hàng gần đây -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> 5 Đơn hàng gần đây
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_orders = $pdo->query("
                            SELECT o.*, a.name as customer_name
                            FROM orders o
                            LEFT JOIN accounts a ON o.account_id = a.id
                            WHERE o.order_date >= '$starts'
                            ORDER BY o.order_date DESC
                            LIMIT 5
                        ");

                        if ($recent_orders && $recent_orders->rowcount() > 0):
                        ?>
                            <div class="list-group list-group-flush">
                                <?php while ($order = $recent_orders->fetch()): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>#<?= $order['id'] ?> - <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></strong>
                                            <span class="badge 
                                                <?php
                                                echo match ($order['status']) {
                                                    'pending' => 'bg-warning text-dark',
                                                    'paid' => 'bg-info',
                                                    'shipped' => 'bg-primary',
                                                    'accepted' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>">
                                                <?= getOrderStatusText($order['status']) ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i>
                                                <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>
                                            </small>
                                            <span class="text-success fw-bold">
                                                <?= number_format($order['total_amount']) ?>đ
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">Chưa có đơn hàng</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal hiển thị chi tiết -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden content for modals -->
    <div style="display: none;">
        <!-- Products Modal Content -->
        <?php if ($filter_type === 'products' && $modal_data): ?>
            <div id="modal-content-products">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $modal_data->fetch()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="text-success fw-bold"><?= number_format($row['price']) ?>đ</td>
                                    <td>
                                        <span class="badge <?= $row['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $row['stock'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $row['is_active'] ? 'Đang bán' : 'Ngừng bán' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Orders Modal Content -->
        <?php if ($filter_type === 'orders' && $modal_data): ?>
            <div id="modal-content-orders">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-success">
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
                            <?php while ($row = $modal_data->fetch()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                                    <td class="text-success fw-bold"><?= number_format($row['total_amount']) ?>đ</td>
                                    <td>
                                        <span class="badge bg-<?= getOrderStatusBadge($row['status']) ?>">
                                            <?= getOrderStatusText($row['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Customers Modal Content -->
        <?php if ($filter_type === 'customers' && $modal_data): ?>
            <div id="modal-content-customers">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-info">
                            <tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Ngày tham gia</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $modal_data->fetch()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $row['is_active'] ? 'Hoạt động' : 'Bị khóa' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pending Orders Modal Content -->
        <?php if ($filter_type === 'pending' && $modal_data): ?>
            <div id="modal-content-pending">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Ngày đặt</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $modal_data->fetch()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                                    <td class="text-success fw-bold"><?= number_format($row['total_amount']) ?>đ</td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-success" target="_blank">
                                            <i class="bi bi-check-circle"></i> Xử lý
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ doanh thu
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?= json_encode(array_values($revenue_data)) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                    'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
                ],
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: revenueData,
                    borderColor: 'rgb(17, 153, 142)',
                    backgroundColor: 'rgba(17, 153, 142, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });

        // Hàm hiển thị modal
        function showModal(type) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');

            const titles = {
                'products': '<i class="bi bi-box-seam"></i> Danh sách sản phẩm',
                'orders': '<i class="bi bi-cart-check"></i> Danh sách đơn hàng',
                'customers': '<i class="bi bi-people"></i> Danh sách khách hàng',
                'pending': '<i class="bi bi-exclamation-triangle"></i> Đơn hàng chờ xử lý'
            };

            modalTitle.innerHTML = titles[type] || 'Chi tiết';
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

            // Mở modal
            modal.show();

            // Load nội dung
            const starts = new URLSearchParams(window.location.search).get('starts') || '';
            fetch(`thongke.php?filter=${type}&starts=${starts}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const content = doc.querySelector('#modal-content-' + type);
                    if (content) {
                        modalBody.innerHTML = content.innerHTML;
                    } else {
                        modalBody.innerHTML = '<div class="alert alert-warning">Không tìm thấy dữ liệu</div>';
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div>';
                    console.error('Error:', error);
                });
        }
    </script>
</body>

</html>