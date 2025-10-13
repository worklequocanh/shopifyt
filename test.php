<?php
require_once __DIR__ . '/includes/config.php';

try {
    // Query lấy tất cả dữ liệu từ bảng products
    // $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
    // $products = $stmt->fetchAll();

    $sql = "SELECT * FROM products WHERE id = ? ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);  // Bind param an toàn (array)
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Hiển thị kết quả dưới dạng HTML table
    echo "<h2>Dữ liệu từ bảng products:</h2>";
    if (empty($products)) {
        echo "<p>Không có dữ liệu nào trong bảng products.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Actions</th></tr>";  // Chỉnh cột theo bảng thực tế
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($product['id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($product['name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($product['price'] ?? '') . "</td>";
            echo "<td><a href='edit.php?id=" . $product['id'] . "'>Edit</a></td>";  // Ví dụ link edit (tùy chỉnh)
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><a href='test.php'>Reload</a> | <a href='index.php'>Về trang chủ</a>";
    
} catch (PDOException $e) {
    die("❌ Lỗi kết nối: " . $e->getMessage());
}
?>