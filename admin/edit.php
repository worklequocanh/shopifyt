<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles(['admin', 'employee']);

$role = $_SESSION['role'];
$account_id = $_SESSION['id'];

// Lấy ID đơn hàng
$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    $_SESSION['error_message'] = 'Đơn hàng không hợp lệ!';
    header("Location: index.php");
    exit;
}

// ✅ Lấy thông tin đơn hàng
try {
    $order_stmt = $pdo->prepare("SELECT o.*, a.name as customer_name, a.email, a.phone, a.address 
                                  FROM orders o 
                                  LEFT JOIN accounts a ON o.account_id = a.id 
                                  WHERE o.id = ?");
    $order_stmt->execute([$order_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['error_message'] = 'Đơn hàng không tồn tại!';
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching order: " . $e->getMessage());
    $_SESSION['error_message'] = 'Lỗi tải thông tin đơn hàng!';
    header("Location: index.php");
    exit;
}

// ✅ Lấy chi tiết đơn hàng
try {
    $details_stmt = $pdo->prepare("SELECT od.*, p.name as product_name, p.price, p.stock 
                                    FROM order_details od 
                                    JOIN products p ON od.product_id = p.id 
                                    WHERE od.order_id = ?");
    $details_stmt->execute([$order_id]);
} catch (PDOException $e) {
    error_log("Error fetching order details: " . $e->getMessage());
    $details_stmt = null;
}

// ✅ Xử lý cập nhật trạng thái
if (isset($_POST['update_status'])) {
    $new_status = $_POST['update_status'];

    try {
        $update_stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");

        if ($update_stmt->execute([$new_status, $order_id])) {
            // Lấy lại chi tiết đơn hàng
            $details_stmt->execute([$order_id]);
            $details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Giảm tồn kho khi chuyển sang shipped/completed
            if (($new_status == 'accepted') && $order['status'] == 'pending') {
                $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                foreach ($details as $detail) {
                    $stock_stmt->execute([$detail['quantity'], $detail['product_id']]);
                }
            }

            // Hoàn lại tồn kho khi hủy đơn đã shipped/completed
            if ($new_status == 'cancelled' && in_array($order['status'], ['shipped', 'accepted'])) {
                $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                foreach ($details as $detail) {
                    $stock_stmt->execute([$detail['quantity'], $detail['product_id']]);
                }
            }

            $_SESSION['success_message'] = 'Cập nhật trạng thái đơn hàng thành công!';
            header("Location: edit.php?id=$order_id");
            exit;
        } else {
            $_SESSION['error_message'] = 'Lỗi cập nhật!';
        }
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi cập nhật trạng thái!';
    }
}

// ✅ Xử lý xóa đơn hàng
if (isset($_POST['delete_order'])) {
    try {
        // Hoàn lại tồn kho nếu đơn đã shipped/completed
        if (in_array($order['status'], ['shipped', 'accepted'])) {
            $details_stmt->execute([$order_id]);
            $details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

            $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($details as $detail) {
                $stock_stmt->execute([$detail['quantity'], $detail['product_id']]);
            }
        }

        // Xóa đơn hàng
        $delete_stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        if ($delete_stmt->execute([$order_id])) {
            $_SESSION['success_message'] = 'Xóa đơn hàng thành công!';
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error_message'] = 'Lỗi xóa đơn hàng!';
        }
    } catch (PDOException $e) {
        error_log("Error deleting order: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi xóa đơn hàng!';
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
    <title>Xử lý đơn hàng #<?= $order_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .order-status {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .status-badge {
            font-size: 1.1rem;
            padding: 10px 20px;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-cart-check-fill"></i> Xử lý đơn hàng #<?= $order_id ?></h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
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

        <div class="row">
            <!-- Thông tin đơn hàng -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="info-label">Mã đơn hàng:</p>
                                <p class="fs-5">#<?= $order['id'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="info-label">Trạng thái:</p>
                                <span class="badge status-badge bg-<?=
                                                                    match ($order['status']) {
                                                                        'pending' => 'warning',
                                                                        'accepted' => 'success',
                                                                        'cancelled' => 'danger',
                                                                        default => 'secondary'
                                                                    }
                                                                    ?>">
                                    <?= match ($order['status']) {
                                        'pending' => 'Chờ xử lý',
                                        'accepted' => 'Đã chấp nhận',
                                        'cancelled' => 'Đã hủy',
                                        default => $order['status']
                                    } ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="info-label">Ngày đặt:</p>
                                <p><?= date('d/m/Y H:i:s', strtotime($order['order_date'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="info-label">Tổng tiền:</p>
                                <p class="text-success fs-4 fw-bold"><?= number_format($order['total_amount']) ?>đ</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chi tiết sản phẩm -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Chi tiết sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th>Số lượng</th>
                                        <th>Tồn kho</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($details_stmt):
                                        $details_stmt->execute([$order_id]);
                                        $details_list = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

                                        if (!empty($details_list)):
                                            foreach ($details_list as $detail):
                                    ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($detail['product_name']) ?></td>
                                                    <td><?= number_format($detail['unit_price']) ?>đ</td>
                                                    <td><span class="badge bg-primary"><?= $detail['quantity'] ?></span></td>
                                                    <td>
                                                        <span class="badge bg-<?= $detail['stock'] > 0 ? 'success' : 'danger' ?>">
                                                            <?= $detail['stock'] ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold"><?= number_format($detail['subtotal']) ?>đ</td>
                                                </tr>
                                            <?php
                                            endforeach;
                                        else:
                                            ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Không có sản phẩm</td>
                                            </tr>
                                    <?php
                                        endif;
                                    endif;
                                    ?>
                                </tbody>
                                <tfoot class="table-success">
                                    <tr>
                                        <th colspan="4" class="text-end">Tổng cộng:</th>
                                        <th><?= number_format($order['total_amount']) ?>đ</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin khách hàng & Hành động -->
            <div class="col-lg-4">
                <!-- Thông tin khách hàng -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Thông tin khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <p class="info-label">Tên khách hàng:</p>
                        <p class="fw-bold"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>

                        <p class="info-label">Email:</p>
                        <p><?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>

                        <p class="info-label">Số điện thoại:</p>
                        <p><?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>

                        <p class="info-label">Địa chỉ giao hàng:</p>
                        <p><?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <!-- Cập nhật trạng thái -->
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Cập nhật trạng thái</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái mới:</label>
                                <div class="btn-group w-100" role="group" aria-label="Trạng thái">
                                    <button
                                        name="update_status"
                                        <?php if ($order['status'] !== 'pending') echo htmlspecialchars("disabled") ?>
                                        type="submit"
                                        value="accepted"
                                        class="btn btn-success w-50">
                                        <i class="bi bi-check-circle"></i> Chấp nhận
                                    </button>
                                    <button
                                        name="update_status"
                                        <?php if ($order['status'] !== 'pending') echo htmlspecialchars("disabled") ?>
                                        type="submit"
                                        value="cancelled"
                                        class=" btn btn-danger w-50">
                                        <i class="bi bi-x-circle"></i> Hủy
                                        </buttonname=>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Hướng dẫn trạng thái -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Hướng dẫn</h6>
                    </div>
                    <div class="card-body">
                        <small>
                            <ul class="mb-0">
                                <li><strong>Chờ xử lý:</strong> Đơn hàng mới</li>
                                <li><strong>Đã thanh toán:</strong> Đã xác nhận thanh toán</li>
                                <li><strong>Đang giao:</strong> Đang vận chuyển (tự động trừ kho)</li>
                                <li><strong>Hoàn thành:</strong> Giao thành công</li>
                                <li><strong>Hủy:</strong> Đơn bị hủy (hoàn kho)</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>

</html>