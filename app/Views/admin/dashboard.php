<!-- Statistics Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-shrink-0 rounded p-2 bg-success bg-opacity-10 text-success">
            <i class="bi bi-currency-dollar fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted mb-0">Doanh thu</h6>
          </div>
        </div>
        <h3 class="mb-0 fw-bold"><?php echo format_currency($stats['total_revenue'] ?? 0); ?></h3>
        <small class="text-muted">Tổng doanh thu thực tế</small>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-shrink-0 rounded p-2 bg-primary bg-opacity-10 text-primary">
            <i class="bi bi-cart fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted mb-0">Đơn hàng</h6>
          </div>
        </div>
        <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_orders'] ?? 0); ?></h3>
        <small class="text-warning fw-bold"><?php echo $stats['pending_orders'] ?? 0; ?> chờ xử lý</small>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-shrink-0 rounded p-2 bg-info bg-opacity-10 text-info">
            <i class="bi bi-box-seam fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted mb-0">Sản phẩm</h6>
          </div>
        </div>
        <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_products'] ?? 0); ?></h3>
        <small class="text-muted">Đang kinh doanh</small>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="flex-shrink-0 rounded p-2 bg-warning bg-opacity-10 text-warning">
            <i class="bi bi-people fs-4"></i>
          </div>
          <div class="ms-3">
            <h6 class="text-muted mb-0">Khách hàng</h6>
          </div>
        </div>
        <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_customers'] ?? 0); ?></h3>
        <small class="text-muted">Tài khoản đăng ký</small>
      </div>
    </div>
  </div>
</div>

<!-- Revenue Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary"></i> Biểu đồ doanh thu 6 tháng gần nhất</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
  <!-- Recent Orders -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary"></i> Đơn hàng gần đây</h5>
        <a href="/admin/orders" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4">Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_orders)): ?>
                <?php foreach ($recent_orders as $order): ?>
                  <tr>
                    <td class="ps-4">
                        <span class="fw-bold text-primary">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-light rounded-circle text-center me-2" style="width: 32px; height: 32px; line-height: 32px;">
                                <i class="bi bi-person text-secondary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td class="fw-bold"><?php echo format_currency($order['total_amount']); ?></td>
                    <td>
                      <?php 
                        $statusClass = match($order['status']) {
                            'pending' => 'bg-warning text-dark',
                            'accepted' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $statusText = match($order['status']) {
                            'pending' => 'Chờ xử lý',
                            'accepted' => 'Đã duyệt',
                            'cancelled' => 'Đã hủy',
                            default => $order['status']
                        };
                      ?>
                      <span class="badge <?php echo $statusClass; ?> rounded-pill"><?php echo $statusText; ?></span>
                    </td>
                    <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                    <td class="text-end pe-4">
                        <a href="/admin/orders/detail/<?php echo $order['id']; ?>" class="btn btn-sm btn-light text-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Chưa có đơn hàng nào</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Top Products -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-trophy text-warning"></i> Top sản phẩm bán chạy</h5>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if (!empty($top_products)): ?>
            <?php foreach ($top_products as $index => $product): ?>
              <div class="list-group-item px-4 py-3 border-bottom-0">
                <div class="d-flex align-items-center">
                  <div class="me-3 position-relative">
                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-<?php echo $index < 3 ? 'warning' : 'secondary'; ?>">
                        <?php echo $index + 1; ?>
                    </span>
                    <img src="<?php echo !empty($product['main_image']) ? $product['main_image'] : 'https://via.placeholder.com/60'; ?>" 
                         class="rounded border" 
                         style="width: 60px; height: 60px; object-fit: cover;"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1 text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($product['name']); ?></h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Đã bán: <span class="fw-bold text-dark"><?php echo number_format($product['total_sold']); ?></span></small>
                        <small class="fw-bold text-success"><?php echo format_currency($product['total_revenue']); ?></small>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center py-4 text-muted">Chưa có dữ liệu</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Data from PHP
    const labels = <?php echo json_encode($revenue_data['labels'] ?? []); ?>;
    const data = <?php echo json_encode($revenue_data['values'] ?? []); ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumSignificantDigits: 3 }).format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
