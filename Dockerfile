# Sử dụng image PHP chính thức có sẵn Apache
FROM php:8.2-apache

# Cài tiện ích mở rộng cần thiết cho MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Bật mod_rewrite nếu bạn có .htaccess
RUN a2enmod rewrite

# Copy toàn bộ mã nguồn vào thư mục web của Apache
COPY . /var/www/html/

# Đặt quyền để Apache đọc được file
RUN chown -R www-data:www-data /var/www/html

# Mặc định cổng 80
EXPOSE 80
