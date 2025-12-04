<?php
/**
 * Debug Voucher Checkout
 * Visit: http://localhost:8080/debug_voucher.php
 */

echo "<h2>Debug Voucher Checkout</h2>";
echo "<p>Đặt hàng với voucher để xem debug info</p>";
echo "<hr>";

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if voucher columns exist
try {
    // Use 'mysql' as host for Docker, not 127.0.0.1
    $host = 'mysql';
    $dbname = 'myapp';
    $username = 'root';
    $password = 'rootpassword';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "<h3>1. Check Voucher Columns in Orders Table</h3>";
$stmt = $pdo->query("SHOW COLUMNS FROM orders WHERE Field IN ('voucher_id', 'discount_amount')");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($columns);
echo "</pre>";

echo "<h3>2. Recent Orders with Voucher Data</h3>";
$stmt = $pdo->query("SELECT id, account_id, total_amount, voucher_id, discount_amount, order_date FROM orders ORDER BY id DESC LIMIT 5");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Account</th><th>Total</th><th>Voucher ID</th><th>Discount</th><th>Date</th></tr>";
foreach ($orders as $order) {
    echo "<tr>";
    echo "<td>{$order['id']}</td>";
    echo "<td>{$order['account_id']}</td>";
    echo "<td>" . number_format($order['total_amount']) . "</td>";
    echo "<td>" . ($order['voucher_id'] ?? 'NULL') . "</td>";
    echo "<td>" . number_format($order['discount_amount']) . "</td>";
    echo "<td>{$order['order_date']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>3. Active Vouchers</h3>";
$stmt = $pdo->query("SELECT id, code, name, discount_type, discount_value, is_active FROM vouchers WHERE is_active=1");
$vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Type</th><th>Value</th></tr>";
foreach ($vouchers as $voucher) {
    echo "<tr>";
    echo "<td>{$voucher['id']}</td>";
    echo "<td><strong>{$voucher['code']}</strong></td>";
    echo "<td>{$voucher['name']}</td>";
    echo "<td>{$voucher['discount_type']}</td>";
    echo "<td>{$voucher['discount_value']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>4. Test Voucher Validation (Direct Query)</h3>";
$testCodes = ['10PT', '5PT', 'SUMMER2025', 'INVALID'];
$testAmount = 500000;

echo "<p>Testing with order amount: " . number_format($testAmount) . " VND</p>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Code</th><th>Valid?</th><th>Details</th></tr>";

foreach ($testCodes as $code) {
    echo "<tr>";
    echo "<td><strong>$code</strong></td>";
    
    // Manual validation
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch();
    
    if (!$voucher) {
        echo "<td style='color: red'>✗ Not Found</td>";
        echo "<td>Mã không tồn tại hoặc đã bị vô hiệu hóa</td>";
    } else {
        $now = date('Y-m-d H:i:s');
        $valid = true;
        $message = '';
        
        // Check date range
        if ($now < $voucher['start_date']) {
            $valid = false;
            $message = "Chưa bắt đầu (Start: {$voucher['start_date']})";
        } elseif ($now > $voucher['end_date']) {
            $valid = false;
            $message = "Đã hết hạn (End: {$voucher['end_date']})";
        }
        
        // Check usage limit
        if ($valid && !is_null($voucher['usage_limit']) && $voucher['used_count'] >= $voucher['usage_limit']) {
            $valid = false;
            $message = "Đã hết lượt ({$voucher['used_count']}/{$voucher['usage_limit']})";
        }
        
        // Check min order value
        if ($valid && $testAmount < $voucher['min_order_value']) {
            $valid = false;
            $message = "Đơn tối thiểu: " . number_format($voucher['min_order_value']) . " VND";
        }
        
        if ($valid) {
            // Calculate discount
            if ($voucher['discount_type'] === 'percentage') {
                $discount = ($testAmount * $voucher['discount_value']) / 100;
                if ($voucher['max_discount'] > 0 && $discount > $voucher['max_discount']) {
                    $discount = $voucher['max_discount'];
                }
            } else {
                $discount = $voucher['discount_value'];
            }
            $discount = min($discount, $testAmount);
            
            echo "<td style='color: green'>✓ Valid</td>";
            echo "<td>Discount: <strong>" . number_format($discount) . " VND</strong><br>";
            echo "Type: {$voucher['discount_type']}, Value: {$voucher['discount_value']}</td>";
        } else {
            echo "<td style='color: red'>✗ Invalid</td>";
            echo "<td>$message</td>";
        }
    }
    echo "</tr>";
}
echo "</table>";

echo "<h3>5. Check Form Submission</h3>";
echo "<p>Kiểm tra xem form có gửi voucher_code không:</p>";
echo "<form method='POST' action=''>";
echo "<input type='text' name='voucher_code' value='10PT' placeholder='Voucher Code'>";
echo "<button type='submit'>Test Submit</button>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_code'])) {
    echo "<div style='background: #e8f5e9; padding: 10px; margin: 10px 0; border: 1px solid #4caf50;'>";
    echo "<strong>✓ Form submitted!</strong><br>";
    echo "Voucher code received: <strong>" . htmlspecialchars($_POST['voucher_code']) . "</strong><br>";
    echo "Empty check: " . (empty($_POST['voucher_code']) ? 'EMPTY' : 'NOT EMPTY') . "<br>";
    echo "Value: '" . $_POST['voucher_code'] . "'<br>";
    echo "Length: " . strlen($_POST['voucher_code']);
    echo "</div>";
}

echo "<h3>6. Check Last Order Created</h3>";
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
$lastOrder = $stmt->fetch();
if ($lastOrder) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($lastOrder as $key => $value) {
        if (in_array($key, ['id', 'account_id', 'total_amount', 'voucher_id', 'discount_amount', 'order_date'])) {
            echo "<tr><td><strong>$key</strong></td><td>" . ($value ?? 'NULL') . "</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p>No orders found</p>";
}

echo "<hr>";
echo "<p><a href='/checkout'>Go to Checkout</a> | <a href='/account/orders'>Order History</a></p>";
?>
