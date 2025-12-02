<?php
require_once __DIR__ . '/../includes/functions/auth_functions.php';
require_once __DIR__ . '/../includes/functions/functions.php';
require_once __DIR__ . '/../includes/functions/product_functions.php';
require_once __DIR__ . '/../includes/functions/cart_functions.php';

restrictToRoles($pdo, 'customer');

header('Content-Type: application/json');

// 1. KIỂM TRA ĐĂNG NHẬP VÀ PHƯƠNG THỨC
if (!isset($_SESSION['id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
  exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit();
}
$accountId = $_SESSION['id'];

// 2. LẤY DỮ LIỆU JSON TỪ REQUEST
$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['product_id'] ?? null;
$action = $data['action'] ?? null;
$quantity = $data['quantity'] ?? null;

if (!$productId || !$action) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
  exit();
}

try {
  $pdo->beginTransaction();

  // 3. LẤY GIỎ HÀNG HIỆN TẠI TỪ DB
  $stmt = $pdo->prepare("SELECT cart_data FROM user_carts WHERE account_id = ?");
  $stmt->execute([$accountId]);
  $userCart = $stmt->fetch();
  $cart = $userCart ? json_decode($userCart['cart_data'], true) : [];

  // 4. XỬ LÝ LOGIC DỰA TRÊN HÀNH ĐỘNG
  if (!isset($cart[$productId]) && $action !== 'delete') {
    throw new Exception('Sản phẩm không có trong giỏ hàng.');
  }

  switch ($action) {
    case 'update':
      if ($quantity <= 0) throw new Exception('Số lượng không hợp lệ.');

      // Kiểm tra tồn kho (quan trọng)
      $stockStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
      $stockStmt->execute([$productId]);
      $stock = $stockStmt->fetchColumn();
      if ($quantity > $stock) {
        throw new Exception('Số lượng vượt quá tồn kho (' . $stock . ').');
      }
      $cart[$productId] = $quantity;
      break;

    case 'delete':
      unset($cart[$productId]);
      break;

    default:
      throw new Exception('Hành động không hợp lệ.');
  }

  // 5. CẬP NHẬT LẠI GIỎ HÀNG VÀO DB
  $newCartJson = json_encode($cart);
  $updateStmt = $pdo->prepare("INSERT INTO user_carts (account_id, cart_data) VALUES (?, ?) ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data)");
  $updateStmt->execute([$accountId, $newCartJson]);

  // 6. TÍNH TOÁN LẠI TỔNG TIỀN VÀ TRẢ VỀ DỮ LIỆU MỚI
  // (Phần này cần tối ưu bằng cách chỉ lấy giá các sản phẩm có trong giỏ)
  $grandTotal = 0;
  $itemSubtotal = 0;

  if (!empty($cart)) {
    $productIds = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $priceStmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $priceStmt->execute($productIds);
    $products = $priceStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($cart as $pId => $qty) {
      $grandTotal += ($products[$pId] ?? 0) * $qty;
    }
  }

  if (isset($cart[$productId])) {
    $itemSubtotal = ($products[$productId] ?? 0) * $cart[$productId];
  }

  $pdo->commit();

  echo json_encode([
    'success' => true,
    'message' => 'Giỏ hàng đã được cập nhật!',
    'newGrandTotal' => format_currency($grandTotal),
    'newItemSubtotal' => format_currency($itemSubtotal),
    'isEmpty' => empty($cart)
  ]);
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
