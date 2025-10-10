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

          -- ==========================================
          -- 6️⃣ Dữ liệu mẫu (demo)
          -- ==========================================
          INSERT INTO accounts
            (name, password, email, role, position, salary)
          VALUES
            ('Admin User', '123456', 'admin@example.com', 'admin', 'System Admin', 0),
            ('John Employee', '123456', 'john@example.com', 'employee', 'Sales Staff', 700.00),
            ('Jane Customer', '123456', 'jane@example.com', 'customer', NULL, 0);

          INSERT INTO products
            (name, description, price, stock, category)
          VALUES
            ('Áo thun nam', 'Chất liệu cotton cao cấp', 250000, 100, 'Thời trang'),
            ('Giày sneaker', 'Thiết kế năng động', 850000, 50, 'Giày dép');

          INSERT INTO product_images
            (product_id, image_url, is_main)
          VALUES
            (1, 'assets/img/products/ao-thun-1.jpg', TRUE),
            (1, 'assets/img/products/ao-thun-2.jpg', FALSE),
            (2, 'assets/img/products/giay-1.jpg', TRUE);

          INSERT INTO orders
            (account_id, total_amount, status)
          VALUES
            (3, 1100000, 'paid');

          INSERT INTO order_details
            (order_id, product_id, quantity, unit_price)
          VALUES
            (1, 1, 2, 250000),
            (1, 2, 1, 850000);

-- ==========================================
-- ✅ Hoàn tất tạo cơ sở dữ liệu
-- ==========================================
