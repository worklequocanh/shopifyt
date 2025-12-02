<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles($pdo, ['admin']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;

// Xử lý thêm tài khoản
if (isset($_POST['add_account'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = ($_POST['phone'] ?? '');
    $address = ($_POST['address'] ?? '');
    $acc_role = ($_POST['role']);
    $position = ($_POST['position'] ?? '');
    $salary = floatval($_POST['salary'] ?? 0);

    // Kiểm tra email đã tồn tại
    $check_email = $pdo->prepare("SELECT id FROM accounts WHERE email=?");
    $check_email->execute([$email]);
    if ($check_email->fetch()) {
        $_SESSION['error_message'] = 'Email đã tồn tại!';
    } elseif (!empty($phone) && !preg_match("/^[0-9]{10,11}$/", $phone)) {
        $_SESSION['error_message'] = "Số điện thoại không hợp lệ";
    } else {
        // Nên hash password trong thực tế
        // $password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO accounts (name, email, password, phone, address, role, position, salary) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $email, $hashed_password, $phone, $address, $acc_role, $position, $salary])) {
            $_SESSION['success_message'] = 'Thêm tài khoản thành công!';
        } else {
            $_SESSION['error_message'] = 'Lỗi: ' . $pdo->error;
        }
    }
    header("Location: accounts.php");
    exit;
}

// Xử lý cập nhật tài khoản
if (isset($_POST['update_account'])) {
    $acc_id = intval($_POST['id']);
    $name = ($_POST['name']);
    $email = ($_POST['email']);
    $phone = ($_POST['phone'] ?? '');
    $address = ($_POST['address'] ?? '');
    $acc_role = ($_POST['role']);
    $position = ($_POST['position'] ?? '');
    $salary = floatval($_POST['salary'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $sql_role_old = "select role from accounts where id=?";
    $stmt_role_old = $pdo->prepare($sql_role_old);
    $stmt_role_old->execute([$acc_id]);
    $role_old = $stmt_role_old->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra email trùng (trừ chính nó)
    $check_email = $pdo->prepare("SELECT id FROM accounts WHERE email=? AND id != ?");
    $check_email->execute([$email, $acc_id]);
    if ($check_email->fetch()) {
        $_SESSION['error_message'] = 'Lỗi: email đã tồn tại';
    } else {
        // Kiem tra co luong moi cap nhat
        if ($acc_role === "employee") {
            if (empty($salary)) {
                $_SESSION['error_message'] = 'Lỗi: Vui lòng nhập lương';
            } else {
                $sql = "UPDATE accounts SET 
                name=?, email=?, phone=?, address=?, role=?, position=?, salary=?, is_active=?
                WHERE id=?";

                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$name, $email, $phone, $address, $acc_role, $position, $salary, $is_active, $acc_id])) {
                    // Cập nhật mật khẩu nếu có
                    if (!empty($_POST['new_password'])) {
                        $new_password = $_POST['new_password'];
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE accounts SET password=? WHERE id=?");
                        $stmt->execute([$hashed_password, $acc_id]);
                    }

                    $_SESSION['success_message'] = 'Cập nhật tài khoản thành công!';
                } else {
                    $_SESSION['error_message'] = 'Lỗi: ' . $pdo->error;
                }
            }
        } else {
            $sql = "UPDATE accounts SET 
                name=?, email=?, phone=?, address=?, role=?, salary=NULL, position=?, is_active=?
                WHERE id=?";

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$name, $email, $phone, $address, $acc_role, $position, $is_active, $acc_id])) {
                // Cập nhật mật khẩu nếu có
                if (!empty($_POST['new_password'])) {
                    $new_password = $_POST['new_password'];
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE accounts SET password=? WHERE id=?");
                    $stmt->execute([$hashed_password, $acc_id]);
                }

                $_SESSION['success_message'] = 'Cập nhật tài khoản thành công!';
            } else {
                $_SESSION['error_message'] = 'Lỗi: ' . $pdo->error;
            }
        }
    }
    header("Location: accounts.php");
    exit;
}

// Xử lý xóa tài khoản
if (isset($_GET['delete'])) {
    $acc_id = intval($_GET['delete']);

    // Không cho phép xóa chính mình
    if ($acc_id == $account_id) {
        $_SESSION['error_message'] = 'Không thể xóa tài khoản của chính mình!';
    } else {
        $stmt = $pdo->prepare("DELETE FROM accounts WHERE id=?");
        if ($stmt->execute([$acc_id])) {
            $_SESSION['success_message'] = 'Xóa tài khoản thành công!';
        } else {
            $_SESSION['error_message'] = 'Lỗi xóa tài khoản!';
        }
    }
    header("Location: accounts.php");
    exit;
}

// Xử lý khóa/mở khóa tài khoản
if (isset($_GET['toggle_active'])) {
    $acc_id = intval($_GET['toggle_active']);

    if ($acc_id == $account_id) {
        $_SESSION['error_message'] = 'Không thể khóa tài khoản của chính mình!';
    } else {
        $stmt = $pdo->prepare("SELECT is_active FROM accounts WHERE id=?");
        $stmt->execute([$acc_id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_status = $current['is_active'] ? 0 : 1;

        $stmt = $pdo->prepare("UPDATE accounts SET is_active=? WHERE id=?");
        if ($stmt->execute([$new_status, $acc_id])) {
            $_SESSION['success_message'] = $new_status ? 'Đã mở khóa tài khoản!' : 'Đã khóa tài khoản!';
        }
    }
    header("Location: accounts.php");
    exit;
}

// Lấy danh sách tài khoản
$filter_role = $_GET['filter_role'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM accounts WHERE 1=1";
$params = [];

if ($filter_role !== 'all') {
    $sql .= " AND role=?";
    $params[] = $filter_role;
}
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accounts_result = $stmt;

// Lấy thông tin cho modal edit
$edit_account = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_account = $stmt->fetch(PDO::FETCH_ASSOC);
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .badge-role {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-people-fill"></i> Quản lý tài khoản</h2>
                <small class="text-muted">Tổng số: <?= $accounts_result->rowCount() ?> tài khoản</small>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-person-plus"></i> Thêm tài khoản
                </button>
            </div>
        </div>

        <!-- Thông báo -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Bộ lọc -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-funnel"></i> Vai trò</label>
                        <select name="filter_role" class="form-select">
                            <option value="all" <?= $filter_role == 'all' ? 'selected' : '' ?>>Tất cả</option>
                            <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="employee" <?= $filter_role == 'employee' ? 'selected' : '' ?>>Nhân viên</option>
                            <option value="customer" <?= $filter_role == 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-search"></i> Tìm kiếm</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Tên hoặc email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng tài khoản -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="15%">Tên</th>
                                <th width="15%">Email</th>
                                <th width="10%">SĐT</th>
                                <th width="10%">Vai trò</th>
                                <th width="12%">Chức vụ</th>
                                <th width="10%">Lương</th>
                                <th width="8%">Trạng thái</th>
                                <th width="10%">Ngày tạo</th>
                                <th width="5%">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $accounts_list = $accounts_result->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($accounts_list)): ?>
                                <?php foreach ($accounts_list as $acc): ?>
                                    <tr class="<?= !$acc['is_active'] ? 'table-secondary' : '' ?>">
                                        <td><?= $acc['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($acc['name']) ?></strong>
                                            <?php if ($acc['id'] == $account_id): ?>
                                                <span class="badge bg-info badge-sm ms-1">Bạn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($acc['email']) ?></td>
                                        <td><?= htmlspecialchars($acc['phone'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge badge-role bg-<?=
                                                                                $acc['role'] == 'admin' ? 'danger' : ($acc['role'] == 'employee' ? 'warning' : 'info')
                                                                                ?>">
                                                <?= ucfirst($acc['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($acc['position'] ?? '-') ?></td>
                                        <?php if ($acc['role'] == 'employee'): ?>
                                            <td> <?= number_format($acc['salary'] ?? 0) ?>đ</td>
                                        <?php else: ?>
                                            <td>NULL</td>
                                        <?php endif; ?>

                                        <td>
                                            <span class="badge <?= $acc['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $acc['is_active'] ? 'Hoạt động' : 'Bị khóa' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($acc['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="accounts.php?edit=<?= $acc['id'] ?>"
                                                    class="btn btn-warning"
                                                    title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($acc['id'] != $account_id): ?>
                                                    <a href="accounts.php?toggle_active=<?= $acc['id'] ?>"
                                                        class="btn btn-<?= $acc['is_active'] ? 'secondary' : 'success' ?>"
                                                        onclick="return confirm('<?= $acc['is_active'] ? 'Khóa' : 'Mở khóa' ?> tài khoản này?')"
                                                        title="<?= $acc['is_active'] ? 'Khóa' : 'Mở khóa' ?>">
                                                        <i class="bi bi-<?= $acc['is_active'] ? 'lock' : 'unlock' ?>"></i>
                                                    </a>
                                                    <a href="accounts.php?delete=<?= $acc['id'] ?>"
                                                        class="btn btn-danger"
                                                        onclick="return confirm('Xóa tài khoản này?')"
                                                        title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Không tìm thấy tài khoản nào</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm tài khoản -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Thêm tài khoản mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
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
                                <input type="text" name="phone" class="form-control" placeholder="0901234567">
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
                                <input type="text" name="position" class="form-control" placeholder="VD: Nhân viên bán hàng">
                            </div>
                            <div class="col-md-4 mb-3 employee-field-add" style="display:none;">
                                <label class="form-label">Lương</label>
                                <input type="number" name="salary" class="form-control" step="100000" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Hủy
                        </button>
                        <button type="submit" name="add_account" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Thêm tài khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa tài khoản -->
    <?php if ($edit_account): ?>
        <div class="modal fade show" id="editModal" tabindex="-1" style="display:block;" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Sửa tài khoản: <?= htmlspecialchars($edit_account['name']) ?></h5>
                        <a href="accounts.php" class="btn-close"></a>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $edit_account['id'] ?>">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= htmlspecialchars($edit_account['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?= htmlspecialchars($edit_account['email']) ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="<?= htmlspecialchars($edit_account['phone'] ?? '') ?>">
                                </div>
                                <?php if ($edit_account['id'] != $account_id): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                        <select name="role" class="form-select" required onchange="toggleEmployeeFields(this, 'edit')">
                                            <option value="customer" <?= $edit_account['role'] == 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                                            <option value="employee" <?= $edit_account['role'] == 'employee' ? 'selected' : '' ?>>Nhân viên</option>
                                            <option value="admin" <?= $edit_account['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($edit_account['address'] ?? '') ?></textarea>
                            </div>
                            <?php if ($edit_account['id'] != $account_id): ?>
                                <div class="row employee-field-edit" style="<?= in_array($edit_account['role'], ['admin', 'employee']) ? '' : 'display:none;' ?>">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Chức vụ</label>
                                        <input type="text" name="position" class="form-control"
                                            value="<?= htmlspecialchars($edit_account['position'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Lương</label>
                                        <input type="number" name="salary" class="form-control"
                                            value="<?= $edit_account['salary'] ?? 0 ?>" step="100000">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới <small class="text-muted">(Để trống nếu không đổi)</small></label>
                                    <input type="password" name="new_password" class="form-control"
                                        placeholder="Nhập mật khẩu mới...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái tài khoản</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                            id="is_active" <?= $edit_account['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            <span class="badge <?= $edit_account['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $edit_account['is_active'] ? 'Đang hoạt động' : 'Đã khóa' ?>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Thông tin bổ sung:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Ngày tạo: <?= date('d/m/Y H:i', strtotime($edit_account['created_at'])) ?></li>
                                    <?php if ($edit_account['updated_at']): ?>
                                        <li>Cập nhật lần cuối: <?= date('d/m/Y H:i', strtotime($edit_account['updated_at'])) ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="accounts.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Hủy
                            </a>
                            <button type="submit" name="update_account" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle hiển thị fields cho nhân viên/admin
        function toggleEmployeeFields(select, formType) {
            const fields = document.querySelectorAll('.employee-field-' + formType);
            if (select.value === 'admin' || select.value === 'employee') {
                fields.forEach(field => field.style.display = '');
            } else {
                fields.forEach(field => field.style.display = 'none');
            }
        }

        // Auto dismiss alerts
        //setTimeout(() => {
        //    document.querySelectorAll('.alert').forEach(alert => {
        //        new bootstrap.Alert(alert).close();
        //    });
        //}, 5000);

        // Xử lý modal edit khi load trang
        <?php if ($edit_account): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const editModal = document.getElementById('editModal');
                if (editModal) {
                    editModal.classList.add('show');
                    editModal.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>