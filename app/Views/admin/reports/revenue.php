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

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng doanh thu (Năm <?php echo $current_year; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_revenue, 0, ',', '.'); ?>đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng đơn hàng (Năm <?php echo $current_year; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_orders); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-check fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu năm <?php echo $current_year; ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Table -->
        <div class="col-xl-4 col-lg-5">
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
                                            <td class="text-end">
                                                <?php echo number_format($kpi['total_sales'], 0, ',', '.'); ?>đ
                                                <div class="small text-muted"><?php echo $kpi['orders_processed']; ?> đơn</div>
                                            </td>
                                            <td class="text-end <?php echo $growthClass; ?>">
                                                <?php echo number_format(abs($growth), 1); ?>% <i class="bi <?php echo $growthIcon; ?>"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu KPI</td>
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
// Chart.js implementation
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode($revenue_by_month); ?>;

const months = [];
const revenues = [];
const orders = [];

for (let i = 1; i <= 12; i++) {
    months.push('Tháng ' + i);
    if (revenueData[i]) {
        revenues.push(revenueData[i].revenue);
        orders.push(revenueData[i].order_count);
    } else {
        revenues.push(0);
        orders.push(0);
    }
}

new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: revenues,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
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
        maintainAspectRatio: false,
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
                        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
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
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.dataset.yAxisID === 'y') {
                            label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw);
                        } else {
                            label += context.raw;
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>
