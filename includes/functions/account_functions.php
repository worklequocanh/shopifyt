<?php

function getLoggedInUserInfo($pdo)
{
  if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  return null;
}

function getOrderHistoryForAccount(PDO $pdo): array
{
  $accountId = $_SESSION['id'];
  // Lấy các thông tin cần thiết từ bảng orders
  $stmt = $pdo->prepare(
    "SELECT id, order_date, total_amount, status 
         FROM orders 
         WHERE account_id = ? 
         ORDER BY order_date DESC"
  );
  $stmt->execute([$accountId]);
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $formattedOrders = [];

  // Mảng để chuyển đổi trạng thái từ tiếng Anh sang tiếng Việt
  $statusMap = [
    'pending'   => 'Đang xử lý',
    'paid'      => 'Đã thanh toán',
    'shipped'   => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
  ];

  // Lặp qua từng đơn hàng để định dạng lại dữ liệu cho dễ hiển thị
  foreach ($orders as $order) {
    $formattedOrders[] = [
      'order_code'    => '#' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
      'order_date'    => date('d/m/Y', strtotime($order['order_date'])),
      'total_amount'  => number_format($order['total_amount'], 0, ',', '.') . 'đ',
      'status'        => $statusMap[$order['status']] ?? ucfirst($order['status'])
    ];
  }

  return $formattedOrders;
}

function updateUserPassword($newPassword, $pdo)
{
  $accountId = $_SESSION['id'];
  $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
  $stmt = $pdo->prepare("UPDATE accounts SET password = :password WHERE id = :id");
  $stmt->execute([
    'password' => $hashedPassword,
    'id'       => $accountId
  ]);
}

function verifyUserPassword($password, $pdo)
{
  $accountId = $_SESSION['id'];
  $stmt = $pdo->prepare("SELECT password FROM accounts WHERE id = :id");
  $stmt->execute(['id' => $accountId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    return password_verify($password, $row['password']);
  }
  return false;
}