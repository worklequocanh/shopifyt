<?php
// Đọc file .env (ở thư mục gốc project)
$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die("⚠️ Không tìm thấy file .env — vui lòng tạo file .env ở thư mục gốc!");
}

// Chuyển nội dung .env thành mảng
// $env = parse_ini_file($envPath);
$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

$host    = $env['DB_HOST']     ?? 'db';
$dbname  = $env['DB_NAME']     ?? 'ecommerce';
$user    = $env['DB_USER']     ?? 'root';
$pass    = $env['DB_PASS']     ?? 'root';
$charset = $env['DB_CHARSET']  ?? 'utf8mb4';

// Chuỗi DSN (Data Source Name) cho PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Cấu hình thêm cho PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi bằng Exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Lấy dữ liệu dạng mảng kết hợp
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Dùng prepared statement thật
];

try {
    // Tạo kết nối PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("❌ Lỗi kết nối Database: " . $e->getMessage());
}
?>
