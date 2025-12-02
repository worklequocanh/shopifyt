<?php
require_once __DIR__ . '/../includes/functions/auth_functions.php';
require_once __DIR__ . '/../includes/functions/functions.php';
require_once __DIR__ . '/../includes/functions/account_functions.php';

restrictToRoles($pdo, 'customer');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
  exit();
}

$accountId = $_SESSION['id'];
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (empty($name) || empty($phone) || empty($address)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
  exit();
}

// validate phone number format
if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ.']);
  exit();
}

try {
  $stmt = $pdo->prepare("UPDATE accounts SET name = :name, phone = :phone, address = :address WHERE id = :id");
  $stmt->execute([
    'name'    => $name,
    'phone'   => $phone,
    'address' => $address,
    'id'      => $accountId
  ]);

  echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin cá nhân thành công.']);
  exit();
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ. Vui lòng thử lại sau.']);
  exit();
}
