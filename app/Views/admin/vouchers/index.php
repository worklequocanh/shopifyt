<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-ticket-perforated-fill"></i> Quản lý mã giảm giá</h2>
            <small class="text-muted">Tổng số: <?php echo count($vouchers); ?> mã</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
            <i class="bi bi-plus-circle"></i> Thêm mã giảm giá
        </button>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Mã Code</th>
                            <th>Tên Voucher</th>
                            <th>Giảm giá</th>
                            <th>Đơn tối thiểu</th>
                            <th>Lượt dùng</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($vouchers)): ?>
                            <?php foreach ($vouchers as $voucher): ?>
                                <?php 
                                    $now = date('Y-m-d H:i:s');
                                    $isExpired = $now > $voucher['end_date'];
                                    $statusClass = $voucher['is_active'] && !$isExpired ? 'bg-success' : 'bg-secondary';
                                    $statusText = $voucher['is_active'] ? ($isExpired ? 'Hết hạn' : 'Đang chạy') : 'Đã tắt';
                                    
                                    $discountDisplay = $voucher['discount_type'] == 'percentage' 
                                        ? number_format($voucher['discount_value']) . '%' 
                                        : number_format($voucher['discount_value'], 0, ',', '.') . 'đ';
                                ?>
                                <tr class="<?php echo !$voucher['is_active'] || $isExpired ? 'table-secondary' : ''; ?>">
                                    <td class="ps-4">
                                        <span class="badge bg-primary fs-6 font-monospace"><?php echo htmlspecialchars($voucher['code']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($voucher['name']); ?></td>
                                    <td class="fw-bold text-danger">
                                        <?php echo $discountDisplay; ?>
                                        <?php if($voucher['discount_type'] == 'percentage' && $voucher['max_discount']): ?>
                                            <div class="small text-muted">Tối đa: <?php echo number_format($voucher['max_discount'], 0, ',', '.'); ?>đ</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($voucher['min_order_value'], 0, ',', '.'); ?>đ</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo $voucher['used_count']; ?> / <?php echo $voucher['usage_limit'] ?? '∞'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">Từ: <?php echo date('d/m/Y', strtotime($voucher['start_date'])); ?></small>
                                        <small class="d-block text-muted">Đến: <?php echo date('d/m/Y', strtotime($voucher['end_date'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning"
                                                    onclick="editVoucher(<?php echo htmlspecialchars(json_encode($voucher)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="/admin/vouchers/delete/<?php echo $voucher['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Xóa mã giảm giá này?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Không tìm thấy mã giảm giá nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Thêm mã giảm giá mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/vouchers/store" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" required placeholder="VD: SUMMER2024">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Voucher <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="VD: Khuyến mãi mùa hè">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Loại giảm giá</label>
                            <select name="discount_type" class="form-select" onchange="toggleDiscountFields(this, 'add')">
                                <option value="percentage">Phần trăm (%)</option>
                                <option value="fixed">Số tiền cố định (VNĐ)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" id="add_discount_label">Giá trị giảm (%) <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" class="form-control" required min="0">
                        </div>
                        <div class="col-md-4 mb-3" id="add_max_discount_group">
                            <label class="form-label">Giảm tối đa (VNĐ)</label>
                            <input type="number" name="max_discount" class="form-control" min="0" step="1000">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Đơn tối thiểu (VNĐ)</label>
                            <input type="number" name="min_order_value" class="form-control" min="0" step="1000" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giới hạn lượt dùng</label>
                            <input type="number" name="usage_limit" class="form-control" min="1" placeholder="Để trống nếu không giới hạn">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_date" class="form-control" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_date" class="form-control" required value="<?php echo date('Y-m-d\TH:i', strtotime('+30 days')); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Cập nhật mã giảm giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/vouchers/update" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_code" class="form-control text-uppercase" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Voucher <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Loại giảm giá</label>
                            <select name="discount_type" id="edit_discount_type" class="form-select" onchange="toggleDiscountFields(this, 'edit')">
                                <option value="percentage">Phần trăm (%)</option>
                                <option value="fixed">Số tiền cố định (VNĐ)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" id="edit_discount_label">Giá trị giảm (%) <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" id="edit_discount_value" class="form-control" required min="0">
                        </div>
                        <div class="col-md-4 mb-3" id="edit_max_discount_group">
                            <label class="form-label">Giảm tối đa (VNĐ)</label>
                            <input type="number" name="max_discount" id="edit_max_discount" class="form-control" min="0" step="1000">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Đơn tối thiểu (VNĐ)</label>
                            <input type="number" name="min_order_value" id="edit_min_order_value" class="form-control" min="0" step="1000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giới hạn lượt dùng</label>
                            <input type="number" name="usage_limit" id="edit_usage_limit" class="form-control" min="1" placeholder="Để trống nếu không giới hạn">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_date" id="edit_start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_date" id="edit_end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Kích hoạt</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDiscountFields(select, type) {
    const isPercentage = select.value === 'percentage';
    const label = document.getElementById(type + '_discount_label');
    const maxGroup = document.getElementById(type + '_max_discount_group');
    
    label.innerText = isPercentage ? 'Giá trị giảm (%)' : 'Số tiền giảm (VNĐ)';
    maxGroup.style.display = isPercentage ? 'block' : 'none';
}

function editVoucher(voucher) {
    document.getElementById('edit_id').value = voucher.id;
    document.getElementById('edit_code').value = voucher.code;
    document.getElementById('edit_name').value = voucher.name;
    document.getElementById('edit_description').value = voucher.description || '';
    
    const typeSelect = document.getElementById('edit_discount_type');
    typeSelect.value = voucher.discount_type;
    toggleDiscountFields(typeSelect, 'edit');
    
    document.getElementById('edit_discount_value').value = voucher.discount_value;
    document.getElementById('edit_max_discount').value = voucher.max_discount || '';
    document.getElementById('edit_min_order_value').value = voucher.min_order_value;
    document.getElementById('edit_usage_limit').value = voucher.usage_limit || '';
    
    // Format dates for datetime-local input (YYYY-MM-DDTHH:mm)
    const startDate = new Date(voucher.start_date);
    startDate.setMinutes(startDate.getMinutes() - startDate.getTimezoneOffset());
    document.getElementById('edit_start_date').value = startDate.toISOString().slice(0, 16);
    
    const endDate = new Date(voucher.end_date);
    endDate.setMinutes(endDate.getMinutes() - endDate.getTimezoneOffset());
    document.getElementById('edit_end_date').value = endDate.toISOString().slice(0, 16);
    
    document.getElementById('edit_is_active').checked = voucher.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editVoucherModal')).show();
}
</script>
