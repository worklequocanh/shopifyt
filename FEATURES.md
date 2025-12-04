# 🎯 Danh Sách Tính Năng - Shopifyt

Hệ thống thương mại điện tử hoàn chỉnh với đầy đủ tính năng cho người dùng và quản trị viên.

---

## 👤 TÍNH NĂNG NGƯỜI DÙNG (CUSTOMER)

### 🔐 1. Xác Thực & Tài Khoản

#### 1.1. Đăng Ký Tài Khoản

**Mô tả:** Tạo tài khoản mới để mua sắm

**Cách hoạt động:**

1. Nhập thông tin: Tên, Email, SĐT, Địa chỉ, Mật khẩu
2. Hệ thống validate dữ liệu
3. Mật khẩu được mã hóa (bcrypt)
4. Gửi email xác nhận tài khoản
5. User click link trong email để kích hoạt

**Yêu cầu:**

- Email chưa tồn tại
- Mật khẩu tối thiểu 6 ký tự

#### 1.2. Đăng Nhập

**Mô tả:** Truy cập vào tài khoản

**Cách hoạt động:**

1. Nhập Email và Mật khẩu
2. Hệ thống kiểm tra thông tin
3. **Chặn nếu email chưa xác nhận**
4. Tạo session lưu trạng thái đăng nhập
5. Chuyển đến trang sản phẩm

**Trường hợp:**

- ✅ Email đã xác nhận → Đăng nhập thành công
- ❌ Email chưa xác nhận → Redirect đến trang "Xác nhận email"
- ❌ Sai mật khẩu → Hiện lỗi

#### 1.3. Xác Nhận Email

**Mô tả:** Kích hoạt tài khoản qua email

**Cách hoạt động:**

1. Sau khi đăng ký, nhận email với link xác nhận
2. Click vào link (token có hiệu lực 24h)
3. Hệ thống kiểm tra token hợp lệ
4. Đánh dấu `email_verified = 1`
5. Có thể đăng nhập

**Features:**

- Nút "Gửi lại email" nếu chưa nhận được
- Link hết hạn sau 24h
- Token bảo mật (hashed)

#### 1.4. Quên Mật Khẩu

**Mô tả:** Đặt lại mật khẩu khi quên

**Cách hoạt động:**

1. Click "Forgot Password?" trên trang login
2. Nhập email tài khoản
3. Nhận email có link reset (token 1h)
4. Nhập mật khẩu mới
5. Mật khẩu được cập nhật, token bị xóa

**Bảo mật:**

- Token chỉ dùng 1 lần
- Hết hạn sau 1 giờ
- Email có thể gửi lại sau 5 phút

#### 1.5. Quản Lý Tài Khoản

**Mô tả:** Xem và cập nhật thông tin cá nhân

**Cách hoạt động:**

1. Vào trang "Account Info"
2. Hiển thị: Tên, Email, SĐT, Địa chỉ
3. Cho phép chỉnh sửa thông tin
4. Đổi mật khẩu (yêu cầu mật khẩu cũ)

---

### 🛍️ 2. Mua Sắm

#### 2.1. Xem Danh Sách Sản Phẩm

**Mô tả:** Browse tất cả sản phẩm có sẵn

**Cách hoạt động:**

1. Hiển thị grid sản phẩm (12 items/page)
2. Mỗi card hiển thị: Hình, Tên, Giá, Stock
3. Phân trang tự động
4. Lọc theo danh mục
5. Tìm kiếm theo tên

**Features:**

- Pagination
- Category filter
- Search bar
- Responsive grid layout

#### 2.2. Xem Chi Tiết Sản Phẩm

**Mô tả:** Xem thông tin chi tiết 1 sản phẩm

**Cách hoạt động:**

1. Click vào sản phẩm
2. Hiển thị:
   - **Gallery ảnh** (main image + thumbnails)
   - Tên, giá, mô tả
   - Số lượng còn lại
   - Chọn số lượng mua
3. Click thumbnail để đổi ảnh chính
4. Nút "Add to Cart"

**New:** Multi-image gallery với thumbnails

#### 2.3. Thêm Vào Giỏ Hàng

**Mô tả:** Lưu sản phẩm muốn mua

**Cách hoạt động:**

1. Chọn số lượng mong muốn
2. Click "Add to Cart"
3. Kiểm tra stock có đủ không
4. Thêm vào bảng `cart`
5. Nếu đã có → tăng số lượng
6. Toast notification thành công

**Validation:**

- Số lượng > 0
- Số lượng <= stock hiện có
- Phải đăng nhập

#### 2.4. Quản Lý Giỏ Hàng

**Mô tả:** Xem và chỉnh sửa giỏ hàng

**Cách hoạt động:**

1. Hiển thị danh sách sản phẩm trong giỏ
2. Mỗi item: Hình, Tên, Giá, Số lượng, Tổng
3. Tăng/giảm số lượng (AJAX)
4. Xóa sản phẩm
5. Tính tổng tiền tự động
6. Nút "Proceed to Checkout"

**Features:**

- Update quantity realtime
- Remove items
- Calculate total
- Apply voucher

#### 2.5. Áp Dụng Mã Giảm Giá

**Mô tả:** Sử dụng voucher để giảm giá

**Cách hoạt động:**

1. Nhập mã voucher
2. Hệ thống validate:
   - Mã có tồn tại?
   - Còn hạn sử dụng?
   - Đủ giá trị đơn hàng tối thiểu?
   - Còn lượt sử dụng?
3. Tính số tiền giảm:
   - **Percentage:** `total * (discount_value / 100)`
   - **Fixed:** `discount_value`
4. Giới hạn max_discount (nếu có)
5. Hiển thị số tiền giảm
6. Tổng tiền = Total - Discount

**Logic:**

```
Giảm 10% (max ₫100k) cho đơn ₫500k:
→ ₫500k × 10% = ₫50k
→ Final: ₫450k

Giảm 20% (max ₫100k) cho đơn ₫1M:
→ ₫1M × 20% = ₫200k (nhưng max ₫100k)
→ Final: ₫900k
```

#### 2.6. Đặt Hàng (Checkout)

**Mô tả:** Hoàn tất đơn hàng

**Cách hoạt động:**

1. Nhập/xác nhận thông tin giao hàng
2. Review đơn hàng + voucher (nếu có)
3. Click "Place Order"
4. **Hệ thống:**
   - Tạo record trong `orders`
   - Tạo `order_details` cho từng item
   - **Trừ stock ngay lập tức** ⚡
   - Track voucher usage
   - Xóa items khỏi cart
   - Gửi email xác nhận đơn hàng
5. Redirect đến success page

**Transaction:** Tất cả bước trong 1 transaction, nếu lỗi → rollback

**Email sent:** Order confirmation với chi tiết

#### 2.7. Xem Đơn Hàng

**Mô tả:** Theo dõi lịch sử và trạng thái đơn hàng

**Cách hoạt động:**

1. Vào "My Orders"
2. Hiển thị danh sách đơn hàng
3. Mỗi đơn: Mã, Ngày, Tổng tiền, Trạng thái
4. Click để xem chi tiết:
   - Thông tin giao hàng
   - Danh sách sản phẩm
   - Giá, voucher, total
   - Timeline trạng thái

**Statuses:**

- 🟡 **Pending:** Chờ xử lý
- 🟢 **Accepted:** Đã chấp nhận
- 🔴 **Cancelled:** Đã hủy
- 📦 **Delivered:** Đã giao (future)

---

### 📧 3. Email Notifications

#### 3.1. Email Xác Nhận Đơn Hàng

**Khi nào:** Ngay sau khi đặt hàng thành công

**Nội dung:**

- Mã đơn hàng
- Danh sách sản phẩm
- Giá, giảm giá, tổng
- Thông tin giao hàng
- Link xem chi tiết

#### 3.2. Email Cập Nhật Trạng Thái

**Khi nào:** Admin thay đổi trạng thái đơn

**Trường hợp:**

- **Accepted:** "Đơn hàng đã được chấp nhận"
- **Cancelled:** "Đơn hàng đã bị hủy"

**Nội dung:**

- Trạng thái mới
- Mã đơn hàng
- Tổng tiền
- Link xem chi tiết

---

## 👨‍💼 TÍNH NĂNG ADMIN & EMPLOYEE

### 📊 4. Dashboard (Admin & Employee)

**Mô tả:** Tổng quan hệ thống

**Hiển thị:**

- 📈 **Quick Stats:**

  - Tổng doanh thu
  - Tổng đơn hàng
  - Đơn chờ xử lý
  - Tổng sản phẩm
  - Tổng khách hàng

- 📊 **Charts:**

  - Doanh thu theo tháng (biểu đồ cột)
  - Top 10 sản phẩm bán chạy
  - Phân phối trạng thái đơn hàng (pie chart)

- 📋 **Tables:**
  - Đơn hàng gần đây
  - Sản phẩm sắp hết hàng
  - Khách hàng mua nhiều nhất

**Cách hoạt động:**

- Tự động query database
- Charts render với Chart.js
- Realtime data

---

### 📦 5. Quản Lý Sản Phẩm (Admin & Employee)

#### 5.1. Danh Sách Sản Phẩm

**Mô tả:** Xem tất cả sản phẩm

**Features:**

- Table với: ID, Ảnh, Tên, Giá, Stock, Danh mục
- Pagination
- Search
- Filter by category
- Sort by date/price
- Actions: Edit, Delete

#### 5.2. Thêm Sản Phẩm

**Cách hoạt động:**

1. Form nhập: Tên, Mô tả, Giá, Stock, Category
2. Upload ảnh (single/multiple)
3. Chọn "Featured" (hiện trang chủ)
4. Submit → Insert vào `products`
5. Upload ảnh → Insert vào `product_images`

**Validation:**

- Tên không trống
- Giá > 0
- Stock >= 0

#### 5.3. Sửa Sản Phẩm

**Cách hoạt động:**

1. Load thông tin hiện tại
2. Cho phép sửa tất cả field
3. Upload ảnh mới (optional)
4. Update database

#### 5.4. Xóa Sản Phẩm

**Cách hoạt động:**

1. Confirm xóa
2. Soft delete: `is_active = 0`
3. Không xóa vật lý để giữ lịch sử đơn hàng

---

### 📋 6. Quản Lý Đơn Hàng (Admin & Employee)

#### 6.1. Danh Sách Đơn Hàng

**Features:**

- Table: Mã, Khách hàng, Ngày, Tổng tiền, Trạng thái
- Filter by status
- Search by order ID/customer
- Pagination

#### 6.2. Chi Tiết Đơn Hàng

**Hiển thị:**

- Thông tin khách hàng
- Địa chỉ giao hàng
- Danh sách sản phẩm
- Tổng tiền, voucher, discount
- Timeline trạng thái

#### 6.3. Cập Nhật Trạng Thái

**Mô tả:** Thay đổi trạng thái đơn hàng

**Logic Stock Management:**

| Từ Status | Đến Status | Stock Thay Đổi | Giải Thích         |
| --------- | ---------- | -------------- | ------------------ |
| Pending   | Accepted   | **Không đổi**  | Đã trừ lúc tạo đơn |
| Pending   | Cancelled  | **+qty**       | Hoàn lại stock     |
| Accepted  | Cancelled  | **+qty**       | Hoàn lại stock     |
| Cancelled | Accepted   | **-qty**       | Trừ lại stock      |

**Cách hoạt động:**

1. Admin chọn status mới
2. Hệ thống kiểm tra logic
3. **Update stock tương ứng:**
   - Cancel → Tăng stock
   - Reactivate → Giảm stock
4. Update `orders.status`
5. **Gửi email thông báo** cho khách

**Transaction:** Đảm bảo consistency

---

### 👥 7. Quản Lý Người Dùng (Admin Only)

#### 7.1. Danh Sách Users

**Features:**

- Table: ID, Tên, Email, Role, Trạng thái
- Filter by role (Admin/Employee/Customer)
- Search
- Actions: Edit, Activate/Deactivate

#### 7.2. Thêm User

**Cách hoạt động:**

1. Form: Tên, Email, Password, Role
2. Validate email unique
3. Hash password
4. Insert vào `accounts`

#### 7.3. Sửa User

**Cho phép:**

- Đổi tên, SĐT, địa chỉ
- Đổi role
- Reset password
- Kích hoạt/vô hiệu hóa

#### 7.4. Phân Quyền

**Roles:**

- **Admin:** Full access
- **Employee:** Quản lý products, orders, vouchers
- **Customer:** Chỉ mua sắm

**Implementation:** `Permission` class check quyền

---

### 🎫 8. Quản Lý Voucher (Admin & Employee)

#### 8.1. Danh Sách Voucher

**Hiển thị:**

- Code, Tên, Loại, Giá trị
- Điều kiện (min order, max discount)
- Số lượt dùng / Giới hạn
- Ngày bắt đầu / kết thúc
- Trạng thái

#### 8.2. Thêm Voucher

**Form:**

- Code (unique)
- Tên voucher
- Loại: Percentage / Fixed
- Giá trị giảm
- Điều kiện:
  - Giá trị đơn tối thiểu
  - Giảm tối đa
  - Giới hạn lượt dùng
- Thời gian: Start date - End date
- Trạng thái: Active/Inactive

**Validation:**

- Code unique
- Percentage ≤ 100
- Fixed > 0
- Start date < End date

#### 8.3. Sửa/Xóa Voucher

**Sửa:** Update thông tin

**Xóa:** Soft delete (is_active = 0)

#### 8.4. Lịch Sử Sử Dụng

**Hiển thị:**

- Voucher nào được dùng
- Đơn hàng nào
- Khách hàng nào
- Số tiền giảm
- Thời gian sử dụng

**Table:** `voucher_usage`

---

### 📈 9. Báo Cáo (Admin Only)

#### 9.1. Báo Cáo Doanh Thu

**Metrics:**

- Tổng doanh thu
- Doanh thu theo tháng
- Doanh thu theo danh mục
- Average order value
- Tổng giảm giá (vouchers)

**Charts:**

- Line chart: Revenue by month
- Bar chart: Revenue by category

#### 9.2. Báo Cáo Sản Phẩm

**Metrics:**

- Top selling products
- Sản phẩm bán chậm
- Sản phẩm hết hàng
- Revenue per product

#### 9.3. Báo Cáo Khách Hàng

**Metrics:**

- Tổng khách hàng
- Khách hàng active (đã mua)
- Customer lifetime value
- Top customers

---

## 🔄 Flow Hệ Thống

### Customer Journey

```
1. Đăng ký → Email xác nhận
2. Click link xác nhận → Account active
3. Login → Browse products
4. Add to cart → Quản lý giỏ hàng
5. Apply voucher (optional)
6. Checkout → Nhập địa chỉ
7. Place order → Stock trừ ngay ⚡
8. Email confirmation sent
9. Theo dõi đơn hàng
10. Admin accept → Email thông báo
```

### Stock Management Flow

```
🛒 Tạo đơn hàng:
   Stock: 100 → 97 (-3) ⚡

📦 Pending → Accepted:
   Stock: 97 → 97 (no change)

❌ Accepted → Cancelled:
   Stock: 97 → 100 (+3 hoàn lại)

🔄 Cancelled → Accepted:
   Stock: 100 → 97 (-3 lại)
```

### Email Flow

```
📧 Registration → Verification email
📧 Password reset → Reset link email
📧 Order placed → Confirmation email
📧 Status changed → Update email
```

---

## 🎨 UI/UX Features

### User Side

- ✅ Responsive design (mobile-friendly)
- ✅ Toast notifications
- ✅ Image gallery với thumbnails
- ✅ AJAX cart updates
- ✅ Loading states
- ✅ Form validation

### Admin Side

- ✅ Dashboard với charts
- ✅ Data tables với pagination
- ✅ Modal forms
- ✅ Inline editing
- ✅ Bulk actions
- ✅ Export reports

---

## 🔒 Security Features

### Authentication

- ✅ Bcrypt password hashing
- ✅ Email verification required
- ✅ Session management
- ✅ CSRF protection (future)

### Authorization

- ✅ Role-based access control
- ✅ Permission checks
- ✅ Admin/Employee separation

### Data Protection

- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ Input validation
- ✅ Secure token generation

### Email Security

- ✅ Token expiration
- ✅ One-time use tokens
- ✅ Rate limiting (future)

---

## 📝 Business Rules

### Stock Management

1. Stock trừ **ngay khi tạo đơn**, không chờ accept
2. Cancel order → Hoàn stock
3. Reactivate cancelled → Trừ stock lại
4. Không cho đặt nếu stock không đủ

### Voucher Rules

1. Check min order value
2. Check max discount limit
3. Check usage limit
4. Check expiry dates
5. Track usage in `voucher_usage`
6. Increment `used_count`

### Order Rules

1. Customer có thể xem order của mình
2. Admin/Employee xem tất cả orders
3. Status flow: Pending → Accepted/Cancelled
4. Cannot delete orders (data integrity)

---

## 🚀 Performance Optimizations

### Database

- ✅ Indexes on foreign keys
- ✅ Indexes on frequently queried columns
- ✅ Query optimization
- ✅ Row locking (`FOR UPDATE`) khi đặt hàng

### Frontend

- ✅ Image optimization (via placeholders)
- ✅ CSS/JS minification (future)
- ✅ Lazy loading images (future)

### Backend

- ✅ PDO prepared statements
- ✅ Single database connection per request
- ✅ Efficient queries (avoid N+1)

---

**Tổng số tính năng: 30+ features**

**Tech Stack:** PHP MVC, MySQL, Docker, Tailwind CSS, Bootstrap, PHPMailer

**Email Service:** Brevo (Sendinblue) SMTP

---

Xem thêm: [README.md](README.md) - Hướng dẫn cài đặt và chạy project
