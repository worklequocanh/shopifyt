-- Migration: Add Voucher Support to Orders Table
-- Run this with: mysql -u root -p shopifyt < migrate_orders_voucher.sql

USE shopifyt;

-- Add voucher_id column if not exists
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'shopifyt' 
                   AND TABLE_NAME = 'orders' 
                   AND COLUMN_NAME = 'voucher_id');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `orders` ADD COLUMN `voucher_id` INT UNSIGNED DEFAULT NULL COMMENT \'Mã giảm giá đã áp dụng\' AFTER `total_amount`',
    'SELECT "voucher_id column already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add discount_amount column if not exists
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'shopifyt' 
                   AND TABLE_NAME = 'orders' 
                   AND COLUMN_NAME = 'discount_amount');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `orders` ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT \'Số tiền được giảm từ voucher\' AFTER `voucher_id`',
    'SELECT "discount_amount column already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for voucher_id if not exists
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = 'shopifyt' 
                   AND TABLE_NAME = 'orders' 
                   AND INDEX_NAME = 'fk_voucher');

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `fk_voucher` (`voucher_id`)',
    'SELECT "fk_voucher index already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if not exists
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                  WHERE TABLE_SCHEMA = 'shopifyt' 
                  AND TABLE_NAME = 'orders' 
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME LIKE '%voucher%');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_vouchers` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE SET NULL',
    'SELECT "Foreign key constraint for voucher already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show current structure
SELECT 'Migration completed! Current voucher-related columns:' AS Status;
SHOW COLUMNS FROM `orders` WHERE Field IN ('total_amount', 'voucher_id', 'discount_amount');
