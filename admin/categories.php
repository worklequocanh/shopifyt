<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles(['admin']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;

// Xử lý thêm danh mục
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');

    // Kiểm tra tên danh mục đã tồn tại
    try {
        $check_categoryname = $pdo->prepare("SELECT id FROM categories WHERE name=?");
        $check_categoryname->execute([$name]);

        if ($check_categoryname->fetch()) {
            $_SESSION['error_message'] = 'Danh mục đã tồn tại!';
        } else {
            $sql = "INSERT INTO categories (name) VALUES (?)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$name])) {
                $_SESSION['success_message'] = 'Thêm danh mục thành công!';
            } else {
                $_SESSION['error_message'] = 'Lỗi: ' . $pdo->errorInfo()[2];
            }
        }
    } catch (PDOException $e) {
        error_log("Error adding category: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi thêm danh mục!';
    }

    header("Location: categories.php");
    exit;
}

// Xử lý cập nhật danh mục
if (isset($_POST['update_category'])) {
    $category_id = intval($_POST['id']);
    $name = trim($_POST['name'] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name=? AND id != ?");
        $stmt->execute([$name, $category_id]);

        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'Danh mục đã tồn tại!';
        } else {
            $sql = "UPDATE categories SET name=? WHERE id=?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$name, $category_id])) {
                $_SESSION['success_message'] = 'Cập nhật danh mục thành công!';
            } else {
                $_SESSION['error_message'] = 'Lỗi: ' . $pdo->errorInfo()[2];
            }
        }
    } catch (PDOException $e) {
        error_log("Error updating category: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi cập nhật danh mục!';
    }

    header("Location: categories.php");
    exit;
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);

    try {
        // Kiểm tra có sản phẩm nào trong danh mục không
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE category_id=?");
        $stmt->execute([$category_id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($count > 0) {
            $_SESSION['error_message'] = 'Không thể xóa! Còn ' . $count . ' sản phẩm đang nằm trong danh mục này.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
            if ($stmt->execute([$category_id])) {
                $_SESSION['success_message'] = 'Xóa danh mục thành công!';
            } else {
                $_SESSION['error_message'] = 'Lỗi xóa danh mục!';
            }
        }
    } catch (PDOException $e) {
        error_log("Error deleting category: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi xóa danh mục!';
    }

    header("Location: categories.php");
    exit;
}

// Lấy danh sách danh mục
$search = $_GET['search'] ?? '';

try {
    $sql = "SELECT * FROM categories WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (name LIKE ?)";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $category_result = $stmt;
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $category_result = null;
}

// Lấy thông tin cho modal edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
        $stmt->execute([$edit_id]);
        $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching category for edit: " . $e->getMessage());
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
    <title>Quản lý danh mục</title>
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
                <h2><i class="bi bi-grid-3x3-gap-fill"></i> Quản lý danh mục</h2>
                <small class="text-muted">Tổng số: <?= $category_result ? $category_result->rowCount() : 0 ?> danh mục</small>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle"></i> Thêm danh mục
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
                    <div class="col-md-10">
                        <label class="form-label"><i class="bi bi-search"></i> Tìm kiếm</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Tên danh mục..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng danh mục -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="20%">ID</th>
                                <th width="40%">Tên</th>
                                <th width="20%">Ngày tạo</th>
                                <th width="20%">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($category_result):
                                $categories_list = $category_result->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($categories_list)):
                                    foreach ($categories_list as $cate):
                                        // ✅ Đếm số sản phẩm trong danh mục bằng PDO
                                        try {
                                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE category_id=?");
                                            $stmt->execute([$cate['id']]);
                                            $product_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                        } catch (PDOException $e) {
                                            error_log("Error counting products: " . $e->getMessage());
                                            $product_count = 0;
                                        }
                            ?>
                                        <tr>
                                            <td><?= $cate['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($cate['name']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-box"></i> <?= $product_count ?> sản phẩm
                                                </small>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($cate['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="categories.php?edit=<?= $cate['id'] ?>"
                                                        class="btn btn-warning"
                                                        title="Sửa">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="categories.php?delete=<?= $cate['id'] ?>"
                                                        class="btn btn-danger"
                                                        onclick="return confirm('<?= $product_count > 0 ? "Danh mục này còn {$product_count} sản phẩm. Bạn có chắc muốn xóa?" : "Xóa danh mục này?" ?>')"
                                                        title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    endforeach;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mb-0 mt-2">Không tìm thấy danh mục nào</p>
                                        </td>
                                    </tr>
                                <?php
                                endif;
                            else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Lỗi tải dữ liệu</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm danh mục -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Thêm danh mục mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Hủy
                        </button>
                        <button type="submit" name="add_category" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Thêm danh mục
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa danh mục -->
    <?php if ($edit_category): ?>
        <div class="modal fade show" id="editCategoryModal" tabindex="-1" style="display:block;" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Sửa danh mục: <?= htmlspecialchars($edit_category['name']) ?></h5>
                        <a href="categories.php" class="btn-close"></a>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= htmlspecialchars($edit_category['name']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="categories.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Hủy
                            </a>
                            <button type="submit" name="update_category" class="btn btn-warning">
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
        // Auto dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);

        // Xử lý modal edit khi load trang
        <?php if ($edit_category): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const editCategoryModal = document.getElementById('editCategoryModal');
                if (editCategoryModal) {
                    editCategoryModal.classList.add('show');
                    editCategoryModal.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>