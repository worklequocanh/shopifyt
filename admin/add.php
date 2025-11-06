<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

restrictToRoles(['admin']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;

// Lấy danh sách categories
$categories_result = $pdo->query("SELECT * FROM categories ORDER BY name ASC");

// THÊM SẢN PHẨM
if (isset($_POST['add'])) {
    $name = $_POST['name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $description = $_POST['description'] ?? '';
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);

    $sql = "INSERT INTO products (name, price, description, stock, category_id) 
        VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $price, $description, $stock, $category_id])) {
        $product_id = $pdo->lastInsertId();  // ✅ PDO

        // Lưu ảnh nếu có
        if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
            $upload_result = uploadImage($_FILES['img']);

            if ($upload_result['success']) {
                $img_path = $upload_result['web_path'];
                $img_sql = "INSERT INTO product_images (product_id, image_url, is_main) 
                                VALUES (?, ?, 1)";
                $stmt = $pdo->prepare($img_sql);
                $stmt->execute([$product_id, $img_path]);;
                $_SESSION['success_message'] = 'Thêm sản phẩm thành công!';
            } else {
                $_SESSION['error_message'] = $upload_result['message'];
            }
        } else {
            $_SESSION['success_message'] = 'Thêm sản phẩm thành công (chưa có ảnh).';
        }
    } else {
        $_SESSION['error_message'] = 'Lỗi thêm sản phẩm: ' . $pdo->error;
    }

    header("Location: /admin/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Thêm sản phẩm</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h3><i class="bi bi-plus-circle"></i> Thêm sản phẩm mới</h3>
            </div>
            <div class="card-body">
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Nhập tên sản phẩm" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Giá <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1000" class="form-control" name="price" placeholder="0" required>
                                <span class="input-group-text">₫</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number" class="form-control" name="stock" value="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php while ($cat = $categories_result->fetch()): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh sản phẩm</label>
                            <input type="file" class="form-control" name="img" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted">JPG, PNG, GIF, WEBP (Max 5MB)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả sản phẩm</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Nhập mô tả..."></textarea>
                    </div>

                    <div id="imagePreview" class="mb-3"></div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="add" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '200px';
                    img.className = 'rounded border';
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>