<?php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 1. Create vouchers table
    $sqlVouchers = "CREATE TABLE IF NOT EXISTS `vouchers` (
      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `code` varchar(50) NOT NULL COMMENT 'Mã voucher (VD: SUMMER2024)',
      `name` varchar(255) NOT NULL COMMENT 'Tên voucher',
      `description` text DEFAULT NULL COMMENT 'Mô tả voucher',
      `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage' COMMENT 'Loại giảm giá: phần trăm hoặc số tiền cố định',
      `discount_value` decimal(10,2) NOT NULL COMMENT 'Giá trị giảm (% hoặc số tiền)',
      `min_order_value` decimal(10,2) DEFAULT 0 COMMENT 'Giá trị đơn hàng tối thiểu',
      `max_discount` decimal(10,2) DEFAULT NULL COMMENT 'Giảm tối đa (cho loại %)',
      `usage_limit` int(11) DEFAULT NULL COMMENT 'Số lần sử dụng tối đa (NULL = không giới hạn)',
      `used_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lần đã sử dụng',
      `start_date` datetime NOT NULL COMMENT 'Ngày bắt đầu',
      `end_date` datetime NOT NULL COMMENT 'Ngày kết thúc',
      `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái hoạt động',
      `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Admin tạo voucher',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `code` (`code`),
      KEY `idx_code` (`code`),
      KEY `idx_active` (`is_active`),
      KEY `idx_dates` (`start_date`, `end_date`),
      KEY `created_by` (`created_by`),
      CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sqlVouchers);
    echo "Table 'vouchers' created or already exists.\n";

    // 2. Create voucher_usage table
    $sqlUsage = "CREATE TABLE IF NOT EXISTS `voucher_usage` (
      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `voucher_id` int(10) UNSIGNED NOT NULL,
      `order_id` int(10) UNSIGNED NOT NULL,
      `account_id` int(10) UNSIGNED NOT NULL,
      `discount_amount` decimal(10,2) NOT NULL COMMENT 'Số tiền được giảm',
      `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `voucher_id` (`voucher_id`),
      KEY `order_id` (`order_id`),
      KEY `account_id` (`account_id`),
      CONSTRAINT `voucher_usage_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE,
      CONSTRAINT `voucher_usage_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
      CONSTRAINT `voucher_usage_ibfk_3` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlUsage);
    echo "Table 'voucher_usage' created or already exists.\n";

    // 3. Alter orders table
    // Check if column exists first to avoid error
    $stmt = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'voucher_id'");
    if (!$stmt->fetch()) {
        $sqlAlter = "ALTER TABLE `orders` 
        ADD COLUMN `voucher_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Voucher được áp dụng' AFTER `total_amount`,
        ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0 COMMENT 'Số tiền được giảm' AFTER `voucher_id`,
        ADD KEY `voucher_id` (`voucher_id`),
        ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL;";
        
        $pdo->exec($sqlAlter);
        echo "Table 'orders' altered.\n";
    } else {
        echo "Table 'orders' already has 'voucher_id' column.\n";
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
