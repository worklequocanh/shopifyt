<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Quản lý đơn hàng</h2>
            <p class="text-muted mb-0">Theo dõi và xử lý đơn hàng</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="/admin/orders" method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Mã đơn, tên, SĐT..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select bg-light">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="accepted" <?php echo $status == 'accepted' ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control bg-light" value="<?php echo $start_date ?? ''; ?>" title="Từ ngày">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control bg-light" value="<?php echo $end_date ?? ''; ?>" title="Đến ngày">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Lọc</button>
                </div>
                <div class="col-md-1">
                    <a href="/admin/orders" class="btn btn-outline-secondary w-100" title="Đặt lại"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Order List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-primary">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></div>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($order['order_date'])); ?></small>
                                    </td>
                                    <td class="fw-bold">
                                        <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ
                                    </td>
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
                                        <span class="badge <?php echo $statusClass; ?> rounded-pill px-3">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="/admin/orders/detail/<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            Xem chi tiết <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Không tìm thấy đơn hàng nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white py-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
