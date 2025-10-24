<?php
require_once __DIR__ . '/../includes/functions/auth_functions.php';
require_once __DIR__ . '/../includes/functions/functions.php';
require_once __DIR__ . '/../includes/functions/product_functions.php';
require_once __DIR__ . '/../includes/functions/cart_functions.php';

restrictToRoles('customer');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
  exit();
}

$accountId = $_SESSION['id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$productId || !$quantity || $quantity <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.']);
  exit();
}

$quantity = (int)$quantity;

try {
  // Bắt đầu một giao dịch (transaction) để đảm bảo toàn vẹn dữ liệu
  $pdo->beginTransaction();

  // 5. KIỂM TRA SẢN PHẨM TRONG DATABASE (Giữ nguyên)
  $stmt = $pdo->prepare("SELECT name, stock, is_active FROM products WHERE id = ? FOR UPDATE"); // "FOR UPDATE" để khóa dòng, tránh race condition
  $stmt->execute([$productId]);
  $product = $stmt->fetch();

  if (!$product || $product['is_active'] != 1) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.']);
    $pdo->rollBack(); // Hủy bỏ giao dịch
    exit();
  }

  // 6. MỚI: LẤY GIỎ HÀNG HIỆN TẠI TỪ DATABASE THAY VÌ SESSION
  $stmt = $pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = ?");
  $stmt->execute([$accountId]);
  $userCart = $stmt->fetch();

  // Giải mã JSON thành mảng PHP. Nếu chưa có giỏ hàng, tạo mảng rỗng.
  $cart = $userCart ? json_decode($userCart['cart_data'], true) : [];

  // 7. KIỂM TRA SỐ LƯỢNG TỒN KHO (Logic giữ nguyên, nhưng lấy dữ liệu từ $cart)
  $currentCartQty = $cart[$productId] ?? 0;
  $newTotalQty = $currentCartQty + $quantity;

  if ($newTotalQty > $product['stock']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Số lượng yêu cầu vượt quá số hàng tồn kho (' . $product['stock'] . ').']);
    $pdo->rollBack();
    exit();
  }

  // 8. MỚI: CẬP NHẬT GIỎ HÀNG VÀO DATABASE
  $cart[$productId] = $newTotalQty;
  $newCartJson = json_encode($cart);

  // Sử dụng INSERT ... ON DUPLICATE KEY UPDATE để tự động tạo mới hoặc cập nhật
  $updateStmt = $pdo->prepare(
    "INSERT INTO user_carts (account_id, cart_data) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data)"
  );
  $updateStmt->execute([$accountId, $newCartJson]);

  // Hoàn tất giao dịch thành công
  $pdo->commit();

  // 9. TRẢ VỀ KẾT QUẢ THÀNH CÔNG (Giữ nguyên)
  echo json_encode([
    'success' => true,
    'message' => 'Đã thêm "' . htmlspecialchars($product['name']) . '" vào giỏ hàng!'
  ]);
} catch (PDOException $e) {
  // Nếu có lỗi, hủy bỏ mọi thay đổi trong giao dịch
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  // error_log('Cart DB error: ' . $e->getMessage()); 
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu, vui lòng thử lại sau.']);
}


// header('Location: ' . $_SERVER['HTTP_REFERER']);
// exit;