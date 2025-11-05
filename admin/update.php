<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles(['admin']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;

$id = intval($_GET['id'] ?? 1);
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID sản phẩm không hợp lệ!';
    header("Location: index.php");
    exit;
}

// ✅ Lấy thông tin sản phẩm bằng PDO
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error_message'] = 'Sản phẩm không tồn tại!';
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    $_SESSION['error_message'] = 'Lỗi truy vấn dữ liệu!';
    header("Location: index.php");
    exit;
}

// ✅ Lấy danh sách categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories_result = [];
}

// ✅ Lấy tất cả ảnh của sản phẩm
try {
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC");
    $stmt->execute([$id]);
    $product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching product images: " . $e->getMessage());
    $product_images = [];
}

// ✅ Xử lý cập nhật
if (isset($_POST['update'])) {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validate dữ liệu
    if (empty($name) || $price <= 0) {
        $_SESSION['error_message'] = 'Vui lòng điền đầy đủ thông tin!';
        header("Location: update.php?id=$id");
        exit;
    }

    try {
        // Cập nhật thông tin sản phẩm
        $sql_update = "UPDATE products 
                       SET name=?, price=?, description=?, stock=?, category_id=?, is_active=?, is_featured=?
                       WHERE id=?";
        $stmt_update = $pdo->prepare($sql_update);

        if ($stmt_update->execute([$name, $price, $description, $stock, $category_id, $is_active, $is_featured, $id])) {
            // Xử lý upload ảnh mới
            if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
                $upload_result = uploadImage($_FILES['img']);

                if ($upload_result['success']) {
                    $img_path = $upload_result['web_path'];

                    // Kiểm tra ảnh chính hiện tại
                    $check_img = $pdo->prepare("SELECT id, image_url FROM product_images WHERE product_id=? AND is_main=1");
                    $check_img->execute([$id]);
                    $old_img = $check_img->fetch(PDO::FETCH_ASSOC);

                    if ($old_img) {
                        // Xóa ảnh cũ
                        if (file_exists($old_img['image_url'])) {
                            @unlink($old_img['image_url']);
                        }

                        // Cập nhật ảnh mới
                        $stmt = $pdo->prepare("UPDATE product_images SET image_url=? WHERE id=?");
                        $stmt->execute([$img_path, $old_img['id']]);
                    } else {
                        // Thêm ảnh mới
                        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)");
                        $stmt->execute([$id, $img_path]);
                    }

                    $_SESSION['success_message'] = 'Cập nhật sản phẩm và ảnh thành công!';
                } else {
                    $_SESSION['error_message'] = $upload_result['message'];
                }
            } else {
                $_SESSION['success_message'] = 'Cập nhật sản phẩm thành công!';
            }
        } else {
            $errorInfo = $pdo->errorInfo();
            $_SESSION['error_message'] = 'Lỗi cập nhật sản phẩm: ' . ($errorInfo[2] ?? 'Không rõ nguyên nhân');
        }
    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi cập nhật sản phẩm!';
    }

    header("Location: update.php?id=$id");
    exit;
}

// ✅ Xử lý xóa ảnh
if (isset($_GET['delete_image'])) {
    $img_id = intval($_GET['delete_image']);

    try {
        // Lấy thông tin ảnh
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE id=? AND product_id=?");
        $stmt->execute([$img_id, $id]);
        $img_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($img_info) {
            // Không cho xóa ảnh chính nếu chỉ còn 1 ảnh
            if ($img_info['is_main'] && count($product_images) <= 1) {
                $_SESSION['error_message'] = 'Không thể xóa ảnh chính duy nhất!';
            } else {
                // Xóa file
                if (file_exists($img_info['image_url'])) {
                    @unlink($img_info['image_url']);
                }

                // Xóa record
                $stmt = $pdo->prepare("DELETE FROM product_images WHERE id=?");
                $stmt->execute([$img_id]);

                // Nếu xóa ảnh chính, đặt ảnh đầu tiên làm ảnh chính
                if ($img_info['is_main']) {
                    $stmt = $pdo->prepare("UPDATE product_images SET is_main=1 WHERE product_id=? ORDER BY id ASC LIMIT 1");
                    $stmt->execute([$id]);
                }

                $_SESSION['success_message'] = 'Xóa ảnh thành công!';
            }
        }
    } catch (PDOException $e) {
        error_log("Error deleting image: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi xóa ảnh!';
    }

    header("Location: update.php?id=$id");
    exit;
}

// ✅ Xử lý đặt ảnh chính
if (isset($_GET['set_main_image'])) {
    $img_id = intval($_GET['set_main_image']);

    try {
        // Reset tất cả ảnh về không phải ảnh chính
        $stmt = $pdo->prepare("UPDATE product_images SET is_main=0 WHERE product_id=?");
        $stmt->execute([$id]);

        // Set ảnh được chọn làm ảnh chính
        $stmt = $pdo->prepare("UPDATE product_images SET is_main=1 WHERE id=? AND product_id=?");
        $stmt->execute([$img_id, $id]);

        $_SESSION['success_message'] = 'Đã đặt làm ảnh chính!';
    } catch (PDOException $e) {
        error_log("Error setting main image: " . $e->getMessage());
        $_SESSION['error_message'] = 'Lỗi đặt ảnh chính!';
    }

    header("Location: update.php?id=$id");
    exit;
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sửa sản phẩm - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .image-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .image-item:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }

        .image-item.main-image {
            border-color: #28a745;
            border-width: 3px;
        }

        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .image-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .main-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-pencil-square"></i> Sửa sản phẩm
            </h2>
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
            <!-- Form sửa sản phẩm -->
            <div class="col-lg-8">
                <div class="card shadow-lg mb-4">
                    <div class="card-header text-white bg-primary">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i>
                            Thông tin sản phẩm: <?= htmlspecialchars($product['name']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">
                                        Tên sản phẩm <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="name"
                                        value="<?= htmlspecialchars($product['name']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Giá <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="1000" class="form-control" name="price"
                                            value="<?= $product['price'] ?>" required>
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Danh mục <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php foreach ($categories_result as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"
                                                <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Số lượng</label>
                                    <input type="number" class="form-control" name="stock"
                                        value="<?= $product['stock'] ?>" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">ID sản phẩm</label>
                                    <input type="text" class="form-control" value="#<?= $product['id'] ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-image"></i> Thêm ảnh mới
                                </label>
                                <input type="file" class="form-control" name="img" accept="image/*"
                                    onchange="previewImage(this)">
                                <small class="form-text text-muted">
                                    JPG, PNG, GIF, WEBP (Max 5MB)
                                </small>
                                <div id="imagePreview" class="mt-2"></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active"
                                            id="is_active" <?= $product['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-toggle-on"></i> Đang bán
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_featured"
                                            id="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_featured">
                                            <i class="bi bi-star"></i> Sản phẩm nổi bật
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" name="update" class="btn btn-success btn-lg">
                                    <i class="bi bi-check2-circle"></i> Cập nhật sản phẩm
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Thông tin bổ sung -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Thông tin bổ sung
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Ngày tạo:</strong><br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y H:i', strtotime($product['created_at'])) ?>
                                </small>
                            </li>
                            <?php if ($product['updated_at']): ?>
                                <li class="mb-2">
                                    <strong>Cập nhật lần cuối:</strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-clock-history"></i>
                                        <?= date('d/m/Y H:i', strtotime($product['updated_at'])) ?>
                                    </small>
                                </li>
                            <?php endif; ?>
                            <li class="mb-2">
                                <strong>Trạng thái:</strong><br>
                                <span class="badge <?= $product['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $product['is_active'] ? 'Đang bán' : 'Ngừng bán' ?>
                                </span>
                            </li>
                            <li class="mb-2">
                                <strong>Tồn kho:</strong><br>
                                <span class="badge <?= $product['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $product['stock'] ?> sản phẩm
                                </span>
                            </li>
                            <li>
                                <strong>Số ảnh:</strong><br>
                                <span class="badge bg-primary"><?= count($product_images) ?> ảnh</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Hướng dẫn -->
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-lightbulb"></i> Hướng dẫn
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>Click vào ảnh để xem chi tiết</li>
                            <li>Click <span class="badge bg-success">Chính</span> để đặt ảnh chính</li>
                            <li>Click <span class="badge bg-danger">Xóa</span> để xóa ảnh</li>
                            <li>Ảnh chính sẽ hiển thị đầu tiên</li>
                            <li>Nên dùng ảnh có kích thước 800x800px</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thư viện ảnh -->
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-images"></i>
                    Thư viện ảnh (<?= count($product_images) ?> ảnh)
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($product_images)): ?>
                    <div class="image-gallery">
                        <?php foreach ($product_images as $img): ?>
                            <div class="image-item <?= $img['is_main'] ? 'main-image' : '' ?>">
                                <?php if ($img['is_main']): ?>
                                    <span class="main-badge">
                                        <i class="bi bi-star-fill"></i> Chính
                                    </span>
                                <?php endif; ?>

                                <img src="<?= htmlspecialchars($img['image_url']) ?>"
                                    alt="Product Image"
                                    onclick="viewImage('<?= htmlspecialchars($img['image_url']) ?>')">

                                <div class="image-actions">
                                    <?php if (!$img['is_main']): ?>
                                        <a href="update.php?id=<?= $id ?>&set_main_image=<?= $img['id'] ?>"
                                            class="btn btn-sm btn-success"
                                            title="Đặt làm ảnh chính">
                                            <i class="bi bi-star"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="update.php?id=<?= $id ?>&delete_image=<?= $img['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Xóa ảnh này?')"
                                        title="Xóa ảnh">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2">Sản phẩm chưa có ảnh nào. Vui lòng thêm ảnh!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal xem ảnh -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xem ảnh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Product" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview ảnh mới
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'border rounded p-2 d-inline-block';
                    div.innerHTML = `
                        <img src="${e.target.result}" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 200px;">
                        <p class="text-center mb-0 mt-2">
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> Ảnh mới
                            </small>
                        </p>
                    `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Xem ảnh trong modal
        function viewImage(url) {
            document.getElementById('modalImage').src = url;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Auto dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>

</html>