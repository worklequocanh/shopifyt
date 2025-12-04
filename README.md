# 🛍️ Shopifyt - E-commerce Website

Website thương mại điện tử bán quần áo và phụ kiện thời trang, được xây dựng bằng PHP MVC.

---

## 📋 Yêu Cầu Hệ Thống

- **Docker** & **Docker Compose**
- **Git**
- **Web Browser** (Chrome, Firefox, Safari)

---

## 🚀 Hướng Dẫn Chạy Project

### Bước 1: Clone Repository

```bash
git clone https://github.com/worklequocanh/shopifyt.git
cd shopifyt
```

### Bước 2: Cấu Hình Environment

```bash
# Copy file .env mẫu
cp .env.example .env

# Chỉnh sửa .env nếu cần
# - Database credentials (mặc định đã OK)
# - Email SMTP settings (nếu muốn test email)
```

### Bước 3: Khởi Động Docker

```bash
# Start tất cả services
docker-compose up -d

# Xem logs (optional)
docker-compose logs -f
```

**Lần đầu chạy:** MySQL sẽ tự động tạo database và import dữ liệu mẫu (khoảng 10-20 giây)

### Bước 4: Truy Cập Website

- **Website:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
  - Username: `root`
  - Password: `rootpassword`

### Bước 5: Đăng Nhập

**Admin:**

- Email: `admin@shopifyt.com`
- Password: `123456`

**Nhân viên:**

- Email: `employee@shopifyt.com`
- Password: `123456`

**Khách hàng:**

- Email: `customer1@example.com` (hoặc customer2, customer3)
- Password: `123456`

---

## 🔄 Các Lệnh Thường Dùng

### Dừng Project

```bash
docker-compose down
```

### Restart Project

```bash
docker-compose restart
```

### Xóa Database & Reset Từ Đầu

```bash
docker-compose down
docker volume rm shopifyt_mysql_data
docker-compose up -d
```

### Xem Logs

```bash
docker-compose logs -f php
docker-compose logs -f mysql
docker-compose logs -f nginx
```

---

## 📁 Cấu Trúc Project

```
shopifyt/
├── app/                        # Application logic
│   ├── Controllers/           # Controllers (xử lý request)
│   │   ├── Admin/            # Admin controllers
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AdminProductController.php
│   │   │   ├── AdminOrderController.php
│   │   │   └── AdminUserController.php
│   │   ├── AuthController.php        # Đăng nhập/đăng ký
│   │   ├── ProductController.php     # Trang sản phẩm
│   │   ├── CartController.php        # Giỏ hàng
│   │   ├── OrderController.php       # Đặt hàng
│   │   └── PasswordResetController.php
│   │
│   ├── Models/                # Models (tương tác database)
│   │   ├── Account.php       # User accounts
│   │   ├── Product.php       # Products
│   │   ├── Order.php         # Orders
│   │   ├── Cart.php          # Shopping cart
│   │   ├── Category.php      # Categories
│   │   ├── Voucher.php       # Discount vouchers
│   │   └── BaseModel.php     # Base model class
│   │
│   ├── Views/                 # Views (giao diện)
│   │   ├── layouts/          # Layout chung
│   │   ├── components/       # Components tái sử dụng
│   │   ├── auth/             # Đăng nhập/đăng ký
│   │   ├── products/         # Trang sản phẩm
│   │   ├── cart/             # Giỏ hàng
│   │   ├── checkout/         # Thanh toán
│   │   ├── order/            # Đơn hàng
│   │   ├── account/          # Tài khoản
│   │   ├── admin/            # Admin panel
│   │   └── emails/           # Email templates
│   │
│   ├── Core/                  # Core framework files
│   │   ├── Router.php        # Routing system
│   │   ├── helpers.php       # Helper functions
│   │   └── Permission.php    # Phân quyền
│   │
│   ├── Services/             # Services
│   │   └── EmailService.php  # Email service (PHPMailer)
│   │
│   └── Helpers/              # Helper classes
│       └── email_helpers.php # Email helper functions
│
├── public/                    # Public files
│   ├── index.php             # Entry point
│   ├── assets/               # CSS, JS, images
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── test_email.php        # Email testing tool
│
├── config/                    # Configuration files
│   └── database.php          # Database config
│
├── docker/                    # Docker configuration
│   ├── nginx/                # Nginx config
│   ├── php/                  # PHP config
│   └── mysql/                # MySQL initialization
│       └── init.sql          # Database schema + sample data
│
├── vendor/                    # Composer dependencies
│   └── phpmailer/            # PHPMailer library
│
├── docker-compose.yml         # Docker Compose configuration
├── composer.json              # PHP dependencies
├── .env                       # Environment variables
└── README.md                 # This file
```

---

## 🏗️ Kiến Trúc MVC

### Model (app/Models/)

- Tương tác với database
- Business logic
- Data validation

**Ví dụ:**

```php
// app/Models/Product.php
$products = $productModel->getAll($limit, $offset, $categoryId);
```

### View (app/Views/)

- Hiển thị giao diện
- PHP templates
- HTML/CSS/JavaScript

**Ví dụ:**

```php
// app/Views/products/index.php
<?php foreach ($products as $product): ?>
    <div class="product-card">...</div>
<?php endforeach; ?>
```

### Controller (app/Controllers/)

- Nhận request từ user
- Gọi Model để lấy dữ liệu
- Trả dữ liệu về View

**Ví dụ:**

```php
// app/Controllers/ProductController.php
public function index() {
    $products = $this->productModel->getAll();
    $this->view('products/index', ['products' => $products]);
}
```

---

## 🔐 Phân Quyền

### Roles (Vai Trò)

| Role         | Quyền Hạn                           |
| ------------ | ----------------------------------- |
| **Admin**    | Toàn quyền quản lý hệ thống         |
| **Employee** | Quản lý sản phẩm, đơn hàng, voucher |
| **Customer** | Mua sắm, xem đơn hàng               |
| **Guest**    | Xem sản phẩm (không mua được)       |

### Permissions System

File: `app/Core/Permission.php`

```php
// Kiểm tra quyền trong controller
$this->requirePermission(Permission::MANAGE_PRODUCTS);
```

---

## 📧 Email System

### Cấu Hình Email (Brevo SMTP)

1. **Đăng ký Brevo:** https://app.brevo.com/
2. **Tạo SMTP Key:**
   - Settings → SMTP & API
   - Create new SMTP key
3. **Cập nhật .env:**
   ```ini
   MAIL_HOST=smtp-relay.brevo.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@example.com
   MAIL_PASSWORD=xsmtpsib-your-smtp-key
   MAIL_FROM_ADDRESS=your-email@example.com
   ```

### Test Email

Truy cập: http://localhost:8080/test_email.php

### Email Features

- ✉️ Account verification
- 🔑 Password reset
- 📦 Order confirmation
- 📬 Order status updates

---

## 🗄️ Database

### Tables (9 bảng)

| Table            | Mô Tả                   |
| ---------------- | ----------------------- |
| `accounts`       | Tài khoản người dùng    |
| `categories`     | Danh mục sản phẩm       |
| `products`       | Sản phẩm                |
| `product_images` | Hình ảnh sản phẩm       |
| `orders`         | Đơn hàng                |
| `order_details`  | Chi tiết đơn hàng       |
| `cart`           | Giỏ hàng                |
| `vouchers`       | Mã giảm giá             |
| `voucher_usage`  | Lịch sử sử dụng voucher |

### Sample Data (Dữ liệu mẫu)

- **30 sản phẩm** (T-shirts, Shirts, Jeans, Dresses, Accessories)
- **5 tài khoản** (1 admin, 1 nhân viên, 3 khách hàng)
- **5 danh mục**
- **3 voucher**

---

## 🛠️ Technologies

### Backend

- **PHP 8.0+**
- **MySQL 8.0**
- **Composer** (dependency management)

### Frontend

- **HTML/CSS**
- **JavaScript (Vanilla)**
- **Tailwind CSS** (utility-first CSS)
- **Bootstrap 5** (components)

### Libraries

- **PHPMailer** - Email sending
- **Brevo (Sendinblue)** - SMTP service

### DevOps

- **Docker** - Containerization
- **Docker Compose** - Multi-container orchestration
- **Nginx** - Web server
- **Git** - Version control

---

## 🐛 Troubleshooting

### Database không tạo?

```bash
# Check MySQL logs
docker-compose logs mysql

# Xem database hiện tại
docker exec shopifyt-mysql-1 mysql -u root -prootpassword -e "SHOW DATABASES;"
```

### Email không gửi được?

1. Check SMTP credentials trong `.env`
2. Test tại: http://localhost:8080/test_email.php
3. Check error logs: `docker-compose logs php`

### Port 8080 đã được sử dụng?

```bash
# Đổi port trong docker-compose.yml
ports:
  - "8081:80"  # Thay 8080 thành 8081
```

### Permission denied khi chạy Docker?

```bash
# Linux/Mac: thêm user vào docker group
sudo usermod -aG docker $USER
newgrp docker
```

---

## 📱 Browser Support

- ✅ Chrome (recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ⚠️ IE11 (limited support)

---

## 🤝 Contributing

Project này là bài tập học tập. Nếu muốn đóng góp:

1. Fork repository
2. Tạo branch mới: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Tạo Pull Request

---

## 📄 License

Educational project - free to use for learning purposes.

---

## 👥 Contact

- **GitHub:** [@worklequocanh](https://github.com/worklequocanh)
- **Repository:** [shopifyt](https://github.com/worklequocanh/shopifyt)

---

## 📚 Documentation

- [FEATURES.md](FEATURES.md) - Danh sách tính năng chi tiết
- [docker/mysql/README.md](docker/mysql/README.md) - Hướng dẫn database init
- [Email Testing Guide](.gemini/antigravity/brain/*/email_testing_guide.md)

---

**Happy Coding! 🚀**
