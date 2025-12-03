<style media="print">
    @page { size: 80mm auto; margin: 0mm; }
    body { background-color: white; margin: 5px; font-family: 'Courier New', Courier, monospace; font-size: 12px; color: black; }
    .sidebar, .navbar, .btn, .card-header, .dropdown, footer, .d-flex.justify-content-between { display: none !important; }
    .card { border: none !important; shadow: none !important; }
    .container-fluid { padding: 0 !important; width: 100% !important; }
    .row { display: block !important; margin: 0 !important; }
    .col-lg-8, .col-lg-4 { width: 100% !important; padding: 0 !important; margin-bottom: 10px !important; }
    
    /* Receipt Style */
    .receipt-header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
    .receipt-title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
    .receipt-info { font-size: 11px; margin-bottom: 5px; }
    
    .table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 10px; }
    .table th { border-bottom: 1px dashed #000 !important; text-align: left; padding: 2px 0; font-size: 11px; }
    .table td { border: none !important; padding: 2px 0; font-size: 11px; }
    .table .text-end { text-align: right !important; }
    .table .text-center { text-align: center !important; }
    .table tfoot td { border-top: 1px dashed #000 !important; padding-top: 5px !important; font-weight: bold; }
    
    .receipt-footer { text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; font-size: 11px; }
    
    /* Hide images in print */
    img { display: none !important; }
    
    /* Hide non-essential elements */
    .text-muted { color: black !important; }
    hr { border-top: 1px dashed #000 !important; opacity: 1; }
</style>

<div class="container-fluid">
    <!-- Print Header (Hidden on Screen) -->
    <div class="d-none d-print-block receipt-header">
        <div class="receipt-title">SHOPIFYT</div>
        <div class="receipt-info">Hóa đơn bán lẻ</div>
        <div class="receipt-info">Mã đơn: #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
        <div class="receipt-info">Ngày: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h2 class="h3 mb-0 text-gray-800">Chi tiết đơn hàng #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
        <div>
            <button onclick="window.print()" class="btn btn-outline-primary me-2">
                <i class="bi bi-printer"></i> In hóa đơn
            </button>
            <a href="/admin/orders" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Info -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-print-none">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>SP</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">ĐG</th>
                                    <th class="text-end">Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['main_image'])): ?>
                                                    <img src="<?php echo $item['main_image']; ?>" alt="" class="rounded me-2 d-print-none" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                                        <td class="text-end"><?php echo number_format($item['quantity'] * $item['unit_price'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng:</td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Shipping Info -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-print-none">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-print-none">
                        <label class="small text-muted mb-1">Trạng thái</label>
                        <div class="dropdown">
                            <?php
                                $statusColor = match($order['status']) {
                                    'pending' => 'warning',
                                    'accepted' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                $statusLabel = match($order['status']) {
                                    'pending' => 'Chờ xử lý',
                                    'accepted' => 'Đã chấp nhận',
                                    'cancelled' => 'Đã hủy',
                                    default => $order['status']
                                };
                            ?>
                            <button class="btn btn-<?php echo $statusColor; ?> dropdown-toggle w-100" 
                                    type="button" id="statusDropdown" data-bs-toggle="dropdown">
                                <?php echo $statusLabel; ?>
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li>
                                    <a class="dropdown-item <?php echo $order['status'] == 'pending' ? 'disabled' : ''; ?>" 
                                       href="#" onclick="updateStatus(<?php echo $order['id']; ?>, 'pending')">Chờ xử lý</a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo $order['status'] == 'accepted' ? 'disabled' : ''; ?>" 
                                       href="#" onclick="updateStatus(<?php echo $order['id']; ?>, 'accepted')">Chấp nhận</a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo $order['status'] == 'cancelled' ? 'disabled' : ''; ?>" 
                                       href="#" onclick="updateStatus(<?php echo $order['id']; ?>, 'cancelled')">Hủy đơn</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <hr class="d-print-none">

                    <div class="mb-3">
                        <label class="small text-muted mb-1 d-print-none">Khách hàng:</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted mb-1 d-print-none">SĐT:</label>
                        <div><?php echo htmlspecialchars($order['shipping_phone']); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted mb-1 d-print-none">Địa chỉ:</label>
                        <div><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                    </div>

                    <div class="mb-3 d-print-none">
                        <label class="small text-muted mb-1">Ngày đặt hàng</label>
                        <div><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Footer -->
    <div class="d-none d-print-block receipt-footer">
        <div>Cảm ơn quý khách!</div>
        <div>Hẹn gặp lại</div>
    </div>
</div>

<script>
function updateStatus(orderId, status) {
    if (!confirm('Bạn có chắc muốn thay đổi trạng thái đơn hàng?')) return;

    console.log('Updating status...', { orderId, status });

    fetch('/admin/orders/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${orderId}&status=${status}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            showAdminToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAdminToast(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAdminToast('Lỗi: ' + error.message, 'error');
    });
}
</script>
