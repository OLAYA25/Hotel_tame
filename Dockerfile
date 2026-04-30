# Hotel Tame PMS Backend

FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    gd \
    intl \
    opcache \
    bcmath

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application files
COPY . .

# Create logs directory
RUN mkdir -p logs storage/framework/storage/logs

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 logs storage

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
