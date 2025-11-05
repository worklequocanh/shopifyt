<?php

/**
 * File functions.php - Các hàm hỗ trợ
 * ✅ ĐÃ CHUYỂN ĐỔI HOÀN TOÀN SANG PDO
 */

/**
 * Lấy ảnh sản phẩm chính
 */
function getProductImage($pdo, $product_id)
{
  try {
    $stmt = $pdo->prepare("SELECT image_url FROM product_images 
                              WHERE product_id = ? AND is_main = 1 
                              LIMIT 1");
    $stmt->execute([$product_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      return $row['image_url'];
    }
  } catch (PDOException $e) {
    error_log("Error fetching product image: " . $e->getMessage());
  }

  return '../assets/img/no-image.png';
}

/**
 * Upload ảnh sản phẩm
 */
function uploadImage($file)
{
  // 1️⃣ Đường dẫn tuyệt đối trên server (dùng để lưu file)
  $upload_dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/assets/img/';

  // 2️⃣ Đường dẫn tương đối cho web (lưu DB, hiển thị <img>)
  $web_dir = '/assets/img/';

  // Tạo thư mục nếu chưa có
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  // Cấu hình upload
  $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  $max_size = 5 * 1024 * 1024; // 5MB

  // Kiểm tra lỗi upload
  if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    return ['success' => false, 'message' => 'Lỗi upload file!'];
  }

  // Kiểm tra loại file
  if (!in_array($file['type'], $allowed_types)) {
    return ['success' => false, 'message' => 'Chỉ chấp nhận file JPG, PNG, GIF, WEBP!'];
  }

  // Kiểm tra kích thước
  if ($file['size'] > $max_size) {
    return ['success' => false, 'message' => 'File không được vượt quá 5MB!'];
  }

  // Tạo tên file unique
  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $filename = 'product-' . time() . '_' . rand(1000, 9999) . '.' . $extension;

  // 3️⃣ Nơi lưu thật trên server
  $file_path = $upload_dir . $filename;

  // 4️⃣ Đường dẫn hiển thị web (để lưu DB)
  $web_path = $web_dir . $filename;

  // Di chuyển file upload vào nơi lưu thật
  if (move_uploaded_file($file['tmp_name'], $file_path)) {
    return [
      'success' => true,
      'message' => 'Upload thành công!',
      'filename' => $filename,
      'server_path' => $file_path,  // đường dẫn thật (nội bộ)
      'web_path' => $web_path       // đường dẫn dùng trong DB hoặc src=""
    ];
  }

  return ['success' => false, 'message' => 'Không thể lưu file!'];
}

/**
 * ✅ SỬA: Lấy doanh thu theo tháng trong năm
 */
function getMonthlyRevenue($pdo, $year)
{
  try {
    $stmt = $pdo->prepare("SELECT MONTH(order_date) as month, 
                         SUM(total_amount) as revenue, 
                         COUNT(id) as order_count
                  FROM orders
                  WHERE YEAR(order_date) = ? 
                  AND status IN ('paid', 'shipped', 'accepted')
                  GROUP BY MONTH(order_date)
                  ORDER BY month");

    $stmt->execute([$year]);
    return $stmt;
  } catch (PDOException $e) {
    error_log("Error getting monthly revenue: " . $e->getMessage());
    return false;
  }
}

/**
 * ✅ SỬA: Lấy KPI nhân viên theo tháng
 */
function getMonthlyKPI($pdo, $month, $year)
{
  try {
    $stmt = $pdo->prepare("SELECT a.id, a.name, a.position,
                         COUNT(DISTINCT o.id) as orders_processed,
                         COALESCE(SUM(o.total_amount), 0) as total_sales,
                         COALESCE(AVG(o.total_amount), 0) as avg_order_value
                  FROM accounts a
                  LEFT JOIN orders o ON o.account_id = a.id 
                      AND MONTH(o.order_date) = ? 
                      AND YEAR(o.order_date) = ?
                      AND o.status IN ('paid', 'shipped', 'accepted')
                  WHERE a.role IN ('admin', 'employee')
                  GROUP BY a.id, a.name, a.position
                  ORDER BY total_sales DESC");

    $stmt->execute([$month, $year]);
    return $stmt;
  } catch (PDOException $e) {
    error_log("Error getting monthly KPI: " . $e->getMessage());
    return false;
  }
}

/**
 * ✅ SỬA: Lấy thông tin đơn hàng chi tiết
 */
function getOrderDetails($pdo, $order_id)
{
  try {
    $stmt = $pdo->prepare("SELECT o.*, a.name as customer_name, a.email, a.phone, a.address
                  FROM orders o
                  LEFT JOIN accounts a ON o.account_id = a.id
                  WHERE o.id = ?");

    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error getting order details: " . $e->getMessage());
    return false;
  }
}

/**
 * ✅ SỬA: Lấy chi tiết sản phẩm trong đơn hàng
 */
function getOrderItems($pdo, $order_id)
{
  try {
    $stmt = $pdo->prepare("SELECT od.*, p.name as product_name, p.stock
                    FROM order_details od
                    JOIN products p ON od.product_id = p.id
                    WHERE od.order_id = ?");

    $stmt->execute([$order_id]);
    return $stmt;
  } catch (PDOException $e) {
    error_log("Error getting order items: " . $e->getMessage());
    return false;
  }
}

/**
 * ✅ SỬA: Cập nhật trạng thái đơn hàng
 */
function updateOrderStatus($pdo, $order_id, $new_status)
{
  try {
    $old_order = getOrderDetails($pdo, $order_id);
    $old_status = $old_order['status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");

    if ($stmt->execute([$new_status, $order_id])) {
      handleStockUpdate($pdo, $order_id, $old_status, $new_status);
      return true;
    }

    return false;
  } catch (PDOException $e) {
    error_log("Error updating order status: " . $e->getMessage());
    return false;
  }
}

/**
 * ✅ SỬA: Xử lý cập nhật tồn kho khi đổi trạng thái đơn hàng
 */
function handleStockUpdate($pdo, $order_id, $old_status, $new_status)
{
  try {
    $items_stmt = getOrderItems($pdo, $order_id);
    if (!$items_stmt) return false;

    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Giảm tồn kho khi chuyển từ pending sang shipped/completed
    if (in_array($new_status, ['shipped', 'accepted']) && $old_status == 'pending') {
      $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
      foreach ($items as $item) {
        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
      }
    }

    // Hoàn lại tồn kho khi hủy đơn đã shipped/completed
    if ($new_status == 'cancelled' && in_array($old_status, ['shipped', 'accepted'])) {
      $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
      foreach ($items as $item) {
        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
      }
    }

    return true;
  } catch (PDOException $e) {
    error_log("Error handling stock update: " . $e->getMessage());
    return false;
  }
}

/**
 * Format số tiền VND
 */
function formatVND($amount)
{
  return number_format($amount, 0, ',', '.') . 'đ';
}

/**
 * Lấy badge color theo trạng thái đơn hàng
 */
function getOrderStatusBadge($status)
{
  $badges = [
    'pending' => 'warning',
    'paid' => 'info',
    'shipped' => 'primary',
    'accepted' => 'success',
    'cancelled' => 'danger'
  ];

  return $badges[$status] ?? 'secondary';
}

/**
 * Lấy tên trạng thái đơn hàng tiếng Việt
 */
function getOrderStatusText($status)
{
  $texts = [
    'pending' => 'Chờ xử lý',
    'paid' => 'Đã thanh toán',
    'shipped' => 'Đang giao',
    'accepted' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
  ];

  return $texts[$status] ?? $status;
}

/**
 * ✅ SỬA: Kiểm tra sản phẩm có đủ tồn kho không
 */
function checkProductStock($pdo, $product_id, $quantity)
{
  try {
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
      return $product['stock'] >= $quantity;
    }

    return false;
  } catch (PDOException $e) {
    error_log("Error checking product stock: " . $e->getMessage());
    return false;
  }
}

/**
 * ✅ SỬA: Lấy tổng số trang cho phân trang
 */
function getTotalPages($pdo, $table, $where = "1=1", $items_per_page = 10)
{
  try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table WHERE $where");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return ceil($row['total'] / $items_per_page);
  } catch (PDOException $e) {
    error_log("Error getting total pages: " . $e->getMessage());
    return 1;
  }
}

/**
 * Sanitize input
 */
function sanitizeInput($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

/**
 * Validate email
 */
function isValidEmail($email)
{
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Vietnam)
 */
function isValidPhone($phone)
{
  return preg_match('/^(0|\+84)[0-9]{9}$/', $phone);
}

/**
 * ✅ SỬA: Hàm lấy thống kê tổng quan
 */
function getOverallStats($pdo)
{
  $stats = [];

  try {
    // Tổng sản phẩm
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Tổng đơn hàng và doanh thu
    $stmt = $pdo->query("SELECT COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue 
                               FROM orders 
                               WHERE status IN ('paid', 'shipped', 'accepted')");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_orders'] = $row['total'];
    $stats['total_revenue'] = $row['revenue'];

    // Tổng khách hàng
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM accounts WHERE role='customer'");
    $stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Đơn hàng chờ xử lý
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status='pending'");
    $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    return $stats;
  } catch (PDOException $e) {
    error_log("Error getting overall stats: " . $e->getMessage());
    return [
      'total_products' => 0,
      'total_orders' => 0,
      'total_revenue' => 0,
      'total_customers' => 0,
      'pending_orders' => 0
    ];
  }
}
