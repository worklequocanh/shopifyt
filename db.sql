-- ==========================================
-- 🧱 DATABASE STRUCTURE: SHOP SYSTEM
-- ==========================================

-- Xóa bảng cũ nếu tồn tại (để tránh lỗi khi chạy lại)
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS accounts;

-- ==========================================
-- 1️⃣ Bảng tài khoản người dùng
-- ==========================================
CREATE TABLE accounts
(
  id INT
  AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR
  (255) NOT NULL,
  password VARCHAR
  (255) NOT NULL,
  email VARCHAR
  (255) NOT NULL UNIQUE,
  phone VARCHAR
  (15) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  salary DECIMAL
  (10,2) DEFAULT 0.00,
  hired_date DATE DEFAULT NULL,
  position VARCHAR
  (255) DEFAULT NULL,
  role ENUM
  ('admin', 'employee', 'customer') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

  -- ==========================================
  -- 2️⃣ Bảng sản phẩm
  -- ==========================================
  CREATE TABLE products
  (
    id INT
    AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR
    (255) NOT NULL,
  description TEXT DEFAULT NULL,
  price DECIMAL
    (10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  category VARCHAR
    (255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON
    UPDATE CURRENT_TIMESTAMP
    );

    -- ==========================================
    -- 3️⃣ Bảng hình ảnh sản phẩm
    -- ==========================================
    CREATE TABLE product_images
    (
      id INT
      AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR
      (500) NOT NULL,
  is_main BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY
      (product_id) REFERENCES products
      (id) ON
      DELETE CASCADE
);

      -- ==========================================
      -- 4️⃣ Bảng đơn hàng (orders)
      -- ==========================================
      CREATE TABLE orders
      (
        id INT
        AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  total_amount DECIMAL
        (10,2) NOT NULL DEFAULT 0.00,
  order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM
        ('pending', 'paid', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  FOREIGN KEY
        (account_id) REFERENCES accounts
        (id) ON
        DELETE CASCADE
);

        -- ==========================================
        -- 5️⃣ Bảng chi tiết đơn hàng (order_details)
        -- ==========================================
        CREATE TABLE order_details
        (
          id INT
          AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL
          (10,2) NOT NULL,
  subtotal DECIMAL
          (10,2) GENERATED ALWAYS AS
          (quantity * unit_price) STORED,
  FOREIGN KEY
          (order_id) REFERENCES orders
          (id) ON
          DELETE CASCADE,
  FOREIGN KEY (product_id)
          REFERENCES products
          (id) ON
          DELETE CASCADE
);

          -- Accounts (1 admin + 2 customers)
INSERT INTO accounts (id, name, password, email, phone, address, position, role, created_at) VALUES
(1, 'Admin Hieu', SHA2('ChangeMe123!',256), 'admin@example.com', '0900000001', 'Hà Nội', 'Quản trị', 'admin', NOW()),
(2, 'Nguyễn Văn Khánh', SHA2('CustPass123!',256), 'khachhang1@example.com', '0900000002', 'Hà Nội', '', 'customer', NOW()),
(3, 'Trần Thị Mai', SHA2('CustPass123!',256), 'khachhang2@example.com', '0900000003', 'Hồ Chí Minh', '', 'customer', NOW());
ALTER TABLE accounts AUTO_INCREMENT = 4;

-- Products (50 items)
INSERT INTO products (id, name, description, price, stock, category, created_at) VALUES
(1, 'Áo thun nam basic', 'Áo thun nam basic - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1490975, 24, 'Áo', NOW()),
(2, 'Áo thun nữ form rộng', 'Áo thun nữ form rộng - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 202451, 45, 'Áo', NOW()),
(3, 'Áo sơ mi nam tay dài', 'Áo sơ mi nam tay dài - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 663575, 38, 'Áo', NOW()),
(4, 'Áo sơ mi nữ họa tiết', 'Áo sơ mi nữ họa tiết - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 442632, 23, 'Áo', NOW()),
(5, 'Áo khoác jean nam', 'Áo khoác jean nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1569141, 79, 'Áo', NOW()),
(6, 'Áo khoác bomber nữ', 'Áo khoác bomber nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 332323, 85, 'Áo', NOW()),
(7, 'Áo len cổ tròn', 'Áo len cổ tròn - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1034834, 14, 'Áo', NOW()),
(8, 'Áo cardigan nữ mỏng', 'Áo cardigan nữ mỏng - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 212489, 21, 'Áo', NOW()),
(9, 'Áo polo nam cotton', 'Áo polo nam cotton - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 608517, 39, 'Áo', NOW()),
(10, 'Áo nỉ hoodie unisex', 'Áo nỉ hoodie unisex - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1209806, 87, 'Áo', NOW()),
(11, 'Quần jean nam ống đứng', 'Quần jean nam ống đứng - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 205649, 81, 'Quần', NOW()),
(12, 'Quần jean nữ ống rộng', 'Quần jean nữ ống rộng - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 566992, 93, 'Quần', NOW()),
(13, 'Quần tây nam công sở', 'Quần tây nam công sở - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1620785, 79, 'Quần', NOW()),
(14, 'Quần short kaki nam', 'Quần short kaki nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1029796, 38, 'Quần', NOW()),
(15, 'Quần legging nữ co dãn', 'Quần legging nữ co dãn - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1092059, 85, 'Quần', NOW()),
(16, 'Quần jogger nam thể thao', 'Quần jogger nam thể thao - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 733408, 10, 'Quần', NOW()),
(17, 'Váy liền suông nữ', 'Váy liền suông nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1741334, 30, 'Váy', NOW()),
(18, 'Váy xòe nữ hoa', 'Váy xòe nữ hoa - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1614104, 64, 'Váy', NOW()),
(19, 'Váy body nữ dự tiệc', 'Váy body nữ dự tiệc - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 863557, 45, 'Váy', NOW()),
(20, 'Váy maxi đi biển', 'Váy maxi đi biển - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 476064, 37, 'Váy', NOW()),
(21, 'Giày thể thao nam', 'Giày thể thao nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1751163, 53, 'Giày Dép', NOW()),
(22, 'Giày sneaker nữ', 'Giày sneaker nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 364350, 21, 'Giày Dép', NOW()),
(23, 'Giày lười nam da', 'Giày lười nam da - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 946765, 22, 'Giày Dép', NOW()),
(24, 'Dép sandal nữ quai mảnh', 'Dép sandal nữ quai mảnh - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 902834, 54, 'Giày Dép', NOW()),
(25, 'Boots da nữ cổ thấp', 'Boots da nữ cổ thấp - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1416105, 43, 'Giày Dép', NOW()),
(26, 'Nón lưỡi trai unisex', 'Nón lưỡi trai unisex - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1842670, 15, 'Phụ kiện', NOW()),
(27, 'Túi đeo chéo nam', 'Túi đeo chéo nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1680358, 68, 'Phụ kiện', NOW()),
(28, 'Túi xách nữ nhỏ gọn', 'Túi xách nữ nhỏ gọn - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1274551, 25, 'Phụ kiện', NOW()),
(29, 'Thắt lưng da nam', 'Thắt lưng da nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 943844, 20, 'Phụ kiện', NOW()),
(30, 'Khăn choàng nữ họa tiết', 'Khăn choàng nữ họa tiết - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1307713, 47, 'Phụ kiện', NOW()),
(31, 'Bộ đồ ngủ nữ cotton', 'Bộ đồ ngủ nữ cotton - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1889387, 90, 'Áo', NOW()),
(32, 'Áo ba lỗ nam thể thao', 'Áo ba lỗ nam thể thao - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1447129, 56, 'Áo', NOW()),
(33, 'Sơ mi nữ cổ bèo', 'Sơ mi nữ cổ bèo - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1360795, 34, 'Áo', NOW()),
(34, 'Quần culottes nữ', 'Quần culottes nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1627594, 18, 'Quần', NOW()),
(35, 'Quần short jean nữ', 'Quần short jean nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 246100, 94, 'Quần', NOW()),
(36, 'Váy ngắn nữ công sở', 'Váy ngắn nữ công sở - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 627937, 47, 'Váy', NOW()),
(37, 'Giày cao gót nữ', 'Giày cao gót nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 317335, 39, 'Giày Dép', NOW()),
(38, 'Dép lê unisex', 'Dép lê unisex - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1967147, 22, 'Giày Dép', NOW()),
(39, 'Túi tote vải nữ', 'Túi tote vải nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 947182, 45, 'Phụ kiện', NOW()),
(40, 'Balo laptop nam', 'Balo laptop nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1100870, 91, 'Phụ kiện', NOW()),
(41, 'Áo khoác dù nam', 'Áo khoác dù nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1899256, 56, 'Áo', NOW()),
(42, 'Áo trench coat nữ', 'Áo trench coat nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 491110, 57, 'Áo', NOW()),
(43, 'Quần tây nữ công sở', 'Quần tây nữ công sở - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 895056, 36, 'Quần', NOW()),
(44, 'Quần vải nam summer', 'Quần vải nam summer - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1555459, 44, 'Quần', NOW()),
(45, 'Đầm suông maxi', 'Đầm suông maxi - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1621823, 97, 'Váy', NOW()),
(46, 'Giày oxford nam', 'Giày oxford nam - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1509028, 19, 'Giày Dép', NOW()),
(47, 'Giày mule nữ', 'Giày mule nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1427440, 91, 'Giày Dép', NOW()),
(48, 'Ví da nam nhỏ gọn', 'Ví da nam nhỏ gọn - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 508903, 78, 'Phụ kiện', NOW()),
(49, 'Găng tay mùa đông nữ', 'Găng tay mùa đông nữ - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 1679089, 41, 'Phụ kiện', NOW()),
(50, 'Áo thun nam basic (2)', 'Áo thun nam basic (2) - Chất liệu tốt, thiết kế tiện dụng, phù hợp hàng ngày.', 492679, 69, 'Áo', NOW());
ALTER TABLE products AUTO_INCREMENT = 51;

-- Product images (2 per product)
INSERT INTO product_images (id, product_id, image_url, is_main, created_at) VALUES
(1, 1, 'assets/img/products/áo-thun-nam-basic-1.jpg', TRUE, NOW()),
(2, 1, 'assets/img/products/áo-thun-nam-basic-2.jpg', FALSE, NOW()),
(3, 2, 'assets/img/products/áo-thun-nữ-form-rộng-1.jpg', TRUE, NOW()),
(4, 2, 'assets/img/products/áo-thun-nữ-form-rộng-2.jpg', FALSE, NOW()),
(5, 3, 'assets/img/products/áo-sơ-mi-nam-tay-dài-1.jpg', TRUE, NOW()),
(6, 3, 'assets/img/products/áo-sơ-mi-nam-tay-dài-2.jpg', FALSE, NOW()),
(7, 4, 'assets/img/products/áo-sơ-mi-nữ-họa-tiết-1.jpg', TRUE, NOW()),
(8, 4, 'assets/img/products/áo-sơ-mi-nữ-họa-tiết-2.jpg', FALSE, NOW()),
(9, 5, 'assets/img/products/áo-khoác-jean-nam-1.jpg', TRUE, NOW()),
(10, 5, 'assets/img/products/áo-khoác-jean-nam-2.jpg', FALSE, NOW()),
(11, 6, 'assets/img/products/áo-khoác-bomber-nữ-1.jpg', TRUE, NOW()),
(12, 6, 'assets/img/products/áo-khoác-bomber-nữ-2.jpg', FALSE, NOW()),
(13, 7, 'assets/img/products/áo-len-cổ-tròn-1.jpg', TRUE, NOW()),
(14, 7, 'assets/img/products/áo-len-cổ-tròn-2.jpg', FALSE, NOW()),
(15, 8, 'assets/img/products/áo-cardigan-nữ-mỏng-1.jpg', TRUE, NOW()),
(16, 8, 'assets/img/products/áo-cardigan-nữ-mỏng-2.jpg', FALSE, NOW()),
(17, 9, 'assets/img/products/áo-polo-nam-cotton-1.jpg', TRUE, NOW()),
(18, 9, 'assets/img/products/áo-polo-nam-cotton-2.jpg', FALSE, NOW()),
(19, 10, 'assets/img/products/áo-nỉ-hoodie-unisex-1.jpg', TRUE, NOW()),
(20, 10, 'assets/img/products/áo-nỉ-hoodie-unisex-2.jpg', FALSE, NOW()),
(21, 11, 'assets/img/products/quần-jean-nam-ống-đứng-1.jpg', TRUE, NOW()),
(22, 11, 'assets/img/products/quần-jean-nam-ống-đứng-2.jpg', FALSE, NOW()),
(23, 12, 'assets/img/products/quần-jean-nữ-ống-rộng-1.jpg', TRUE, NOW()),
(24, 12, 'assets/img/products/quần-jean-nữ-ống-rộng-2.jpg', FALSE, NOW()),
(25, 13, 'assets/img/products/quần-tây-nam-công-sở-1.jpg', TRUE, NOW()),
(26, 13, 'assets/img/products/quần-tây-nam-công-sở-2.jpg', FALSE, NOW()),
(27, 14, 'assets/img/products/quần-short-kaki-nam-1.jpg', TRUE, NOW()),
(28, 14, 'assets/img/products/quần-short-kaki-nam-2.jpg', FALSE, NOW()),
(29, 15, 'assets/img/products/quần-legging-nữ-co-dãn-1.jpg', TRUE, NOW()),
(30, 15, 'assets/img/products/quần-legging-nữ-co-dãn-2.jpg', FALSE, NOW()),
(31, 16, 'assets/img/products/quần-jogger-nam-thể-thao-1.jpg', TRUE, NOW()),
(32, 16, 'assets/img/products/quần-jogger-nam-thể-thao-2.jpg', FALSE, NOW()),
(33, 17, 'assets/img/products/váy-liền-suông-nữ-1.jpg', TRUE, NOW()),
(34, 17, 'assets/img/products/váy-liền-suông-nữ-2.jpg', FALSE, NOW()),
(35, 18, 'assets/img/products/váy-xòe-nữ-hoa-1.jpg', TRUE, NOW()),
(36, 18, 'assets/img/products/váy-xòe-nữ-hoa-2.jpg', FALSE, NOW()),
(37, 19, 'assets/img/products/váy-body-nữ-dự-tiệc-1.jpg', TRUE, NOW()),
(38, 19, 'assets/img/products/váy-body-nữ-dự-tiệc-2.jpg', FALSE, NOW()),
(39, 20, 'assets/img/products/váy-maxi-đi-biển-1.jpg', TRUE, NOW()),
(40, 20, 'assets/img/products/váy-maxi-đi-biển-2.jpg', FALSE, NOW()),
(41, 21, 'assets/img/products/giày-thể-thao-nam-1.jpg', TRUE, NOW()),
(42, 21, 'assets/img/products/giày-thể-thao-nam-2.jpg', FALSE, NOW()),
(43, 22, 'assets/img/products/giày-sneaker-nữ-1.jpg', TRUE, NOW()),
(44, 22, 'assets/img/products/giày-sneaker-nữ-2.jpg', FALSE, NOW()),
(45, 23, 'assets/img/products/giày-lười-nam-da-1.jpg', TRUE, NOW()),
(46, 23, 'assets/img/products/giày-lười-nam-da-2.jpg', FALSE, NOW()),
(47, 24, 'assets/img/products/dép-sandal-nữ-quai-mảnh-1.jpg', TRUE, NOW()),
(48, 24, 'assets/img/products/dép-sandal-nữ-quai-mảnh-2.jpg', FALSE, NOW()),
(49, 25, 'assets/img/products/boots-da-nữ-cổ-thấp-1.jpg', TRUE, NOW()),
(50, 25, 'assets/img/products/boots-da-nữ-cổ-thấp-2.jpg', FALSE, NOW()),
(51, 26, 'assets/img/products/nón-lưỡi-trai-unisex-1.jpg', TRUE, NOW()),
(52, 26, 'assets/img/products/nón-lưỡi-trai-unisex-2.jpg', FALSE, NOW()),
(53, 27, 'assets/img/products/túi-đeo-chéo-nam-1.jpg', TRUE, NOW()),
(54, 27, 'assets/img/products/túi-đeo-chéo-nam-2.jpg', FALSE, NOW()),
(55, 28, 'assets/img/products/túi-xách-nữ-nhỏ-gọn-1.jpg', TRUE, NOW()),
(56, 28, 'assets/img/products/túi-xách-nữ-nhỏ-gọn-2.jpg', FALSE, NOW()),
(57, 29, 'assets/img/products/thắt-lưng-da-nam-1.jpg', TRUE, NOW()),
(58, 29, 'assets/img/products/thắt-lưng-da-nam-2.jpg', FALSE, NOW()),
(59, 30, 'assets/img/products/khăn-choàng-nữ-họa-tiết-1.jpg', TRUE, NOW()),
(60, 30, 'assets/img/products/khăn-choàng-nữ-họa-tiết-2.jpg', FALSE, NOW()),
(61, 31, 'assets/img/products/bộ-đồ-ngủ-nữ-cotton-1.jpg', TRUE, NOW()),
(62, 31, 'assets/img/products/bộ-đồ-ngủ-nữ-cotton-2.jpg', FALSE, NOW()),
(63, 32, 'assets/img/products/áo-ba-lỗ-nam-thể-thao-1.jpg', TRUE, NOW()),
(64, 32, 'assets/img/products/áo-ba-lỗ-nam-thể-thao-2.jpg', FALSE, NOW()),
(65, 33, 'assets/img/products/sơ-mi-nữ-cổ-bèo-1.jpg', TRUE, NOW()),
(66, 33, 'assets/img/products/sơ-mi-nữ-cổ-bèo-2.jpg', FALSE, NOW()),
(67, 34, 'assets/img/products/quần-culottes-nữ-1.jpg', TRUE, NOW()),
(68, 34, 'assets/img/products/quần-culottes-nữ-2.jpg', FALSE, NOW()),
(69, 35, 'assets/img/products/quần-short-jean-nữ-1.jpg', TRUE, NOW()),
(70, 35, 'assets/img/products/quần-short-jean-nữ-2.jpg', FALSE, NOW()),
(71, 36, 'assets/img/products/váy-ngắn-nữ-công-sở-1.jpg', TRUE, NOW()),
(72, 36, 'assets/img/products/váy-ngắn-nữ-công-sở-2.jpg', FALSE, NOW()),
(73, 37, 'assets/img/products/giày-cao-gót-nữ-1.jpg', TRUE, NOW()),
(74, 37, 'assets/img/products/giày-cao-gót-nữ-2.jpg', FALSE, NOW()),
(75, 38, 'assets/img/products/dép-lê-unisex-1.jpg', TRUE, NOW()),
(76, 38, 'assets/img/products/dép-lê-unisex-2.jpg', FALSE, NOW()),
(77, 39, 'assets/img/products/túi-tote-vải-nữ-1.jpg', TRUE, NOW()),
(78, 39, 'assets/img/products/túi-tote-vải-nữ-2.jpg', FALSE, NOW()),
(79, 40, 'assets/img/products/balo-laptop-nam-1.jpg', TRUE, NOW()),
(80, 40, 'assets/img/products/balo-laptop-nam-2.jpg', FALSE, NOW()),
(81, 41, 'assets/img/products/áo-khoác-dù-nam-1.jpg', TRUE, NOW()),
(82, 41, 'assets/img/products/áo-khoác-dù-nam-2.jpg', FALSE, NOW()),
(83, 42, 'assets/img/products/áo-trench-coat-nữ-1.jpg', TRUE, NOW()),
(84, 42, 'assets/img/products/áo-trench-coat-nữ-2.jpg', FALSE, NOW()),
(85, 43, 'assets/img/products/quần-tây-nữ-công-sở-1.jpg', TRUE, NOW()),
(86, 43, 'assets/img/products/quần-tây-nữ-công-sở-2.jpg', FALSE, NOW()),
(87, 44, 'assets/img/products/quần-vải-nam-summer-1.jpg', TRUE, NOW()),
(88, 44, 'assets/img/products/quần-vải-nam-summer-2.jpg', FALSE, NOW()),
(89, 45, 'assets/img/products/đầm-suông-maxi-1.jpg', TRUE, NOW()),
(90, 45, 'assets/img/products/đầm-suông-maxi-2.jpg', FALSE, NOW()),
(91, 46, 'assets/img/products/giày-oxford-nam-1.jpg', TRUE, NOW()),
(92, 46, 'assets/img/products/giày-oxford-nam-2.jpg', FALSE, NOW()),
(93, 47, 'assets/img/products/giày-mule-nữ-1.jpg', TRUE, NOW()),
(94, 47, 'assets/img/products/giày-mule-nữ-2.jpg', FALSE, NOW()),
(95, 48, 'assets/img/products/ví-da-nam-nhỏ-gọn-1.jpg', TRUE, NOW()),
(96, 48, 'assets/img/products/ví-da-nam-nhỏ-gọn-2.jpg', FALSE, NOW()),
(97, 49, 'assets/img/products/găng-tay-mùa-đông-nữ-1.jpg', TRUE, NOW()),
(98, 49, 'assets/img/products/găng-tay-mùa-đông-nữ-2.jpg', FALSE, NOW()),
(99, 50, 'assets/img/products/áo-thun-nam-basic-2-1.jpg', TRUE, NOW()),
(100, 50, 'assets/img/products/áo-thun-nam-basic-2-2.jpg', FALSE, NOW());
ALTER TABLE product_images AUTO_INCREMENT = 101;

-- Orders (~10 orders)
INSERT INTO orders (id, account_id, total_amount, order_date, status) VALUES
(1, 3, 7319591, '2025-08-30 18:14:35', 'pending'),
(2, 2, 1327150, '2025-09-17 18:14:35', 'pending'),
(3, 2, 952005, '2025-09-22 18:14:35', 'paid'),
(4, 3, 3106536, '2025-10-04 18:14:35', 'paid'),
(5, 3, 7051515, '2025-09-10 18:14:35', 'pending'),
(6, 2, 212489, '2025-09-02 18:14:35', 'paid'),
(7, 3, 9280961, '2025-09-07 18:14:35', 'paid'),
(8, 2, 5710582, '2025-09-24 18:14:35', 'pending'),
(9, 2, 7327805, '2025-09-10 18:14:35', 'paid'),
(10, 2, 4688243, '2025-09-19 18:14:35', 'paid');
ALTER TABLE orders AUTO_INCREMENT = 11;

-- Order details
INSERT INTO order_details (id, order_id, product_id, quantity, unit_price) VALUES
(1, 1, 41, 3, 1899256),
(2, 1, 45, 1, 1621823),
(3, 2, 3, 2, 663575),
(4, 3, 37, 3, 317335),
(5, 4, 42, 1, 491110),
(6, 4, 30, 2, 1307713),
(7, 5, 38, 2, 1967147),
(8, 5, 28, 1, 1274551),
(9, 5, 26, 1, 1842670),
(10, 6, 8, 1, 212489),
(11, 7, 5, 2, 1569141),
(12, 7, 25, 3, 1416105),
(13, 7, 39, 2, 947182),
(14, 8, 35, 3, 246100),
(15, 8, 49, 2, 1679089),
(16, 8, 18, 1, 1614104),
(17, 9, 1, 3, 1490975),
(18, 9, 47, 2, 1427440),
(19, 10, 20, 3, 476064),
(20, 10, 41, 1, 1899256),
(21, 10, 33, 1, 1360795);
ALTER TABLE order_details AUTO_INCREMENT = 22;

-- ==========================================
-- ✅ Hoàn tất tạo cơ sở dữ liệu
-- ==========================================
