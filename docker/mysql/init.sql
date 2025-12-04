-- ============================================
-- Shopify Clone Database Initialization Script
-- ============================================
-- This script creates the database structure and populates it with sample data
-- Password for all accounts: 123456

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `shopifyt` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shopifyt`;

-- ============================================
-- 1. ACCOUNTS TABLE
-- ============================================
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `role` enum('admin','employee','customer') NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample accounts (password: 123456)
INSERT INTO `accounts` (`name`, `email`, `password`, `phone`, `address`, `role`, `is_active`, `email_verified`) VALUES
('Admin User', 'admin@shopifyt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', '123 Admin Street, HCM City', 'admin', 1, 1),
('Employee User', 'employee@shopifyt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0902345678', '456 Employee Road, HCM City', 'employee', 1, 1),
('Nguyễn Văn A', 'customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0903456789', '789 Customer Ave, Hanoi', 'customer', 1, 1),
('Trần Thị B', 'customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0904567890', '321 Buyer Street, Da Nang', 'customer', 1, 1),
('Lê Văn C', 'customer3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0905678901', '654 Shopping Blvd, HCM City', 'customer', 1, 1);

-- ============================================
-- 2. CATEGORIES TABLE
-- ============================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `description`) VALUES
('Áo T-Shirt', 'Áo thun basic, daily wear'),
('Áo Sơ Mi', 'Áo sơ mi nam nữ công sở và casual'),
('Quần Jeans', 'Quần jean nam nữ các loại'),
('Váy Đầm', 'Váy đầm nữ cho các dịp'),
('Phụ Kiện', 'Phụ kiện thời trang: túi, nón, khăn');

-- ============================================
-- 3. PRODUCTS TABLE
-- ============================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT 0,
  `category_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_category` (`category_id`),
  CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert 30 sample products
INSERT INTO `products` (`name`, `description`, `price`, `stock`, `category_id`, `is_featured`) VALUES
-- T-Shirts (1-8)
('Áo Thun Basic White', 'Áo thun cotton trắng basic, form regular fit', 150000, 50, 1, 1),
('Áo Thun Oversize Black', 'Áo thun oversize màu đen, chất liệu cotton 100%', 180000, 45, 1, 1),
('Áo Thun Graphic Vintage', 'Áo thun in họa tiết vintage, phong cách retro', 220000, 30, 1, 0),
('Áo Thun Polo Classic', 'Áo polo cổ bẻ, lịch sự và thanh lịch', 250000, 40, 1, 1),
('Áo Thun Stripe Navy', 'Áo thun sọc ngang màu navy, phong cách biển', 190000, 35, 1, 0),
('Áo Thun Premium Cotton', 'Áo thun cotton cao cấp, mềm mại thoáng mát', 280000, 25, 1, 1),
('Áo Thun Henley Grey', 'Áo thun henley cổ khuy, màu xám cổ điển', 210000, 30, 1, 0),
('Áo Thun V-Neck Minimal', 'Áo thun cổ tim tối giản, dễ phối đồ', 160000, 55, 1, 0),

-- Shirts (9-14)
('Áo Sơ Mi Oxford Blue', 'Áo sơ mi oxford xanh navy, công sở lịch sự', 350000, 40, 2, 1),
('Áo Sơ Mi Flannel Check', 'Áo sơ mi flannel kẻ caro, phong cách casual', 320000, 35, 2, 0),
('Áo Sơ Mi Linen Trắng', 'Áo sơ mi linen trắng, thoáng mát mùa hè', 380000, 28, 2, 1),
('Áo Sơ Mi Denim Basic', 'Áo sơ mi denim basic, bền đẹp theo thời gian', 420000, 30, 2, 0),
('Áo Sơ Mi Cuban Collar', 'Áo sơ mi cổ cuban, phong cách resort', 340000, 25, 2, 0),
('Áo Sơ Mi Slim Fit', 'Áo sơ mi body, ôm vừa vặn thời trang', 360000, 32, 2, 0),

-- Jeans (15-21)
('Quần Jean Skinny Dark Blue', 'Quần jean skinny màu xanh đậm, form ôm', 450000, 45, 3, 1),
('Quần Jean Straight Vintage', 'Quần jean straight wash nhẹ, phong cách vintage', 480000, 38, 3, 1),
('Quần Jean Baggy Black', 'Quần jean baggy đen, phong cách streetwear', 520000, 32, 3, 0),
('Quần Jean Mom Fit', 'Quần jean mom fit trẻ trung, năng động', 460000, 40, 3, 0),
('Quần Jean Ripped Style', 'Quần jean rách gối, cá tính và phá cách', 490000, 28, 3, 1),
('Quần Jean Bootcut Classic', 'Quần jean ống loe cổ điển, tôn dáng', 470000, 35, 3, 0),
('Quần Jean Wide Leg', 'Quần jean ống rộng, thoải mái và thời trang', 510000, 30, 3, 0),

-- Dresses (22-26)
('Váy Midi Floral', 'Váy midi hoa nhí, nữ tính và dịu dàng', 380000, 35, 4, 1),
('Váy Đầm Maxi Elegant', 'Váy đầm maxi sang trọng, dự tiệc đẹp', 680000, 20, 4, 1),
('Váy Suông A-Line', 'Váy suông form A, che khuyết điểm hiệu quả', 320000, 42, 4, 0),
('Váy Bodycon Mini', 'Váy ôm body ngắn, quyến rũ và gợi cảm', 290000, 38, 4, 0),
('Váy Đầm Vintage Polka Dot', 'Váy đầm chấm bi retro, phong cách cổ điển', 420000, 25, 4, 1),

-- Accessories (27-30)
('Túi Tote Canvas', 'Túi tote vải canvas bền đẹp, đựng đồ tiện lợi', 180000, 60, 5, 0),
('Nón Bucket Streetwear', 'Nón bucket phong cách streetwear, chống nắng', 120000, 80, 5, 0),
('Khăn Choàng Cotton Scarf', 'Khăn choàng cotton mềm mại, ấm áp', 150000, 50, 5, 0),
('Belt Leather Classic', 'Thắt lưng da thật, bền đẹp theo thời gian', 250000, 45, 5, 0);

-- ============================================
-- 4. PRODUCT IMAGES TABLE
-- ============================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_product_images` (`product_id`),
  CONSTRAINT `fk_product_images` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample product images (placeholder images)
INSERT INTO `product_images` (`product_id`, `image_url`, `is_main`) VALUES
(1, 'https://via.placeholder.com/600x800/FFFFFF/000000?text=White+T-Shirt', 1),
(2, 'https://via.placeholder.com/600x800/000000/FFFFFF?text=Black+Oversize', 1),
(3, 'https://via.placeholder.com/600x800/8B4513/FFFFFF?text=Vintage+Graphic', 1),
(4, 'https://via.placeholder.com/600x800/4169E1/FFFFFF?text=Polo+Classic', 1),
(5, 'https://via.placeholder.com/600x800/000080/FFFFFF?text=Stripe+Navy', 1),
(6, 'https://via.placeholder.com/600x800/D3D3D3/000000?text=Premium+Cotton', 1),
(7, 'https://via.placeholder.com/600x800/808080/FFFFFF?text=Henley+Grey', 1),
(8, 'https://via.placeholder.com/600x800/F5F5F5/000000?text=V-Neck+Minimal', 1),
(9, 'https://via.placeholder.com/600x800/00008B/FFFFFF?text=Oxford+Blue', 1),
(10, 'https://via.placeholder.com/600x800/8B0000/FFFFFF?text=Flannel+Check', 1),
(11, 'https://via.placeholder.com/600x800/FFFFF0/000000?text=Linen+White', 1),
(12, 'https://via.placeholder.com/600x800/4682B4/FFFFFF?text=Denim+Basic', 1),
(13, 'https://via.placeholder.com/600x800/E6E6FA/000000?text=Cuban+Collar', 1),
(14, 'https://via.placeholder.com/600x800/708090/FFFFFF?text=Slim+Fit', 1),
(15, 'https://via.placeholder.com/600x800/00008B/FFFFFF?text=Skinny+Dark', 1),
(16, 'https://via.placeholder.com/600x800/87CEEB/000000?text=Straight+Vintage', 1),
(17, 'https://via.placeholder.com/600x800/000000/FFFFFF?text=Baggy+Black', 1),
(18, 'https://via.placeholder.com/600x800/4169E1/FFFFFF?text=Mom+Fit', 1),
(19, 'https://via.placeholder.com/600x800/2F4F4F/FFFFFF?text=Ripped+Style', 1),
(20, 'https://via.placeholder.com/600x800/191970/FFFFFF?text=Bootcut+Classic', 1),
(21, 'https://via.placeholder.com/600x800/6495ED/FFFFFF?text=Wide+Leg', 1),
(22, 'https://via.placeholder.com/600x800/FFB6C1/8B008B?text=Midi+Floral', 1),
(23, 'https://via.placeholder.com/600x800/DC143C/FFFFFF?text=Maxi+Elegant', 1),
(24, 'https://via.placeholder.com/600x800/FFC0CB/8B008B?text=A-Line+Dress', 1),
(25, 'https://via.placeholder.com/600x800/FF1493/FFFFFF?text=Bodycon+Mini', 1),
(26, 'https://via.placeholder.com/600x800/000000/FFFFFF?text=Vintage+Polka', 1),
(27, 'https://via.placeholder.com/600x800/F5DEB3/8B4513?text=Tote+Canvas', 1),
(28, 'https://via.placeholder.com/600x800/8B4513/FFFFFF?text=Bucket+Hat', 1),
(29, 'https://via.placeholder.com/600x800/D2691E/FFFFFF?text=Cotton+Scarf', 1),
(30, 'https://via.placeholder.com/600x800/654321/FFFFFF?text=Leather+Belt', 1);

-- ============================================
-- 5. ORDERS TABLE
-- ============================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `voucher_id` int DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','accepted','cancelled','delivered') DEFAULT 'pending',
  `order_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_account_order` (`account_id`),
  KEY `idx_status` (`status`),
  KEY `idx_order_date` (`order_date`),
  CONSTRAINT `fk_account_order` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. ORDER DETAILS TABLE
-- ============================================
DROP TABLE IF EXISTS `order_details`;
CREATE TABLE `order_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) STORED,
  PRIMARY KEY (`id`),
  KEY `fk_order` (`order_id`),
  KEY `fk_product_order` (`product_id`),
  CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_order` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. CART TABLE
-- ============================================
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`account_id`,`product_id`),
  KEY `fk_cart_product` (`product_id`),
  CONSTRAINT `fk_cart_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. VOUCHERS TABLE
-- ============================================
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `name` varchar(200) NOT NULL,
  `description` text,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample vouchers
INSERT INTO `vouchers` (`code`, `name`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `start_date`, `end_date`) VALUES
('WELCOME10', 'Giảm 10% cho khách hàng mới', 'Mã giảm giá 10% cho đơn hàng đầu tiên', 'percentage', 10.00, 200000, 100000, 100, '2025-01-01 00:00:00', '2025-12-31 23:59:59'),
('SALE50K', 'Giảm 50K đơn từ 500K', 'Giảm trực tiếp 50,000đ cho đơn hàng từ 500,000đ', 'fixed', 50000.00, 500000, NULL, 200, '2025-01-01 00:00:00', '2025-12-31 23:59:59'),
('VIP20', 'Giảm 20% cho khách VIP', 'Mã giảm giá đặc biệt 20% cho khách hàng thân thiết', 'percentage', 20.00, 1000000, 500000, 50, '2025-01-01 00:00:00', '2025-12-31 23:59:59');

-- ============================================
-- 9. VOUCHER USAGE TABLE
-- ============================================
DROP TABLE IF EXISTS `voucher_usage`;
CREATE TABLE `voucher_usage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voucher_id` int NOT NULL,
  `order_id` int NOT NULL,
  `account_id` int NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_voucher_usage_voucher` (`voucher_id`),
  KEY `fk_voucher_usage_order` (`order_id`),
  KEY `fk_voucher_usage_account` (`account_id`),
  CONSTRAINT `fk_voucher_usage_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_voucher_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_voucher_usage_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INITIALIZATION COMPLETE
-- ============================================
-- Database: shopifyt
-- Tables: 9 (accounts, categories, products, product_images, orders, order_details, cart, vouchers, voucher_usage)
-- Sample Data:
--   - 5 accounts (1 admin, 1 employee, 3 customers)
--   - 5 categories
--   - 30 products
--   - 30 product images
--   - 3 vouchers
-- All account passwords: 123456
-- ============================================
