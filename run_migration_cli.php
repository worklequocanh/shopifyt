<?php
// Manual .env parser since we can't rely on config/database.php which uses PDO
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    die("Error: .env file not found.\n");
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $env[trim($name)] = trim($value);
}

$host = $env['DB_HOST'] ?? 'localhost';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$name = $env['DB_NAME'] ?? 'db';

echo "Database: $name, User: $user, Host: $host\n";

// SQL commands to run
$sqlCommands = [
    "ALTER TABLE `vouchers` ADD COLUMN `name` varchar(255) NOT NULL COMMENT 'Tên voucher' AFTER `code`;",
    "ALTER TABLE `vouchers` ADD COLUMN `description` text DEFAULT NULL COMMENT 'Mô tả voucher' AFTER `name`;",
    "ALTER TABLE `vouchers` ADD COLUMN `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage' AFTER `description`;",
    "ALTER TABLE `vouchers` ADD COLUMN `max_discount` decimal(10,2) DEFAULT NULL COMMENT 'Giảm tối đa (cho loại %)' AFTER `min_order_value`;",
    "ALTER TABLE `vouchers` ADD COLUMN `usage_limit` int(11) DEFAULT NULL COMMENT 'Số lần sử dụng tối đa' AFTER `max_discount`;",
    "ALTER TABLE `vouchers` ADD COLUMN `used_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lần đã sử dụng' AFTER `usage_limit`;",
    "ALTER TABLE `vouchers` ADD COLUMN `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Admin tạo voucher' AFTER `is_active`;",
    "ALTER TABLE `vouchers` ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;"
];

// Create temporary SQL file
$tmpSqlFile = __DIR__ . '/temp_fix.sql';
$sqlContent = implode("\n", $sqlCommands);
file_put_contents($tmpSqlFile, $sqlContent);

// Construct mysql command
// Use --force to continue if errors (e.g. column exists)
$cmd = sprintf(
    "mysql -h%s -u%s %s %s < %s",
    escapeshellarg($host),
    escapeshellarg($user),
    $pass ? "-p" . escapeshellarg($pass) : "",
    escapeshellarg($name),
    escapeshellarg($tmpSqlFile)
);

echo "Executing SQL update...\n";
system($cmd . " 2>&1", $retval);

unlink($tmpSqlFile);

if ($retval === 0) {
    echo "Migration finished successfully.\n";
} else {
    echo "Migration finished with potential errors (exit code $retval). Check output above.\n";
}
