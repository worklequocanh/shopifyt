-- =================================================================
-- FILE SQL HOÀN CHỈNH CHO CSDL E-COMMERCE (Bản đầy đủ)
-- Tác giả: Gemini
-- Ngày cập nhật: 23/10/2025
-- Các thay đổi chính: Thêm bảng user_carts để lưu giỏ hàng.
-- =================================================================

-- Thiết lập môi trường
SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- ==========================================
-- 1. Bảng Tài khoản (accounts)
-- ==========================================
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL COMMENT 'Luôn lưu mật khẩu đã được băm (hashed)',
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(15) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `salary` DECIMAL(10,2) DEFAULT NULL COMMENT 'Lương dành cho vai trò nhân viên',
  `position` VARCHAR(255) DEFAULT NULL COMMENT 'Chức vụ dành cho vai trò nhân viên',
  `role` ENUM('admin', 'employee', 'customer') NOT NULL DEFAULT 'customer',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Tài khoản đang hoạt động (TRUE) hay bị khóa (FALSE)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. Bảng Danh mục (categories)
-- ==========================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 3. Bảng Sản phẩm (products)
-- ==========================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL CHECK (`price` >= 0),
  `stock` INT UNSIGNED NOT NULL DEFAULT 0,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Sản phẩm có đang được bán hay không',
  `is_featured` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Đánh dấu sản phẩm nổi bật',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_name` (`name`),
  INDEX `idx_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 4. Bảng Hình ảnh sản phẩm (product_images)
-- ==========================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `is_main` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 5. Bảng Đơn hàng (orders)
-- ==========================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_phone` VARCHAR(15) NOT NULL,
  `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 CHECK (`total_amount` >= 0),
  `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE,
  INDEX `idx_account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 6. Bảng Chi tiết đơn hàng (order_details)
-- ==========================================
DROP TABLE IF EXISTS `order_details`;
CREATE TABLE `order_details` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL CHECK (`quantity` > 0),
  `unit_price` DECIMAL(10, 2) NOT NULL CHECK (`unit_price` >= 0) COMMENT 'Giá sản phẩm tại thời điểm mua',
  `subtotal` DECIMAL(12, 2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
  INDEX `idx_order_id_details` (`order_id`),
  INDEX `idx_product_id_details` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 7. Bảng Giỏ hàng của người dùng (user_carts) - MỚI
-- ==========================================
DROP TABLE IF EXISTS `user_carts`;
CREATE TABLE `user_carts` (
  `account_id` INT UNSIGNED NOT NULL PRIMARY KEY,
  `cart_data` JSON NOT NULL COMMENT 'Lưu trữ dữ liệu giỏ hàng dưới dạng JSON',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;