-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 08:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Luôn lưu mật khẩu đã được băm (hashed)',
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL COMMENT 'Lương dành cho vai trò nhân viên',
  `position` varchar(255) DEFAULT NULL COMMENT 'Chức vụ dành cho vai trò nhân viên',
  `role` enum('admin','employee','customer') NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Tài khoản đang hoạt động (TRUE) hay bị khóa (FALSE)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `password`, `email`, `phone`, `address`, `salary`, `position`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin Manager', 'hashed_password', 'admin@example.com', '0901234567', '123 Đường Admin, Quận 1, TP.HCM', NULL, NULL, 'admin', 1, '2025-10-25 14:41:32', NULL),
(2, 'Nhân Viên kho', 'hashed_password', 'employee@example.com', '0912345678', '456 Đường Nhân Viên, Quận 3, TP.HCM', 0.00, '', 'employee', 1, '2025-10-25 14:41:32', '2025-10-26 17:03:21'),
(3, 'Nguyễn Anh Thư', 'hashed_password', 'customer1@example.com', '0987654321', '111 Nguyễn Trãi, Quận 5, TP.HCM', NULL, NULL, 'customer', 1, '2025-10-25 14:41:32', NULL),
(4, 'Trần Minh Hoàng', 'hashed_password', 'customer2@example.com', '0911223344', '222 Lê Văn Sỹ, Quận Tân Bình, TP.HCM', NULL, NULL, 'customer', 1, '2025-10-25 14:41:32', NULL),
(5, 'Lê Thị Bích', 'hashed_password', 'customer3@example.com', '0933445566', '333 Võ Văn Tần, Quận 3, TP.HCM', NULL, NULL, 'customer', 1, '2025-10-25 14:41:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID của danh mục cha, cho phép tạo danh mục đa cấp',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Áo', NULL, '2025-10-25 14:41:49', NULL),
(2, 'Quần', NULL, '2025-10-25 14:41:49', NULL),
(3, 'Váy & Đầm', NULL, '2025-10-25 14:41:49', NULL),
(4, 'Phụ Kiện', NULL, '2025-10-25 14:41:49', NULL),
(5, 'Áo Thun', 1, '2025-10-25 14:41:49', NULL),
(6, 'Áo Sơ Mi', 1, '2025-10-25 14:41:49', NULL),
(7, 'Quần Jeans', 2, '2025-10-25 14:41:49', NULL),
(8, 'Túi Xách', 4, '2025-10-25 14:41:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_phone` varchar(15) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 CHECK (`total_amount` >= 0),
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','shipped','accepted','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0),
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Sản phẩm có đang được bán hay không',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Đánh dấu sản phẩm nổi bật',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `is_active`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Áo Thun Cotton Cổ Tròn Basic', 'Chất liệu 100% cotton thoáng mát, form regular fit.', 198000.00, 200, 5, 1, 1, '2025-10-25 14:42:05', '2025-10-26 17:02:46'),
(2, 'Áo Thun Polo Pique Co Giãn', 'Vải cá sấu pique, co giãn 4 chiều, lịch sự và năng động.', 280000.00, 150, 5, 1, 0, '2025-10-25 14:42:05', NULL),
(3, 'Áo Sơ Mi Oxford Tay Dài', 'Vải oxford dày dặn, đứng form, phù hợp môi trường công sở.', 450000.00, 100, 6, 1, 1, '2025-10-25 14:42:05', NULL),
(4, 'Quần Jeans Slim-fit Xanh Đậm', 'Form ôm vừa vặn, tôn dáng, chất jean co giãn nhẹ.', 550000.00, 180, 7, 1, 1, '2025-10-25 14:42:05', NULL),
(5, 'Quần Jeans Baggy Ống Rộng', 'Dáng quần rộng rãi, hack dáng, phong cách street style.', 580000.00, 120, 7, 1, 0, '2025-10-25 14:42:05', NULL),
(6, 'Đầm Voan Hoa Nhí Vintage', 'Chất voan mềm mại, bay bổng, họa tiết hoa nhí nữ tính.', 520000.00, 80, 3, 1, 1, '2025-10-25 14:42:05', NULL),
(7, 'Túi Tote Vải Canvas In Chữ', 'Kích thước lớn, đựng được nhiều đồ, thân thiện môi trường.', 180000.00, 300, 8, 1, 0, '2025-10-25 14:42:05', NULL),
(8, 'Áo Khoác Jean Denim Jacket', 'Item không thể thiếu trong tủ đồ, phong cách và bền bỉ.', 680000.00, 90, 1, 1, 0, '2025-10-25 14:42:05', NULL),
(9, 'Quần Kaki Chinos Ống Đứng', 'Thiết kế công sở lịch lãm, chất vải kaki cao cấp.', 480000.00, 130, 2, 1, 0, '2025-10-25 14:42:05', NULL),
(10, 'Chân Váy Tennis Xếp Ly', 'Kiểu dáng trẻ trung, năng động, dễ phối đồ.', 280000.00, 250, 3, 1, 0, '2025-10-25 14:42:05', '2025-10-26 18:31:52');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_main`, `created_at`) VALUES
(1, 1, '../assets/img/product-1.jpg', 1, '2025-10-25 14:42:23'),
(2, 1, '../assets/img/product-2.jpg', 0, '2025-10-25 14:42:23'),
(3, 2, '../assets/img/product-3.jpg', 1, '2025-10-25 14:42:23'),
(4, 3, '../assets/img/product-4.jpg', 1, '2025-10-25 14:42:23'),
(5, 3, '../assets/img/product-5.jpg', 0, '2025-10-25 14:42:23'),
(6, 4, '../assets/img/product-5.jpg', 1, '2025-10-25 14:42:23'),
(7, 5, '../assets/img/product-1.jpg', 1, '2025-10-25 14:42:23'),
(8, 6, '../assets/img/product-2.jpg', 1, '2025-10-25 14:42:23'),
(9, 7, '../assets/img/product-3.jpg', 1, '2025-10-25 14:42:23'),
(10, 8, '../assets/img/product-4.jpg', 1, '2025-10-25 14:42:23'),
(11, 9, '../assets/img/product-5.jpg', 1, '2025-10-25 14:42:23'),
(12, 10, '../assets/img/product-5.jpg', 1, '2025-10-25 14:42:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_id` (`account_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
