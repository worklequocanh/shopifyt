<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles($pdo, ['admin']);

// XỬ LÝ THÊM VOUCHER
if (isset($_POST['add_voucher'])) {
    // $code1 = ctype_upper($_POST['code']);
    $code = ctype_upper($pdo->real_escape_string($_POST['code']));
    $discount_type = $pdo->real_escape_string($_POST['discount_type']); // percentage hoặc fixed
    $discount_value = floatval($_POST['discount_value']);
    $min_order_value = floatval($_POST['min_order_value'] ?? 0);
    $max_discount = floatval($_POST['max_discount'] ?? 0);
    $usage_limit = intval($_POST['usage_limit'] ?? 0);
    $start_date = $pdo->real_escape_string($_POST['start_date']);
    $end_date = $pdo->real_escape_string($_POST['end_date']);
    $description = $pdo->real_escape_string($_POST['description'] ?? '');

    // Kiểm tra mã voucher đã tồn tại
    $check = $pdo->query("SELECT id FROM vouchers WHERE code='$code'");
    if ($check && $check->num_rows > 0) {
        $_SESSION['error_message'] = 'Mã voucher đã tồn tại!';
    } else {
        $sql = "INSERT INTO vouchers (code, discount_type, discount_value, min_order_value, 
                max_discount, usage_limit, start_date, end_date, description) 
                VALUES ('$code', '$discount_type', $discount_value, $min_order_value, 
                $max_discount, $usage_limit, '$start_date', '$end_date', '$description')";

        if ($pdo->query($sql)) {
            $_SESSION['success_message'] = 'Thêm voucher thành công!';
        } else {
            $_SESSION['error_message'] = 'Lỗi: ' . $pdo->error;
        }
    }
    header("Location: vouchers.php");
    exit;
}

// XỬ LÝ CẬP NHẬT VOUCHER
if (isset($_POST['update_voucher'])) {
    $id = intval($_POST['id']);
    $code = strtoupper($pdo->real_escape_string($_POST['code']));
    $discount_type = $pdo->real_escape_string($_POST['discount_type']);
    $discount_value = floatval($_POST['discount_value']);
    $min_order_value = floatval($_POST['min_order_value'] ?? 0);
    $max_discount = floatval($_POST['max_discount'] ?? 0);
    $usage_limit = intval($_POST['usage_limit'] ?? 0);
    $start_date = $pdo->real_escape_string($_POST['start_date']);
    $end_date = $pdo->real_escape_string($_POST['end_date']);
    $description = $pdo->real_escape_string($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Kiểm tra mã trùng (trừ chính nó)
    $check = $pdo->query("SELECT id FROM vouchers WHERE code='$code' AND id != $id");
    if ($check && $check->num_rows > 0) {
        $_SESSION['error_message'] = 'Mã voucher đã tồn tại!';
    } else {
        $sql = "UPDATE vouchers SET 
                code='$code', discount_type='$discount_type', discount_value=$discount_value,
                min_order_value=$min_order_value, max_discount=$max_discount,
                usage_limit=$usage_limit, start_date='$start_date', end_date='$end_date',
                description='$description', is_active=$is_active
                WHERE id=$id";

        if ($pdo->query($sql)) {
            $_SESSION['success_message'] = 'Cập nhật voucher thành công!';
        } else {
            $_SESSION['error_message'] = 'Lỗi: ' . $pdo->error;
        }
    }
    header("Location: vouchers.php");
    exit;
}

// XỬ LÝ XÓA VOUCHER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Kiểm tra voucher đã được sử dụng chưa
    $check_usage = $pdo->query("SELECT id FROM orders WHERE voucher_id=$id");
    if ($check_usage && $check_usage->num_rows > 0) {
        $_SESSION['error_message'] = 'Không thể xóa! Voucher đã được sử dụng trong ' . $check_usage->num_rows . ' đơn hàng.';
    } else {
        if ($pdo->query("DELETE FROM vouchers WHERE id=$id")) {
            $_SESSION['success_message'] = 'Xóa voucher thành công!';
        } else {
            $_SESSION['error_message'] = 'Lỗi xóa voucher!';
        }
    }
    header("Location: vouchers.php");
    exit;
}

// LẤY DANH SÁCH VOUCHER
$search = $_GET['search'] ?? '';
$filter_status = $_GET['filter_status'] ?? 'all';

$sql = "SELECT v.*, 
        (SELECT COUNT(*) FROM orders WHERE voucher_id = v.id) as usage_count
        FROM vouchers v WHERE 1=1";

if (!empty($search)) {
    $search_term = $pdo->real_escape_string($search);
    $sql .= " AND (v.code LIKE '%$search_term%' OR v.description LIKE '%$search_term%')";
}

if ($filter_status === 'active') {
    $sql .= " AND v.is_active = 1 AND v.end_date >= CURDATE()";
} elseif ($filter_status === 'expired') {
    $sql .= " AND v.end_date < CURDATE()";
} elseif ($filter_status === 'inactive') {
    $sql .= " AND v.is_active = 0";
}

$sql .= " ORDER BY v.created_at DESC";
$vouchers_result = $pdo->query($sql);

// LẤY THÔNG TIN CHO MODAL EDIT
$edit_voucher = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $pdo->query("SELECT * FROM vouchers WHERE id=$edit_id");
    if ($edit_result && $edit_result->num_rows > 0) {
        $edit_voucher = $edit_result->fetch_assoc();
    }
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
    <title>Quản lý Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .voucher-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .voucher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .voucher-active {
            border-color: #28a745;
        }

        .voucher-expired {
            border-color: #dc3545;
        }

        .voucher-inactive {
            border-color: #6c757d;
        }

        .voucher-code {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-ticket-perforated"></i> Quản lý Voucher</h2>
                <small class="text-muted">Tổng số: <?= $vouchers_result->num_rows ?> voucher</small>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
                    <i class="bi bi-plus-circle"></i> Tạo voucher
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
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-funnel"></i> Trạng thái</label>
                        <select name="filter_status" class="form-select">
                            <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Tất cả</option>
                            <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="expired" <?= $filter_status == 'expired' ? 'selected' : '' ?>>Đã hết hạn</option>
                            <option value="inactive" <?= $filter_status == 'inactive' ? 'selected' : '' ?>>Ngưng hoạt động</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-search"></i> Tìm kiếm</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Mã voucher hoặc mô tả..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách Voucher dạng Card -->
        <div class="row g-4">
            <?php if ($vouchers_result && $vouchers_result->num_rows > 0): ?>
                <?php while ($voucher = $vouchers_result->fetch_assoc()):
                    $is_expired = strtotime($voucher['end_date']) < time();
                    $is_active = $voucher['is_active'] && !$is_expired;
                    $status_class = $is_active ? 'voucher-active' : ($is_expired ? 'voucher-expired' : 'voucher-inactive');
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card voucher-card <?= $status_class ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="voucher-code"><?= htmlspecialchars($voucher['code']) ?></div>
                                    <span class="badge <?= $is_active ? 'bg-success' : ($is_expired ? 'bg-danger' : 'bg-secondary') ?>">
                                        <?= $is_active ? 'Hoạt động' : ($is_expired ? 'Hết hạn' : 'Tạm ngưng') ?>
                                    </span>
                                </div>

                                <p class="text-muted mb-3"><?= htmlspecialchars($voucher['description']) ?></p>

                                <div class="mb-2">
                                    <i class="bi bi-tag-fill text-primary"></i>
                                    <strong>Giảm giá:</strong>
                                    <?php if ($voucher['discount_type'] == 'percentage'): ?>
                                        <span class="text-danger"><?= $voucher['discount_value'] ?>%</span>
                                        <?php if ($voucher['max_discount'] > 0): ?>
                                            (Tối đa <?= number_format($voucher['max_discount']) ?>₫)
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-danger"><?= number_format($voucher['discount_value']) ?>₫</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($voucher['min_order_value'] > 0): ?>
                                    <div class="mb-2">
                                        <i class="bi bi-cart-check text-success"></i>
                                        <strong>Đơn tối thiểu:</strong> <?= number_format($voucher['min_order_value']) ?>₫
                                    </div>
                                <?php endif; ?>

                                <div class="mb-2">
                                    <i class="bi bi-calendar-range text-info"></i>
                                    <strong>Thời gian:</strong><br>
                                    <small><?= date('d/m/Y', strtotime($voucher['start_date'])) ?> - <?= date('d/m/Y', strtotime($voucher['end_date'])) ?></small>
                                </div>

                                <div class="mb-3">
                                    <i class="bi bi-people text-warning"></i>
                                    <strong>Đã dùng:</strong>
                                    <?= $voucher['usage_count'] ?>
                                    <?php if ($voucher['usage_limit'] > 0): ?>
                                        / <?= $voucher['usage_limit'] ?> lượt
                                    <?php else: ?>
                                        / ∞
                                    <?php endif; ?>
                                </div>

                                <div class="btn-group w-100" role="group">
                                    <a href="vouchers.php?edit=<?= $voucher['id'] ?>"
                                        class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="vouchers.php?delete=<?= $voucher['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Xóa voucher này?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mb-0 mt-2">Chưa có voucher nào</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Thêm Voucher -->
    <div class="modal fade" id="addVoucherModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tạo Voucher mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã Voucher <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control"
                                    placeholder="VD: SUMMER2024" required
                                    style="text-transform: uppercase;">
                                <small class="text-muted">Sẽ tự động chuyển thành chữ in hoa</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                <select name="discount_type" class="form-select" required onchange="toggleDiscountFields(this)">
                                    <option value="percentage">Phần trăm (%)</option>
                                    <option value="fixed">Số tiền cố định (₫)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="discount_value" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3" id="max_discount_field">
                                <label class="form-label">Giảm tối đa (cho %)</label>
                                <input type="number" step="1000" name="max_discount" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá trị đơn tối thiểu</label>
                                <input type="number" step="1000" name="min_order_value" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giới hạn số lần dùng</label>
                                <input type="number" name="usage_limit" class="form-control" value="0">
                                <small class="text-muted">0 = Không giới hạn</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Mô tả chi tiết về voucher..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Hủy
                        </button>
                        <button type="submit" name="add_voucher" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Tạo Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Voucher -->
    <?php if ($edit_voucher): ?>
        <div class="modal fade show" id="editVoucherModal" tabindex="-1" style="display:block;" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Sửa Voucher: <?= htmlspecialchars($edit_voucher['code']) ?></h5>
                        <a href="vouchers.php" class="btn-close"></a>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $edit_voucher['id'] ?>">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã Voucher <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control"
                                        value="<?= htmlspecialchars($edit_voucher['code']) ?>" required
                                        style="text-transform: uppercase;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                    <select name="discount_type" class="form-select" required onchange="toggleDiscountFields(this)">
                                        <option value="percentage" <?= $edit_voucher['discount_type'] == 'percentage' ? 'selected' : '' ?>>Phần trăm (%)</option>
                                        <option value="fixed" <?= $edit_voucher['discount_type'] == 'fixed' ? 'selected' : '' ?>>Số tiền cố định (₫)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="discount_value" class="form-control"
                                        value="<?= $edit_voucher['discount_value'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3" id="max_discount_field_edit">
                                    <label class="form-label">Giảm tối đa (cho %)</label>
                                    <input type="number" step="1000" name="max_discount" class="form-control"
                                        value="<?= $edit_voucher['max_discount'] ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá trị đơn tối thiểu</label>
                                    <input type="number" step="1000" name="min_order_value" class="form-control"
                                        value="<?= $edit_voucher['min_order_value'] ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giới hạn số lần dùng</label>
                                    <input type="number" name="usage_limit" class="form-control"
                                        value="<?= $edit_voucher['usage_limit'] ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="<?= $edit_voucher['start_date'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="<?= $edit_voucher['end_date'] ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_voucher['description']) ?></textarea>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                    id="is_active" <?= $edit_voucher['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt voucher
                                </label>
                            </div>

                            <div class="alert alert-info mt-3">
                                <strong>Thống kê:</strong> Voucher đã được sử dụng
                                <?php
                                $usage_count = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE voucher_id={$edit_voucher['id']}")->fetch_assoc()['count'];
                                echo $usage_count;
                                ?> lần
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="vouchers.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Hủy
                            </a>
                            <button type="submit" name="update_voucher" class="btn btn-warning">
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
        // Toggle hiển thị trường max_discount
        function toggleDiscountFields(select) {
            const maxDiscountField = document.getElementById('max_discount_field') || document.getElementById('max_discount_field_edit');
            if (maxDiscountField) {
                maxDiscountField.style.display = select.value === 'percentage' ? '' : 'none';
            }
        }

        // Auto dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);

        // Xử lý modal edit
        <?php if ($edit_voucher): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const editModal = document.getElementById('editVoucherModal');
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