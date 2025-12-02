<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles($pdo, ['admin', 'employee']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;

// Lấy tháng/năm từ URL hoặc mặc định tháng hiện tại
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Tính tháng trước để so sánh KPI
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

// ✅ SỬA: Lấy dữ liệu doanh thu theo tháng năm hiện tại
$revenue_by_month = [];
$total_revenue = 0;
$total_orders = 0;

try {
    $stmt = $pdo->prepare("
        SELECT MONTH(order_date) as month, 
               SUM(total_amount) as revenue, 
               COUNT(id) as order_count
        FROM orders
        WHERE YEAR(order_date) = ? 
        AND status IN ('paid', 'shipped', 'accepted')
        GROUP BY MONTH(order_date)
        ORDER BY month
    ");
    $stmt->execute([$current_year]);

    while ($rev = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total_revenue += $rev['revenue'];
        $total_orders += $rev['order_count'];
        $revenue_by_month[$rev['month']] = $rev;
    }
} catch (PDOException $e) {
    error_log("Error fetching revenue data: " . $e->getMessage());
}

// ✅ SỬA: Lấy KPI tháng hiện tại
$kpi_current_data = [];
try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.name, a.position,
               COUNT(DISTINCT o.id) as orders_processed,
               COALESCE(SUM(o.total_amount), 0) as total_sales,
               COALESCE(AVG(o.total_amount), 0) as avg_order_value
        FROM accounts a
        LEFT JOIN orders o ON o.account_id = a.id 
            AND MONTH(o.order_date) = ? 
            AND YEAR(o.order_date) = ?
            AND o.status IN ('paid', 'shipped', 'accepted')
        WHERE a.role IN ('admin', 'employee')
        GROUP BY a.id, a.name, a.position
        ORDER BY total_sales DESC
    ");
    $stmt->execute([$current_month, $current_year]);
    $kpi_current_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching current KPI: " . $e->getMessage());
}

// ✅ SỬA: Lấy KPI tháng trước để so sánh
$prev_kpi_data = [];
try {
    $stmt = $pdo->prepare("
        SELECT a.id,
               COALESCE(SUM(o.total_amount), 0) as total_sales
        FROM accounts a
        LEFT JOIN orders o ON o.account_id = a.id 
            AND MONTH(o.order_date) = ? 
            AND YEAR(o.order_date) = ?
            AND o.status IN ('paid', 'shipped', 'accepted')
        WHERE a.role IN ('admin', 'employee')
        GROUP BY a.id
    ");
    $stmt->execute([$prev_month, $prev_year]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $prev_kpi_data[$row['id']] = $row['total_sales'];
    }
} catch (PDOException $e) {
    error_log("Error fetching previous KPI: " . $e->getMessage());
}

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
    <title>Báo cáo Doanh thu & KPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s ease;
            cursor: pointer;
            min-height: 150px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .revenue-card {
            border-color: #28a745;
        }

        .orders-card {
            border-color: #007bff;
        }

        .avg-card {
            border-color: #ffc107;
        }

        .growth-card {
            border-color: #17a2b8;
        }

        .stat-number {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: bold;
            margin: 10px 0;
            word-break: break-word;
        }

        .kpi-rank-1 {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        }

        .kpi-rank-2 {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
        }

        .kpi-rank-3 {
            background: linear-gradient(135deg, #cd7f32 0%, #e8b687 100%);
        }

        .month-selector {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            position: relative;
            height: 400px;
        }

        @media (max-width: 768px) {
            .stat-card {
                min-height: 120px;
            }

            .chart-container {
                height: 300px;
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
                    <i class="bi bi-bar-chart-line"></i> Báo cáo Doanh thu & KPI
                </h1>
                <small class="text-muted">Xin chào, <?= htmlspecialchars($account['name']) ?> (<?= ucfirst($role) ?>)</small>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Quay lại</span>
                </a>
            </div>
        </div>

        <!-- Chọn tháng/năm -->
        <div class="month-selector mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label"><i class="bi bi-calendar"></i> Chọn tháng</label>
                    <select name="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $current_month ? 'selected' : '' ?>>
                                Tháng <?= $m ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label"><i class="bi bi-calendar-event"></i> Chọn năm</label>
                    <select name="year" class="form-select">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $current_year ? 'selected' : '' ?>>
                                Năm <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Xem báo cáo
                    </button>
                </div>
            </form>
        </div>

        <!-- Thống kê tổng quan năm -->
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card stat-card revenue-card">
                    <div class="card-body text-center p-2 p-md-3">
                        <i class="bi bi-cash-coin text-success"></i>
                        <div class="stat-number text-success">
                            <?= number_format($total_revenue, 0) ?>đ
                        </div>
                        <p class="text-muted">Tổng doanh thu năm <?= $current_year ?></p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card avg-card">
                    <div class="card-body text-center p-2 p-md-3">
                        <i class="bi bi-graph-up text-warning"></i>
                        <div class="stat-number text-warning">
                            <?= number_format($total_orders > 0 ? $total_revenue / $total_orders : 0, 0) ?>đ
                        </div>
                        <p class="text-muted">Giá trị TB/Đơn</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card growth-card">
                    <div class="card-body text-center p-2 p-md-3">
                        <i class="bi bi-trophy text-info"></i>
                        <div class="stat-number text-info">
                            <?= count($revenue_by_month) ?>
                        </div>
                        <p class="text-muted">Tháng có doanh thu</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card orders-card">
                    <div class="card-body text-center p-2 p-md-3">
                        <i class="bi bi-cart-check text-primary"></i>
                        <div class="stat-number text-primary">
                            <?= $total_orders ?>
                        </div>
                        <p class="text-muted">Tổng đơn hàng</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up-arrow"></i>
                    Biểu đồ doanh thu năm <?= $current_year ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Báo cáo doanh thu theo tháng -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i>
                    Báo cáo doanh thu từng tháng - Năm <?= $current_year ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($revenue_by_month)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-success">
                                <tr>
                                    <th>Tháng</th>
                                    <th>Số đơn hàng</th>
                                    <th>Doanh thu</th>
                                    <th>TB/Đơn hàng</th>
                                    <th>% Tổng DT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <?php if (isset($revenue_by_month[$m])):
                                        $rev = $revenue_by_month[$m];
                                        $avg = $rev['order_count'] > 0 ? $rev['revenue'] / $rev['order_count'] : 0;
                                        $percent = $total_revenue > 0 ? ($rev['revenue'] / $total_revenue) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td class="fw-bold">Tháng <?= $m ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?= $rev['order_count'] ?></span> đơn
                                            </td>
                                            <td class="text-success fw-bold">
                                                <?= number_format($rev['revenue']) ?>đ
                                            </td>
                                            <td><?= number_format($avg) ?>đ</td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success"
                                                        style="width: <?= $percent ?>%">
                                                        <?= number_format($percent, 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr class="table-light">
                                            <td class="text-muted">Tháng <?= $m ?></td>
                                            <td colspan="4" class="text-muted text-center">Chưa có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-info">
                                <tr class="fw-bold">
                                    <td>TỔNG NĂM</td>
                                    <td><span class="badge bg-primary"><?= $total_orders ?></span> đơn</td>
                                    <td class="text-success"><?= number_format($total_revenue) ?>đ</td>
                                    <td><?= number_format($total_orders > 0 ? $total_revenue / $total_orders : 0) ?>đ</td>
                                    <td>100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i>
                        Chưa có dữ liệu doanh thu cho năm <?= $current_year ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Báo cáo KPI nhân viên -->
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="bi bi-trophy-fill"></i>
                    Bảng xếp hạng KPI - Tháng <?= $current_month ?>/<?= $current_year ?>
                    <small class="ms-2">(So sánh với tháng <?= $prev_month ?>/<?= $prev_year ?>)</small>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($kpi_current_data)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-warning">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Nhân viên</th>
                                    <th width="15%">Chức vụ</th>
                                    <th width="12%">Đơn xử lý</th>
                                    <th width="18%">Doanh số</th>
                                    <th width="15%">TB/Đơn</th>
                                    <th width="10%">So sánh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rank = 1;
                                foreach ($kpi_current_data as $kpi):
                                    $current_sales = $kpi['total_sales'] ?? 0;
                                    $previous_sales = $prev_kpi_data[$kpi['id']] ?? 0;

                                    // Tính % tăng trưởng
                                    if ($previous_sales > 0) {
                                        $growth = (($current_sales - $previous_sales) / $previous_sales) * 100;
                                    } else {
                                        $growth = $current_sales > 0 ? 100 : 0;
                                    }

                                    $row_class = '';
                                    if ($rank == 1) $row_class = 'kpi-rank-1';
                                    elseif ($rank == 2) $row_class = 'kpi-rank-2';
                                    elseif ($rank == 3) $row_class = 'kpi-rank-3';
                                ?>
                                    <tr class="<?= $row_class ?>">
                                        <td class="text-center fw-bold">
                                            <?php if ($rank <= 3): ?>
                                                <i class="bi bi-trophy-fill" style="font-size: 1.5rem;"></i>
                                            <?php else: ?>
                                                <?= $rank ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($kpi['name']) ?></td>
                                        <td><?= htmlspecialchars($kpi['position'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $kpi['orders_processed'] ?? 0 ?></span>
                                        </td>
                                        <td class="text-success fw-bold">
                                            <?= number_format($current_sales, 0) ?>đ
                                        </td>
                                        <td><?= number_format($kpi['avg_order_value'] ?? 0, 0) ?>đ</td>
                                        <td>
                                            <?php if ($growth > 0): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-arrow-up"></i> <?= number_format($growth, 1) ?>%
                                                </span>
                                            <?php elseif ($growth < 0): ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-arrow-down"></i> <?= number_format(abs($growth), 1) ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-dash"></i> 0%
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                    $rank++;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Chú thích -->
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Chú thích:</strong>
                        <ul class="mb-0 mt-2">
                            <li><i class="bi bi-trophy-fill text-warning"></i> Top 3 nhân viên xuất sắc nhất tháng</li>
                            <li><span class="badge bg-success"><i class="bi bi-arrow-up"></i></span> Tăng trưởng so với tháng trước</li>
                            <li><span class="badge bg-danger"><i class="bi bi-arrow-down"></i></span> Giảm so với tháng trước</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i>
                        Chưa có dữ liệu KPI cho tháng <?= $current_month ?>/<?= $current_year ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer info -->
        <div class="text-center mt-4 text-muted">
            <small>
                <i class="bi bi-clock"></i> Báo cáo được tạo lúc: <?= date('d/m/Y H:i:s') ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dữ liệu cho biểu đồ
        const monthLabels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
        ];

        const revenueByMonth = <?= json_encode($revenue_by_month) ?>;
        const revenueData = [];
        const orderData = [];

        for (let i = 1; i <= 12; i++) {
            if (revenueByMonth[i]) {
                revenueData.push(revenueByMonth[i].revenue);
                orderData.push(revenueByMonth[i].order_count);
            } else {
                revenueData.push(0);
                orderData.push(0);
            }
        }

        // Tạo biểu đồ
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenueData,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    yAxisID: 'y'
                }, {
                    label: 'Số đơn hàng',
                    data: orderData,
                    type: 'line',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.datasetIndex === 0) {
                                        label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                                    } else {
                                        label += context.parsed.y + ' đơn';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value) + 'đ';
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' đơn';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>