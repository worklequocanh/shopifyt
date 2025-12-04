<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-graph-up"></i> Báo cáo Doanh thu & KPI</h2>
        <form method="GET" action="/admin/reports/revenue" class="d-flex gap-2">
            <select name="month" class="form-select" onchange="this.form.submit()">
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == $current_month ? 'selected' : ''; ?>>
                        Tháng <?php echo $m; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-select" onchange="this.form.submit()">
                <?php for($y=date('Y'); $y>=2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $current_year ? 'selected' : ''; ?>>
                        Năm <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <!-- Enhanced Stats Cards -->
    <div class="row mb-4">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng doanh thu (<?php echo $current_year; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($aggregate_metrics['total_revenue'], 0, ',', '.'); ?>đ
                            </div>
                            <?php
                            $growthClass = $revenue_growth >= 0 ? 'text-success' : 'text-danger';
                            $growthIcon = $revenue_growth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                            ?>
                            <div class="small <?php echo $growthClass; ?>">
                                <i class="bi <?php echo $growthIcon; ?>"></i> 
                                <?php echo number_format(abs($revenue_growth), 1); ?>% vs <?php echo $current_year - 1; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng đơn hàng (<?php echo $current_year; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($aggregate_metrics['total_orders']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-check fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Giá trị đơn TB</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($aggregate_metrics['avg_order_value'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-receipt fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Discounts -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tổng giảm giá</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($aggregate_metrics['total_discounts'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tag fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu năm <?php echo $current_year; ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Phân bổ trạng thái đơn</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie" style="height: 320px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Charts Row -->
    <div class="row">
        <!-- Revenue by Category -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo danh mục</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 sản phẩm bán chạy</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>#</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Số lượng</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_products)): ?>
                                    <?php foreach ($top_products as $index => $product): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="text-end"><?php echo number_format($product['total_quantity']); ?></td>
                                            <td class="text-end"><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?>đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Chưa có dữ liệu</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">KPI Nhân viên (Tháng <?php echo $current_month; ?>)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nhân viên</th>
                                    <th class="text-end">Doanh số</th>
                                    <th class="text-end">Đơn hàng</th>
                                    <th class="text-end">Giá trị TB</th>
                                    <th class="text-end">Tăng trưởng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($kpi_data)): ?>
                                    <?php foreach ($kpi_data as $kpi): ?>
                                        <?php 
                                            $prevSales = $prev_kpi_data[$kpi['id']] ?? 0;
                                            $growth = 0;
                                            if ($prevSales > 0) {
                                                $growth = (($kpi['total_sales'] - $prevSales) / $prevSales) * 100;
                                            } elseif ($kpi['total_sales'] > 0) {
                                                $growth = 100;
                                            }
                                            $growthClass = $growth >= 0 ? 'text-success' : 'text-danger';
                                            $growthIcon = $growth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($kpi['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($kpi['position'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td class="text-end fw-bold">
                                                <?php echo number_format($kpi['total_sales'], 0, ',', '.'); ?>đ
                                            </td>
                                            <td class="text-end">
                                                <?php echo $kpi['orders_processed']; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php echo number_format($kpi['avg_order_value'], 0, ',', '.'); ?>đ
                                            </td>
                                            <td class="text-end <?php echo $growthClass; ?>">
                                                <?php echo number_format(abs($growth), 1); ?>% 
                                                <i class="bi <?php echo $growthIcon; ?>"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu KPI</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Common chart options
const commonOptions = {
    maintainAspectRatio: false,
    responsive: true,
    plugins: {
        legend: {
            display: true,
            position: 'top'
        }
    }
};

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode($revenue_by_month); ?>;

const months = [];
const revenues = [];
const orders = [];

for (let i = 1; i <= 12; i++) {
    months.push('T' + i);
    if (revenueData[i]) {
        revenues.push(revenueData[i].revenue);
        orders.push(revenueData[i].order_count);
    } else {
        revenues.push(0);
        orders.push(0);
    }
}

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: revenues,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true,
            yAxisID: 'y'
        }, {
            label: 'Số đơn hàng',
            data: orders,
            borderColor: '#1cc88a',
            backgroundColor: 'transparent',
            borderDash: [5, 5],
            tension: 0.3,
            yAxisID: 'y1'
        }]
    },
    options: {
        ...commonOptions,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Status Distribution Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = <?php echo json_encode($status_distribution); ?>;

const statusLabels = {
    'pending': 'Chờ xử lý',
    'accepted': 'Đã chấp nhận',
    'cancelled': 'Đã hủy'
};

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusData.map(s => statusLabels[s.status] || s.status),
        datasets: [{
            data: statusData.map(s => s.count),
            backgroundColor: ['#f6c23e', '#36b9cc', '#e74a3b'],
            hoverBackgroundColor: ['#f4b619', '#2c9faf', '#e02d1b'],
            borderWidth: 2
        }]
    },
    options: {
        ...commonOptions,
        cutout: '70%'
    }
});

// Category Revenue Bar Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryData = <?php echo json_encode($revenue_by_category); ?>;

new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: categoryData.map(c => c.category_name),
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: categoryData.map(c => c.revenue),
            backgroundColor: 'rgba(78, 115, 223, 0.8)',
            borderColor: '#4e73df',
            borderWidth: 1
        }]
    },
    options: {
        ...commonOptions,
        indexAxis: 'y',
        scales: {
            x: {
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    }
                }
            }
        }
    }
});
</script>
