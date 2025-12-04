# Docker MySQL Initialization

## Overview

This directory contains the MySQL initialization script that automatically sets up the database and populates it with sample data when the Docker container is first created.

## File Structure

```
docker/mysql/
└── init.sql    # Database initialization script
```

## What's Included

### Database Schema

- **9 Tables:** accounts, categories, products, product_images, orders, order_details, cart, vouchers, voucher_usage
- **Complete relationships:** Foreign keys and constraints
- **Indexes:** Optimized for performance

### Sample Data

#### Accounts (5 users - all with password: `123456`)

| Email                 | Role     | Name          |
| --------------------- | -------- | ------------- |
| admin@shopifyt.com    | Admin    | Admin User    |
| employee@shopifyt.com | Employee | Employee User |
| customer1@example.com | Customer | Nguyễn Văn A  |
| customer2@example.com | Customer | Trần Thị B    |
| customer3@example.com | Customer | Lê Văn C      |

#### Categories (5)

- Áo T-Shirt
- Áo Sơ Mi
- Quần Jeans
- Váy Đầm
- Phụ Kiện

#### Products (30)

- 8 T-Shirts (₫150k - ₫280k)
- 6 Shirts (₫320k - ₫420k)
- 7 Jeans (₫450k - ₫520k)
- 5 Dresses (₫290k - ₫680k)
- 4 Accessories (₫120k - ₫250k)

All products include:

- Detailed descriptions
- Pricing
- Stock quantities
- Featured flags
- Placeholder images

#### Vouchers (3)

- **WELCOME10:** 10% off for new customers
- **SALE50K:** ₫50,000 off orders over ₫500k
- **VIP20:** 20% off for VIP customers

## How It Works

1. **First Time Setup:**

   - When you run `docker-compose up` for the first time
   - MySQL container starts and checks for database `shopifyt`
   - If database doesn't exist, executes `init.sql`
   - Creates all tables and inserts sample data

2. **Subsequent Runs:**
   - Database already exists in volume
   - Init script is skipped
   - Data persists across container restarts

## Usage

### Start Fresh Database

```bash
# Stop containers
docker-compose down

# Remove MySQL volume (WARNING: deletes all data)
docker volume rm shopifyt_mysql_data

# Start containers (will re-run init.sql)
docker-compose up -d
```

### Access Database

**Via phpMyAdmin:**

- URL: http://localhost:8081
- Server: mysql
- Username: root
- Password: rootpassword

**Via MySQL Client:**

```bash
docker exec -it shopifyt-mysql-1 mysql -u root -p
# Password: rootpassword
```

### Login Credentials

**Admin Panel:**

- Email: `admin@shopifyt.com`
- Password: `123456`

**Employee Account:**

- Email: `employee@shopifyt.com`
- Password: `123456`

**Customer Accounts:**

- Email: `customer1@example.com` / `customer2@example.com` / `customer3@example.com`
- Password: `123456`

## Customization

To modify sample data:

1. Edit `docker/mysql/init.sql`
2. Follow steps in "Start Fresh Database" section
3. New data will be loaded

## Important Notes

- ⚠️ **Password:** All accounts use `123456` (change in production!)
- 📸 **Images:** Using placeholder images from via.placeholder.com
- 💾 **Data Persistence:** Data stored in `mysql_data` Docker volume
- 🔄 **Re-initialization:** Only runs when database doesn't exist

## Troubleshooting

**Script not running?**

- Database might already exist
- Remove volume and restart (see "Start Fresh Database")

**Permission errors?**

- Check file permissions: `chmod 644 docker/mysql/init.sql`

**Connection refused?**

- Wait 10-20 seconds after `docker-compose up`
- MySQL needs time to initialize

## Production Deployment

Before deploying to production:

1. ✅ Change all default passwords
2. ✅ Replace placeholder images with real product images
3. ✅ Review and adjust product data
4. ✅ Update email configurations in `.env`
5. ✅ Generate new SMTP keys
6. ✅ Set up proper backup strategy
