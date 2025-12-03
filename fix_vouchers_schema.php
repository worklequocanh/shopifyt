<?php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "Checking 'vouchers' table schema...\n";

    // Helper function to check if column exists
    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return $stmt->fetch() !== false;
    }

    // List of columns to check and add if missing
    $columnsToAdd = [
        'name' => "ADD COLUMN `name` varchar(255) NOT NULL COMMENT 'Tên voucher' AFTER `code`",
        'description' => "ADD COLUMN `description` text DEFAULT NULL COMMENT 'Mô tả voucher' AFTER `name`",
        'discount_type' => "ADD COLUMN `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage' AFTER `description`",
        'max_discount' => "ADD COLUMN `max_discount` decimal(10,2) DEFAULT NULL COMMENT 'Giảm tối đa (cho loại %)' AFTER `min_order_value`",
        'usage_limit' => "ADD COLUMN `usage_limit` int(11) DEFAULT NULL COMMENT 'Số lần sử dụng tối đa' AFTER `max_discount`",
        'used_count' => "ADD COLUMN `used_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lần đã sử dụng' AFTER `usage_limit`",
        'created_by' => "ADD COLUMN `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Admin tạo voucher' AFTER `is_active`"
    ];

    foreach ($columnsToAdd as $col => $sql) {
        if (!columnExists($pdo, 'vouchers', $col)) {
            echo "Adding column '$col'...\n";
            $pdo->exec("ALTER TABLE `vouchers` $sql");
        } else {
            echo "Column '$col' already exists.\n";
        }
    }
    
    // Add foreign key for created_by if it doesn't exist
    // This is a bit harder to check safely in one go, so we wrap in try-catch or just skip if column existed
    try {
        $pdo->exec("ALTER TABLE `vouchers` ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL");
        echo "Added foreign key for created_by.\n";
    } catch (PDOException $e) {
        // Ignore if FK already exists
    }

    echo "Schema update completed successfully.\n";

} catch (PDOException $e) {
    echo "Schema update failed: " . $e->getMessage() . "\n";
}
