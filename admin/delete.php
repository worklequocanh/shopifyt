<?php
require_once __DIR__ . "/../includes/functions/auth_functions.php";
require_once __DIR__ . "/../includes/functions/admin_functions.php";

// Chỉ admin mới được xóa sản phẩm
restrictToRoles($pdo, ['admin']);

$role = $_SESSION['role'] ?? "admin";
$account_id = $_SESSION['id'] ?? 1;

// XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Kiểm tra sản phẩm có tồn tại không
    $check_product = $pdo->query("SELECT id, name FROM products WHERE id=$id");
    $product = $check_product->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product_name = $product['name'];

        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        $pdo->beginTransaction();

        try {
            // 1. Lấy danh sách ảnh của sản phẩm
            $images_result = $pdo->query("SELECT image_url FROM product_images WHERE product_id=$id");
            $image_files = [];

            if ($images_result) {
                while ($img = $images_result->fetch(PDO::FETCH_ASSOC)) {
                    $image_files[] = $img['image_url'];
                }
            }
            // 2. Xóa các bản ghi ảnh trong database
            $pdo->query("DELETE FROM product_images WHERE product_id=$id");

            // 3. Xóa sản phẩm (không cần check order_details vì chưa có bảng này)
            if ($pdo->query("DELETE FROM products WHERE id=$id")) {
                // 4. Xóa file ảnh vật lý
                foreach ($image_files as $image_path) {
                    // Bỏ qua ảnh mặc định
                    if ($image_path === "/assets/img/product-sale.png") {
                        continue;
                    }

                    // Tạo đường dẫn tuyệt đối đến file
                    $full_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;

                    // Kiểm tra và xóa file
                    if (file_exists($full_path)) {
                        if (@unlink($full_path)) {
                            echo "Đã xóa: $image_path<br>";
                        } else {
                            echo "Không thể xóa: $image_path<br>";
                        }
                    } else {
                        echo "File không tồn tại: $image_path<br>";
                    }
                }
                $message = "Xóa sản phẩm '$product_name' thành công!";
                $_SESSION['success_message'] = $message;
            } else {
                throw new Exception($pdo->errorInfo()[2]);
            }

            // Commit transaction
            $pdo->commit();
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $pdo->rollBack();
            $_SESSION['error_message'] = 'Lỗi khi xóa sản phẩm: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Không tìm thấy sản phẩm!';
    }

    header("Location: product-list.php");
    exit;
} else {

    // Nếu không có tham số delete, chuyển về admin
    header("Location: product-list.php");
    exit;
}
