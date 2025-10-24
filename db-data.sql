SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 1. THÊM DỮ LIỆU BẢNG `accounts`
-- ----------------------------
INSERT INTO `accounts` (`id`, `name`, `password`, `email`, `phone`, `address`, `role`, `is_active`) VALUES
(1, 'Admin Manager', 'hashed_password', 'admin@example.com', '0901234567', '123 Đường Admin, Quận 1, TP.HCM', 'admin', 1),
(2, 'Nhân Viên Kho', 'hashed_password', 'employee@example.com', '0912345678', '456 Đường Nhân Viên, Quận 3, TP.HCM', 'employee', 1),
(3, 'Nguyễn Anh Thư', 'hashed_password', 'customer1@example.com', '0987654321', '111 Nguyễn Trãi, Quận 5, TP.HCM', 'customer', 1),
(4, 'Trần Minh Hoàng', 'hashed_password', 'customer2@example.com', '0911223344', '222 Lê Văn Sỹ, Quận Tân Bình, TP.HCM', 'customer', 1),
(5, 'Lê Thị Bích', 'hashed_password', 'customer3@example.com', '0933445566', '333 Võ Văn Tần, Quận 3, TP.HCM', 'customer', 1);

-- ----------------------------
-- 2. THÊM DỮ LIỆU BẢNG `categories`
-- ----------------------------
INSERT INTO `categories` (`id`, `name`, `parent_id`) VALUES
(1, 'Áo', NULL),
(2, 'Quần', NULL),
(3, 'Váy & Đầm', NULL),
(4, 'Phụ Kiện', NULL)

-- ----------------------------
-- 3. THÊM DỮ LIỆU BẢNG `products`
-- ----------------------------
INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `is_featured`) VALUES
(1, 'Áo Thun Cotton Cổ Tròn Basic', 'Chất liệu 100% cotton thoáng mát, form regular fit.', 199000.00, 200, 5, 1),
(2, 'Áo Thun Polo Pique Co Giãn', 'Vải cá sấu pique, co giãn 4 chiều, lịch sự và năng động.', 280000.00, 150, 5, 0),
(3, 'Áo Sơ Mi Oxford Tay Dài', 'Vải oxford dày dặn, đứng form, phù hợp môi trường công sở.', 450000.00, 100, 6, 1),
(4, 'Quần Jeans Slim-fit Xanh Đậm', 'Form ôm vừa vặn, tôn dáng, chất jean co giãn nhẹ.', 550000.00, 180, 7, 1),
(5, 'Quần Jeans Baggy Ống Rộng', 'Dáng quần rộng rãi, hack dáng, phong cách street style.', 580000.00, 120, 7, 0),
(6, 'Đầm Voan Hoa Nhí Vintage', 'Chất voan mềm mại, bay bổng, họa tiết hoa nhí nữ tính.', 520000.00, 80, 3, 1),
(7, 'Túi Tote Vải Canvas In Chữ', 'Kích thước lớn, đựng được nhiều đồ, thân thiện môi trường.', 180000.00, 300, 8, 0),
(8, 'Áo Khoác Jean Denim Jacket', 'Item không thể thiếu trong tủ đồ, phong cách và bền bỉ.', 680000.00, 90, 1, 0),
(9, 'Quần Kaki Chinos Ống Đứng', 'Thiết kế công sở lịch lãm, chất vải kaki cao cấp.', 480000.00, 130, 2, 0),
(10, 'Chân Váy Tennis Xếp Ly', 'Kiểu dáng trẻ trung, năng động, dễ phối đồ.', 280000.00, 250, 3, 0);

-- ----------------------------
-- 4. THÊM DỮ LIỆU BẢNG `product_images`
-- ----------------------------
INSERT INTO `product_images` (`product_id`, `image_url`, `is_main`) VALUES
(1, '/assets/img/product-1.jpg', 1), (1, '/assets/img/product-2.jpg', 0),
(2, '/assets/img/product-3.jpg', 1),
(3, '/assets/img/product-4.jpg', 1), (3, '/assets/img/product-5.jpg', 0),
(4, '/assets/img/product-6.jpg', 1),
(5, '/assets/img/product-1.jpg', 1),
(6, '/assets/img/product-2.jpg', 1),
(7, '/assets/img/product-3.jpg', 1),
(8, '/assets/img/product-4.jpg', 1),
(9, '/assets/img/product-5.jpg', 1),
(10, '/assets/img/product-6.jpg', 1);

-- ----------------------------
-- KẾT THÚC
-- ----------------------------
SET FOREIGN_KEY_CHECKS = 1;