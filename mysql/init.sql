-- =================================================================
-- FILE SQL HOÀN CHỈNH CHO CSDL E-COMMERCE (Bản đầy đủ)
-- Tác giả: Gemini
-- Ngày cập nhật: 23/10/2025
-- Các thay đổi chính: 
-- - Thêm bảng user_carts để lưu giỏ hàng
-- - Thêm bảng vouchers và tích hợp vào orders
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
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
-- 5. Bảng Mã giảm giá (vouchers)
-- ==========================================
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã voucher (VD: SUMMER2024)',
  `name` VARCHAR(255) NOT NULL COMMENT 'Tên voucher',
  `description` TEXT DEFAULT NULL COMMENT 'Mô tả voucher',
  `discount_type` ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage' COMMENT 'Loại giảm giá',
  `discount_value` DECIMAL(10,2) NOT NULL COMMENT 'Giá trị giảm',
  `min_order_value` DECIMAL(10,2) DEFAULT 0 COMMENT 'Đơn tối thiểu',
  `max_discount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Giảm tối đa (cho %)',
  `usage_limit` INT DEFAULT NULL COMMENT 'Số lần dùng tối đa (NULL=vô hạn)',
  `used_count` INT NOT NULL DEFAULT 0 COMMENT 'Đã dùng',
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_code` (`code`),
  INDEX `idx_dates` (`start_date`, `end_date`),
  INDEX `idx_active` (`is_active`),
  FOREIGN KEY (`created_by`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 5b. Bảng Lịch sử dùng Voucher (voucher_usage)
-- ==========================================
DROP TABLE IF EXISTS `voucher_usage`;
CREATE TABLE `voucher_usage` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `voucher_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NOT NULL,
  `account_id` INT UNSIGNED NOT NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL,
  `used_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 6. Bảng Đơn hàng (orders)
-- ==========================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_phone` VARCHAR(15) NOT NULL,
  `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 CHECK (`total_amount` >= 0),
  `voucher_id` INT UNSIGNED DEFAULT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0,
  `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'accepted', 'cancelled') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE SET NULL,
  INDEX `idx_account_id` (`account_id`),
  INDEX `fk_voucher` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 7. Bảng Chi tiết đơn hàng (order_details)
-- ==========================================
DROP TABLE IF EXISTS `order_details`;
CREATE TABLE `order_details` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL CHECK (`quantity` > 0),
  `unit_price` DECIMAL(10, 2) NOT NULL CHECK (`unit_price` >= 0) COMMENT 'Giá sản phẩm tại thời điểm mua',
  `subtotal` DECIMAL(12, 2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
  INDEX `idx_order_id_details` (`order_id`),
  INDEX `idx_product_id_details` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 8. Bảng Giỏ hàng của người dùng (user_carts)
-- ==========================================
DROP TABLE IF EXISTS `user_carts`;
CREATE TABLE `user_carts` (
  `account_id` INT UNSIGNED NOT NULL PRIMARY KEY,
  `cart_data` JSON NOT NULL COMMENT 'Lưu trữ dữ liệu giỏ hàng dưới dạng JSON',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- INSERT DỮ LIỆU MẪU
-- ==========================================

-- Insert dữ liệu mẫu cho vouchers
INSERT INTO `vouchers` (`code`, `name`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_by`) VALUES
('WELCOME10', 'Chào mừng thành viên mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10.00, 100000, 50000, 100, 0, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1),
('FREESHIP', 'Miễn phí vận chuyển', 'Giảm 30k phí ship cho đơn từ 200k', 'fixed', 30000, 200000, NULL, NULL, 0, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1),
('SUMMER50K', 'Chào Hè Rực Rỡ', 'Giảm 50k cho đơn từ 500k', 'fixed', 50000, 500000, NULL, 50, 0, '2024-06-01 00:00:00', '2025-08-31 23:59:59', 1, 1);

SET FOREIGN_KEY_CHECKS = 1;

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 1. THÊM DỮ LIỆU BẢNG `accounts`
-- ----------------------------
INSERT INTO `accounts` (`id`, `name`, `password`, `email`, `phone`, `address`, `role`, `is_active`) VALUES
(1, 'Admin Manager', '$2y$10$20ZjvxQPJyMJNbCNqTVsMetW6Z6bLILry7PIUI9Hn5NTCO1WOli9y', 'admin@example.com', '0901234567', '123 Đường Admin, Quận 1, TP.HCM', 'admin', 1),
(2, 'Nhân Viên Kho', '$2y$10$20ZjvxQPJyMJNbCNqTVsMetW6Z6bLILry7PIUI9Hn5NTCO1WOli9y', 'employee@example.com', '0912345678', '456 Đường Nhân Viên, Quận 3, TP.HCM', 'employee', 1),
(3, 'Nguyễn Anh Thư', '$2y$10$20ZjvxQPJyMJNbCNqTVsMetW6Z6bLILry7PIUI9Hn5NTCO1WOli9y', 'customer1@example.com', '0987654321', '111 Nguyễn Trãi, Quận 5, TP.HCM', 'customer', 1),
(4, 'Trần Minh Hoàng', '$2y$10$20ZjvxQPJyMJNbCNqTVsMetW6Z6bLILry7PIUI9Hn5NTCO1WOli9y', 'customer2@example.com', '0911223344', '222 Lê Văn Sỹ, Quận Tân Bình, TP.HCM', 'customer', 1),
(5, 'Lê Thị Bích', '$2y$10$20ZjvxQPJyMJNbCNqTVsMetW6Z6bLILry7PIUI9Hn5NTCO1WOli9y', 'customer3@example.com', '0933445566', '333 Võ Văn Tần, Quận 3, TP.HCM', 'customer', 1);
-- Mật khẩu chung: 123456

-- =================================================================
-- FILE SQL HOÀN CHỈNH - DỮ LIỆU MẪU E-COMMERCE FASHION
-- 30+ Sản phẩm quần áo với hình ảnh thực tế
-- =================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- ==========================================
-- 2. CHÈN DỮ LIỆU MẪU
-- ==========================================

-- 2.1. Bảng categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Áo Thun'),
(2, 'Áo Sơ Mi'),
(3, 'Quần Jeans'),
(4, 'Quần Short'),
(5, 'Đầm/Váy'),
(6, 'Áo Khoác'),
(7, 'Áo Len'),
(8, 'Đồ Bộ'),
(9, 'Áo Polo'),
(10, 'Quần Âu');

-- 2.3. Bảng vouchers
INSERT INTO `vouchers` (`id`, `code`, `name`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_by`) VALUES
(1, 'WELCOME10', 'Chào mừng thành viên mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10.00, 100000, 50000, 100, 0, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1),
(2, 'FREESHIP', 'Miễn phí vận chuyển', 'Giảm 30k phí ship cho đơn từ 200k', 'fixed', 30000, 200000, NULL, NULL, 0, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1),
(3, 'SUMMER50K', 'Chào Hè Rực Rỡ', 'Giảm 50k cho đơn từ 500k', 'fixed', 50000, 500000, NULL, 50, 0, '2024-06-01 00:00:00', '2025-08-31 23:59:59', 1, 1);

-- 2.4. Bảng products
INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `is_featured`) VALUES
-- Áo Thun (Category 1)
(1, 'Áo Thun Cotton Basic Trắng', 'Áo thun cotton 100% co giãn tốt, thấm hút mồ hôi, form regular', 149000, 50, 1, 1),
(2, 'Áo Thun Tay Lỡ Đen', 'Áo thun tay lỡ unisex chất liệu cotton pha, thoáng mát', 179000, 45, 1, 0),
(3, 'Áo Thun In Hình Streetwear', 'Áo thun streetwear phong cách trẻ trung, in hình độc đáo', 220000, 30, 1, 1),
(4, 'Áo Thun Cổ Tròn Xám', 'Áo thun cổ tròn basic màu xám, dễ phối đồ', 159000, 40, 1, 0),
(5, 'Áo Thun Local Brand', 'Áo thun local brand chất lượng cao, thiết kế độc quyền', 250000, 25, 1, 1),

-- Áo Sơ Mi (Category 2)
(6, 'Sơ Mi Trắng Văn Phòng', 'Sơ mi trắng form slim fit, chất liệu poplin không nhăn', 350000, 35, 2, 1),
(7, 'Sơ Mi Kẻ Sọc Xanh', 'Sơ mi kẻ sọc thanh lịch, phù hợp đi làm và dạo phố', 420000, 28, 2, 0),
(8, 'Sơ Mi Floral Họa Tiết', 'Sơ mi họa tiết floral phong cách bohemian', 380000, 22, 2, 1),
(9, 'Sơ Mi Denim Xanh Đậm', 'Sơ mi chất liệu denim, phong cách cá tính', 320000, 18, 2, 0),
(10, 'Sơ Mi Lụa Cao Cấp', 'Sơ mi chất liệu lụa tự nhiên, sang trọng và thoải mái', 550000, 15, 2, 1),

-- Quần Jeans (Category 3)
(11, 'Quần Jeans Slim Fit Đen', 'Quần jeans slim fit màu đen, co giãn 4 chiều', 450000, 40, 3, 1),
(12, 'Quần Jeans Skinny Xanh Nhạt', 'Quần jeans skinny wash sáng, phong cách trẻ trung', 420000, 35, 3, 0),
(13, 'Quần Jeans Boyfriend', 'Quần jeans boyfriend rộng rãi thoải mái', 480000, 25, 3, 1),
(14, 'Quần Jeans Ống Rộng', 'Quần jeans ống rộng phong cách vintage', 520000, 20, 3, 0),
(15, 'Quần Jeans Rách Gối', 'Quần jeans rách gối phong cách streetwear', 490000, 30, 3, 1),

-- Quần Short (Category 4)
(16, 'Quần Short Kaki Đen', 'Quần short kaki form regular, chất liệu thoáng mát', 220000, 60, 4, 0),
(17, 'Quần Short Jeans Xanh', 'Quần short jeans wash trung, dễ phối đồ', 190000, 55, 4, 1),
(18, 'Quần Short Thể Thao', 'Quần short thể thao co giãn, thấm hút mồ hôi', 150000, 70, 4, 0),
(19, 'Quần Short Cotton Basic', 'Quần short cotton basic nhiều màu, mặc nhà thoải mái', 120000, 80, 4, 1),
(20, 'Quần Short Jogger', 'Quần short jogger có dây rút, phong cách năng động', 180000, 45, 4, 0),

-- Đầm/Váy (Category 5)
(21, 'Đầm Suông Body Kiểu', 'Đầm suông body phong cách tối giản, chất liệu linen', 320000, 25, 5, 1),
(22, 'Váy Liền Tay Bồng', 'Váy liền thân tay bồng họa tiết hoa, đi tiệc sang trọng', 650000, 15, 5, 1),
(23, 'Đầm Maxi Dạo Phố', 'Đầm maxi dạo phố chất liệu voan mát mẻ', 280000, 20, 5, 0),
(24, 'Váy Ngắn Denim', 'Váy ngắn denim phong cách trẻ trung, năng động', 210000, 30, 5, 1),
(25, 'Đầm Ôm Bodycon', 'Đầm bodycon dự tiệc, tôn dáng', 380000, 18, 5, 0),

-- Áo Khoác (Category 6)
(26, 'Áo Khoác Denim Jacket', 'Áo khoác denim classic, dễ phối đồ', 520000, 20, 6, 1),
(27, 'Áo Khoác Bomber', 'Áo khoác bomber phong cách streetwear', 450000, 25, 6, 1),
(28, 'Áo Khoác Blazer', 'Áo khoác blazer thanh lịch, mặc đi làm', 680000, 15, 6, 0),
(29, 'Áo Khoác Hoodie', 'Áo khoác hoodie nỉ mềm, ấm áp', 350000, 35, 6, 1),
(30, 'Áo Khoác Gió', 'Áo khoác gió chống nước, đi du lịch thể thao', 290000, 40, 6, 0),

-- Áo Len (Category 7)
(31, 'Áo Len Cổ Lọ', 'Áo len cổ lọ mặc đẹp trong mùa lạnh', 280000, 30, 7, 1),
(32, 'Áo Len Cardigan', 'Áo len cardigan dáng dài, mặc ngoài thanh lịch', 320000, 25, 7, 0);

-- 2.5. Bảng product_images
INSERT INTO `product_images` (`product_id`, `image_url`, `is_main`) VALUES
-- Áo Thun Cotton Basic Trắng (1)
(1, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500&h=600&fit=crop', 1),
(1, 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=500&h=600&fit=crop', 0),

-- Áo Thun Tay Lỡ Đen (2)
(2, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=600&fit=crop', 1),
(2, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=600&fit=crop', 0),

-- Áo Thun In Hình Streetwear (3)
(3, 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=500&h=600&fit=crop', 1),
(3, 'https://images.unsplash.com/photo-1586366775916-301eacf13e0d?w=500&h=600&fit=crop', 0),

-- Áo Thun Cổ Tròn Xám (4)
(4, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=500&h=600&fit=crop', 1),
(4, 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=500&h=600&fit=crop', 0),

-- Áo Thun Local Brand (5)
(5, 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=500&h=600&fit=crop', 1),
(5, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=600&fit=crop', 0),

-- Sơ Mi Trắng Văn Phòng (6)
(6, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&h=600&fit=crop', 1),
(6, 'https://images.unsplash.com/photo-1621072156002-e2fccdc0b176?w=500&h=600&fit=crop', 0),
(6, 'https://images.unsplash.com/photo-1589310243389-96a5483213a8?w=500&h=600&fit=crop', 0),

-- Sơ Mi Kẻ Sọc Xanh (7)
(7, 'https://images.unsplash.com/photo-1593030103066-0093718efeb9?w=500&h=600&fit=crop', 1),
(7, 'https://images.unsplash.com/photo-1589310243389-96a5483213a8?w=500&h=600&fit=crop', 0),

-- Sơ Mi Floral Họa Tiết (8)
(8, 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500&h=600&fit=crop', 1),
(8, 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=500&h=600&fit=crop', 0),

-- Sơ Mi Denim Xanh Đậm (9)
(9, 'https://images.unsplash.com/photo-1593030103066-0093718efeb9?w=500&h=600&fit=crop', 1),
(9, 'https://images.unsplash.com/photo-1586366775916-301eacf13e0d?w=500&h=600&fit=crop', 0),

-- Sơ Mi Lụa Cao Cấp (10)
(10, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&h=600&fit=crop', 1),
(10, 'https://images.unsplash.com/photo-1621072156002-e2fccdc0b176?w=500&h=600&fit=crop', 0),

-- Quần Jeans Slim Fit Đen (11)
(11, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500&h=600&fit=crop', 1),
(11, 'https://images.unsplash.com/photo-1582418702059-97ebafb35d09?w=500&h=600&fit=crop', 0),
(11, 'https://images.unsplash.com/photo-1582555172866-f73bb12a2ab3?w=500&h=600&fit=crop', 0),

-- Quần Jeans Skinny Xanh Nhạt (12)
(12, 'https://images.unsplash.com/photo-1582555172866-f73bb12a2ab3?w=500&h=600&fit=crop', 1),
(12, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500&h=600&fit=crop', 0),

-- Quần Jeans Boyfriend (13)
(13, 'https://images.unsplash.com/photo-1582418702059-97ebafb35d09?w=500&h=600&fit=crop', 1),
(13, 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=500&h=600&fit=crop', 0),

-- Quần Jeans Ống Rộng (14)
(14, 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=500&h=600&fit=crop', 1),
(14, 'https://images.unsplash.com/photo-1582555172866-f73bb12a2ab3?w=500&h=600&fit=crop', 0),

-- Quần Jeans Rách Gối (15)
(15, 'https://images.unsplash.com/photo-1582418702059-97ebafb35d09?w=500&h=600&fit=crop', 1),
(15, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500&h=600&fit=crop', 0),

-- Quần Short Kaki Đen (16)
(16, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&h=600&fit=crop', 1),
(16, 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=500&h=600&fit=crop', 0),

-- Quần Short Jeans Xanh (17)
(17, 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=500&h=600&fit=crop', 1),
(17, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&h=600&fit=crop', 0),

-- Quần Short Thể Thao (18)
(18, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&h=600&fit=crop', 1),
(18, 'https://images.unsplash.com/photo-1506629905607-e48b0e67d879?w=500&h=600&fit=crop', 0),

-- Quần Short Cotton Basic (19)
(19, 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=500&h=600&fit=crop', 1),
(19, 'https://images.unsplash.com/photo-1506629905607-e48b0e67d879?w=500&h=600&fit=crop', 0),

-- Quần Short Jogger (20)
(20, 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=500&h=600&fit=crop', 1),
(20, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&h=600&fit=crop', 0),

-- Đầm Suông Body Kiểu (21)
(21, 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&h=600&fit=crop', 1),
(21, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=500&h=600&fit=crop', 0),
(21, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=500&h=600&fit=crop', 0),

-- Váy Liền Tay Bồng (22)
(22, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=500&h=600&fit=crop', 1),
(22, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=500&h=600&fit=crop', 0),
(22, 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&h=600&fit=crop', 0),

-- Đầm Maxi Dạo Phố (23)
(23, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=500&h=600&fit=crop', 1),
(23, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=500&h=600&fit=crop', 0),

-- Váy Ngắn Denim (24)
(24, 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&h=600&fit=crop', 1),
(24, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=500&h=600&fit=crop', 0),

-- Đầm Ôm Bodycon (25)
(25, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=500&h=600&fit=crop', 1),
(25, 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&h=600&fit=crop', 0),
(25, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=500&h=600&fit=crop', 0),

-- Áo Khoác Denim Jacket (26)
(26, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&h=600&fit=crop', 1),
(26, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=600&fit=crop', 0),
(26, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=600&fit=crop', 0),

-- Áo Khoác Bomber (27)
(27, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&h=600&fit=crop', 1),
(27, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=600&fit=crop', 0),

-- Áo Khoác Blazer (28)
(28, 'https://images.unsplash.com/photo-1593030103066-0093718efeb9?w=500&h=600&fit=crop', 1),
(28, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&h=600&fit=crop', 0),

-- Áo Khoác Hoodie (29)
(29, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=600&fit=crop', 1),
(29, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=600&fit=crop', 0),

-- Áo Khoác Gió (30)
(30, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&h=600&fit=crop', 1),
(30, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=600&fit=crop', 0),

-- Áo Len Cổ Lọ (31)
(31, 'https://images.unsplash.com/photo-1574180045827-681f8a1a9622?w=500&h=600&fit=crop', 1),
(31, 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500&h=600&fit=crop', 0),

-- Áo Len Cardigan (32)
(32, 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500&h=600&fit=crop', 1),
(32, 'https://images.unsplash.com/photo-1574180045827-681f8a1a9622?w=500&h=600&fit=crop', 0);

-- 2.6. Bảng orders
INSERT INTO `orders` (`id`, `account_id`, `customer_name`, `shipping_address`, `shipping_phone`, `total_amount`, `voucher_id`, `discount_amount`, `status`) VALUES
(1, 3, 'Khách Hàng A', '789 Đường DEF, Quận 3, TP.HCM', '0901000103', 920000, 1, 92000, 'accepted'),
(2, 4, 'Nguyễn Văn An', '321 Đường GHI, Quận 4, TP.HCM', '0901000104', 670000, NULL, 0, 'pending'),
(3, 3, 'Khách Hàng A', '789 Đường DEF, Quận 3, TP.HCM', '0901000103', 1250000, 3, 50000, 'accepted');

-- 2.7. Bảng order_details
INSERT INTO `order_details` (`order_id`, `product_id`, `product_name`, `quantity`, `unit_price`) VALUES
(1, 1, 'Áo Thun Cotton Basic Trắng', 2, 149000),
(1, 11, 'Quần Jeans Slim Fit Đen', 1, 450000),
(1, 26, 'Áo Khoác Denim Jacket', 1, 520000),
(2, 6, 'Sơ Mi Trắng Văn Phòng', 1, 350000),
(2, 17, 'Quần Short Thể Thao', 2, 150000),
(3, 8, 'Sơ Mi Floral Họa Tiết', 1, 380000),
(3, 15, 'Quần Jeans Rách Gối', 1, 490000),
(3, 22, 'Váy Liền Tay Bồng', 1, 650000);

-- 2.8. Bảng user_carts
INSERT INTO `user_carts` (`account_id`, `cart_data`) VALUES
(3, '{"items": [{"product_id": 3, "quantity": 1, "price": 220000}, {"product_id": 15, "quantity": 2, "price": 490000}]}'),
(4, '{"items": [{"product_id": 8, "quantity": 1, "price": 380000}, {"product_id": 20, "quantity": 1, "price": 180000}]}');

-- ==========================================
-- 3. RESET AUTO_INCREMENT
-- ==========================================
ALTER TABLE `accounts` AUTO_INCREMENT = 100;
ALTER TABLE `categories` AUTO_INCREMENT = 100;
ALTER TABLE `products` AUTO_INCREMENT = 100;
ALTER TABLE `product_images` AUTO_INCREMENT = 100;
ALTER TABLE `vouchers` AUTO_INCREMENT = 100;
ALTER TABLE `orders` AUTO_INCREMENT = 100;
ALTER TABLE `order_details` AUTO_INCREMENT = 100;

-- ==========================================
-- 4. KÍCH HOẠT FOREIGN KEY CHECKS
-- ==========================================
SET FOREIGN_KEY_CHECKS = 1;
