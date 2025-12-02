FROM php:8.1-fpm-alpine

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create working directory
WORKDIR /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html