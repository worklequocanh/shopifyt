<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800"><i class="bi bi-speedometer2"></i> Thống kê chi tiết</h2>
        <div class="text-muted small">
            <i class="bi bi-clock"></i> Cập nhật: <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="row">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng doanh thu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>đ
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
                                Tổng đơn hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total_orders']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-check fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đơn chờ xử lý</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['pending_orders']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-data fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Khách hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total_customers']); ?>
                            </div>
                            <div class="small text-muted">
                                <?php echo number_format($stats['active_customers']); ?> đã mua hàng
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="row">
        <!-- Today Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Doanh thu hôm nay</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['today_revenue'], 0, ',', '.'); ?>đ
                            </div>
                            <?php
                            $change = $stats['yesterday_revenue'] > 0 
                                ? (($stats['today_revenue'] - $stats['yesterday_revenue']) / $stats['yesterday_revenue']) * 100
                                : 0;
                            $changeClass = $change >= 0 ? 'text-success' : 'text-danger';
                            ?>
                            <div class="small <?php echo $changeClass; ?>">
                                <?php echo $change >= 0 ? '▲' : '▼'; ?> 
                                <?php echo number_format(abs($change), 1); ?>% vs hôm qua
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sản phẩm</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total_products']); ?>
                            </div>
                            <div class="small text-muted">Đang hoạt động</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                KH đã mua hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['active_customers']); ?>
                            </div>
                            <div class="small text-muted">
                                <?php 
                                $conversionRate = $stats['total_customers'] > 0 
                                    ? ($stats['active_customers'] / $stats['total_customers']) * 100 
                                    : 0;
                                echo number_format($conversionRate, 1); 
                                ?>% tỷ lệ chuyển đổi
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avg Lifetime Value -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Giá trị KH TB</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['avg_lifetime_value'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-gem fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="row">
        <!-- Daily Revenue Trend -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Xu hướng doanh thu 30 ngày gần nhất</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top khách hàng</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th class="text-end">Chi tiêu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_customers)): ?>
                                    <?php foreach ($top_customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold"><?php echo htmlspecialchars($customer['name']); ?></div>
                                                <small class="text-muted"><?php echo $customer['total_orders']; ?> đơn</small>
                                            </td>
                                            <td class="text-end">
                                                <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Low Stock Row -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
                    <a href="/admin/orders" class="btn btn-sm btn-primary">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Giá trị</th>
                                    <th>Ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_orders)): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'accepted' => 'success',
                                            'cancelled' => 'danger'
                                        ][$order['status']] ?? 'secondary';
                                        $statusText = [
                                            'pending' => 'Chờ',
                                            'accepted' => 'Hoàn thành',
                                            'cancelled' => 'Hủy'
                                        ][$order['status']] ?? $order['status'];
                                        ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                            <td><span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                            <td class="text-end"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                            <td><?php echo date('d/m H:i', strtotime($order['order_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Chưa có đơn hàng</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sản phẩm sắp hết hàng</h6>
                    <a href="/admin/products" class="btn btn-sm btn-primary">Quản lý kho</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Tồn kho</th>
                                    <th class="text-end">Giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($low_stock_products)): ?>
                                    <?php foreach ($low_stock_products as $product): ?>
                                        <tr class="<?php echo $product['stock'] == 0 ? 'table-danger' : ($product['stock'] <= 5 ? 'table-warning' : ''); ?>">
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?php echo $product['stock'] == 0 ? 'danger' : ($product['stock'] <= 5 ? 'warning' : 'info'); ?>">
                                                    <?php echo $product['stock']; ?>
                                                </span>
                                            </td>
                                            <td class="text-end"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-success py-3">
                                            <i class="bi bi-check-circle"></i> Tất cả sản phẩm đều có đủ hàng
                                        </td>
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
// Daily Trend Chart
const trendCtx = document.getElementById('dailyTrendChart').getContext('2d');
const dailyData = <?php echo json_encode($daily_trend); ?>;

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: dailyData.map(d => {
            const date = new Date(d.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        }),
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: dailyData.map(d => d.revenue),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
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
