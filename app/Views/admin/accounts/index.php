<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-people-fill"></i> Quản lý tài khoản</h2>
            <small class="text-muted">Tổng số: <?php echo count($accounts); ?> tài khoản</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="bi bi-person-plus"></i> Thêm tài khoản
        </button>
    </div>

    <!-- Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="/admin/accounts" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-funnel"></i> Vai trò</label>
                    <select name="role" class="form-select">
                        <option value="all" <?php echo $filter_role == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                        <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="employee" <?php echo $filter_role == 'employee' ? 'selected' : ''; ?>>Nhân viên</option>
                        <option value="customer" <?php echo $filter_role == 'customer' ? 'selected' : ''; ?>>Khách hàng</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-search"></i> Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tên hoặc email..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Chức vụ</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php foreach ($accounts as $account): ?>
                                <tr class="<?php echo !$account['is_active'] ? 'table-secondary' : ''; ?>">
                                    <td class="ps-4">#<?php echo $account['id']; ?></td>
                                    <td>
                                        <span class="fw-bold"><?php echo htmlspecialchars($account['name']); ?></span>
                                        <?php if ($account['id'] == $current_user_id): ?>
                                            <span class="badge bg-info ms-1">Bạn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($account['email']); ?></td>
                                    <td>
                                        <?php
                                        $roleClass = match($account['role']) {
                                            'admin' => 'bg-danger',
                                            'employee' => 'bg-warning text-dark',
                                            default => 'bg-info'
                                        };
                                        ?>
                                        <span class="badge <?php echo $roleClass; ?>">
                                            <?php echo ucfirst($account['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($account['position'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $account['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $account['is_active'] ? 'Hoạt động' : 'Đã khóa'; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning"
                                                    onclick="editAccount(<?php echo htmlspecialchars(json_encode($account)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($account['id'] != $current_user_id): ?>
                                                <a href="/admin/accounts/toggle/<?php echo $account['id']; ?>" 
                                                   class="btn btn-sm btn-outline-<?php echo $account['is_active'] ? 'secondary' : 'success'; ?>"
                                                   onclick="return confirm('<?php echo $account['is_active'] ? 'Khóa' : 'Mở khóa'; ?> tài khoản này?')">
                                                    <i class="bi bi-<?php echo $account['is_active'] ? 'lock' : 'unlock'; ?>"></i>
                                                </a>
                                                <a href="/admin/accounts/delete/<?php echo $account['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Xóa tài khoản này?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Không tìm thấy tài khoản nào
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
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Thêm tài khoản mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/accounts/store" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required onchange="toggleEmployeeFields(this, 'add')">
                                <option value="customer">Khách hàng</option>
                                <option value="employee">Nhân viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 employee-field-add" style="display:none;">
                            <label class="form-label">Chức vụ</label>
                            <input type="text" name="position" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3 employee-field-add" style="display:none;">
                            <label class="form-label">Lương</label>
                            <input type="number" name="salary" class="form-control" step="100000">
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
<div class="modal fade" id="editAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Cập nhật tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/accounts/update" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select name="role" id="edit_role" class="form-select" required onchange="toggleEmployeeFields(this, 'edit')">
                                <option value="customer">Khách hàng</option>
                                <option value="employee">Nhân viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row employee-field-edit" style="display:none;">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chức vụ</label>
                            <input type="text" name="position" id="edit_position" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lương</label>
                            <input type="number" name="salary" id="edit_salary" class="form-control" step="100000">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu mới <small class="text-muted">(Để trống nếu không đổi)</small></label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                <label class="form-check-label" for="edit_is_active">Hoạt động</label>
                            </div>
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
const currentUserId = <?php echo $current_user_id; ?>;

function toggleEmployeeFields(select, type) {
    const fields = document.querySelectorAll('.employee-field-' + type);
    const isEmployee = select.value === 'employee';
    
    fields.forEach(field => {
        field.style.display = isEmployee ? '' : 'none';
        const inputs = field.querySelectorAll('input');
        inputs.forEach(input => {
            input.disabled = !isEmployee;
            if (!isEmployee) input.value = ''; // Clear value if not employee
            input.required = isEmployee; // Make required if employee
        });
    });
}

function editAccount(account) {
    document.getElementById('edit_id').value = account.id;
    document.getElementById('edit_name').value = account.name;
    document.getElementById('edit_email').value = account.email;
    document.getElementById('edit_phone').value = account.phone || '';
    document.getElementById('edit_address').value = account.address || '';
    
    const roleSelect = document.getElementById('edit_role');
    roleSelect.value = account.role;
    
    // Disable role change if editing self
    if (account.id == currentUserId) {
        roleSelect.disabled = true;
        // Add hidden input to submit role since disabled select won't submit
        if (!document.getElementById('hidden_role')) {
            const hiddenRole = document.createElement('input');
            hiddenRole.type = 'hidden';
            hiddenRole.name = 'role';
            hiddenRole.id = 'hidden_role';
            hiddenRole.value = account.role;
            roleSelect.parentNode.appendChild(hiddenRole);
        } else {
            document.getElementById('hidden_role').value = account.role;
        }
    } else {
        roleSelect.disabled = false;
        const hiddenRole = document.getElementById('hidden_role');
        if (hiddenRole) hiddenRole.remove();
    }

    document.getElementById('edit_position').value = account.position || '';
    document.getElementById('edit_salary').value = account.salary || '';
    document.getElementById('edit_is_active').checked = account.is_active == 1;
    
    // Disable active toggle if editing self
    document.getElementById('edit_is_active').disabled = (account.id == currentUserId);

    toggleEmployeeFields(roleSelect, 'edit');
    
    new bootstrap.Modal(document.getElementById('editAccountModal')).show();
}

// Initialize fields on load for add modal
document.addEventListener('DOMContentLoaded', function() {
    const addRoleSelect = document.querySelector('select[name="role"]');
    if(addRoleSelect) toggleEmployeeFields(addRoleSelect, 'add');
});
</script>
