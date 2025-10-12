# ShopifyT - E-commerce Website

## 🚀 Tính năng chính

- **Trang chủ**: Hero section, features, categories, sản phẩm nổi bật, hot deals, testimonials, newsletter
- **Sản phẩm**: Danh sách sản phẩm với filter, search, pagination
- **Chi tiết sản phẩm**: Gallery hình ảnh, mô tả, đánh giá, sản phẩm liên quan
- **Giỏ hàng**: Quản lý sản phẩm, cập nhật số lượng, tính tổng tiền
- **Thanh toán**: Form đặt hàng với validation, phương thức thanh toán
- **Xác nhận đơn hàng**: Thông tin đơn hàng, bước tiếp theo

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP 8+ với PDO
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Inter)
- **Styling**: Custom CSS với CSS Variables

## 📁 Cấu trúc thư mục

```
shopifyt/
├── assets/
│   ├── css/
│   │   └── style.css          # CSS chính
│   ├── js/
│   │   └── script.js          # JavaScript chung
│   └── img/                   # Hình ảnh
├── includes/
│   ├── config.php             # Cấu hình database
│   ├── layouts/
│   │   ├── header.php         # Header chung
│   │   └── footer.php         # Footer chung
│   ├── auth.php               # Xác thực
│   └── functions.php          # Functions chung
├── index.php                  # Trang chủ
├── products.php               # Danh sách sản phẩm
├── product-detail.php         # Chi tiết sản phẩm
├── shopping-cart.php          # Giỏ hàng
├── checkout.php               # Thanh toán
├── checkout-success.php       # Xác nhận đơn hàng
├── database.sql               # Schema database
└── docker-compose.yml         # Docker setup
```

## 🗄️ Database Schema

### Bảng `accounts`

- `id`: Primary key
- `name`: Tên người dùng
- `email`: Email (unique)
- `password`: Mật khẩu
- `phone`: Số điện thoại
- `address`: Địa chỉ
- `role`: Vai trò (admin, employee, customer)

### Bảng `products`

- `id`: Primary key
- `name`: Tên sản phẩm
- `description`: Mô tả
- `price`: Giá
- `stock`: Số lượng tồn kho
- `category`: Danh mục

### Bảng `product_images`

- `id`: Primary key
- `product_id`: Foreign key
- `image_url`: URL hình ảnh
- `is_main`: Hình ảnh chính

### Bảng `orders`

- `id`: Primary key
- `account_id`: Foreign key
- `total_amount`: Tổng tiền
- `status`: Trạng thái đơn hàng

### Bảng `order_details`

- `id`: Primary key
- `order_id`: Foreign key
- `product_id`: Foreign key
- `quantity`: Số lượng
- `unit_price`: Giá đơn vị

## 🎨 CSS Architecture

### CSS Variables

```css
:root {
  --primary-color: #2563eb;
  --secondary-color: #1e40af;
  --accent-color: #f59e0b;
  --success-color: #10b981;
  --danger-color: #ef4444;
  --gray-100: #f1f5f9;
  --gray-600: #475569;
  --gray-800: #1e293b;
  --white: #ffffff;
  --black: #000000;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --border-radius: 8px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Responsive Design

- **Mobile-first**: Thiết kế ưu tiên mobile
- **Breakpoints**: 768px, 992px, 1200px
- **Grid System**: CSS Grid và Flexbox
- **Typography**: Responsive font sizes

## 🔧 Cài đặt và chạy

### 1. Clone repository

```bash
git clone <repository-url>
cd shopifyt
```

### 2. Cấu hình database

```bash
# Tạo database
mysql -u root -p
CREATE DATABASE shopifyt;
USE shopifyt;

# Import schema
mysql -u root -p shopifyt < database.sql
```

### 3. Cấu hình environment

Tạo file `.env` trong thư mục gốc:

```env
DB_HOST=localhost
DB_NAME=shopifyt
DB_USER=root
DB_PASS=your_password
DB_CHARSET=utf8mb4
```

### 4. Chạy với Docker (khuyến nghị)

```bash
docker-compose up -d
```

### 5. Truy cập website

- **URL**: http://localhost:8080
- **Admin**: http://localhost:8080/admin

## 📱 Responsive Features

### Mobile (< 768px)

- Navigation collapse
- Single column layout
- Touch-friendly buttons
- Optimized images

### Tablet (768px - 992px)

- Two column layout
- Larger touch targets
- Sidebar collapse

### Desktop (> 992px)

- Full layout
- Hover effects
- Multi-column grids

## 🎯 JavaScript Features

### Common Functions

- `addToCart(productId)`: Thêm sản phẩm vào giỏ
- `toggleWishlist(productId)`: Toggle wishlist
- Back to top button
- Search functionality
- Scroll animations

### Page-specific Scripts

- Newsletter subscription
- Form validation
- Image gallery
- Quantity controls
- Payment method selection

## 🔒 Security Features

- **SQL Injection**: Sử dụng PDO Prepared Statements
- **XSS Protection**: `htmlspecialchars()` cho tất cả output
- **CSRF Protection**: Session-based validation
- **Input Validation**: Server-side validation
- **Password Hashing**: `password_hash()` và `password_verify()`

## 🚀 Performance Optimizations

- **CSS Minification**: Minified CSS
- **Image Optimization**: Responsive images
- **Lazy Loading**: Intersection Observer API
- **Caching**: Browser caching headers
- **Database Indexing**: Proper indexes

## 📊 SEO Features

- **Meta Tags**: Dynamic meta descriptions
- **Structured Data**: JSON-LD markup
- **Sitemap**: XML sitemap
- **Robots.txt**: Search engine directives
- **URL Structure**: Clean, SEO-friendly URLs

## 🧪 Testing

### Manual Testing

1. **Cross-browser**: Chrome, Firefox, Safari, Edge
2. **Responsive**: Mobile, tablet, desktop
3. **Functionality**: All features working
4. **Performance**: Page load times

### Automated Testing

```bash
# PHP syntax check
php -l *.php

# CSS validation
# Use online CSS validator

# JavaScript validation
# Use ESLint or similar
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**

   - Kiểm tra file `.env`
   - Kiểm tra MySQL service
   - Kiểm tra credentials

2. **CSS Not Loading**

   - Kiểm tra đường dẫn file CSS
   - Clear browser cache
   - Kiểm tra file permissions

3. **JavaScript Errors**

   - Kiểm tra browser console
   - Kiểm tra file script.js
   - Kiểm tra jQuery/Font Awesome loading

4. **Images Not Displaying**
   - Kiểm tra đường dẫn hình ảnh
   - Kiểm tra file permissions
   - Kiểm tra file tồn tại

## 📈 Future Enhancements

- [ ] User authentication system
- [ ] Admin dashboard
- [ ] Payment gateway integration
- [ ] Email notifications
- [ ] Product reviews system
- [ ] Wishlist functionality
- [ ] Order tracking
- [ ] Multi-language support
- [ ] API endpoints
- [ ] Mobile app

## 📄 License

MIT License - Xem file LICENSE để biết thêm chi tiết.

## 👥 Contributing

1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Tạo Pull Request

## 📞 Support

- **Email**: support@shopifyt.com
- **Documentation**: [Wiki](link-to-wiki)
- **Issues**: [GitHub Issues](link-to-issues)

---

**ShopifyT** - Thương mại điện tử hàng đầu Việt Nam 🚀
