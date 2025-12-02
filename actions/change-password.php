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
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmNewPassword = $_POST['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
  exit();
}
if ($newPassword !== $confirmNewPassword) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.']);
  exit();
}

try {
  if (!verifyUserPassword($currentPassword, $pdo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
    exit();
  }

  updateUserPassword($newPassword, $pdo);

  echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công.']);
  exit();
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ. Vui lòng thử lại sau.']);
  exit();
}
