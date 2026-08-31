# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS frontend
WORKDIR /app

# Copy dependency definitions
COPY package*.json ./
RUN npm ci || npm install

# Copy source files needed for Vite build
COPY resources ./resources
COPY vite.config.js postcss.config.js* tailwind.config.js* ./
COPY public ./public

# Build production assets
RUN npm run build

# ==========================================
# Stage 2: PHP Application Container
# ==========================================
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies & utilities (including netcat for DB wait)
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    netcat-openbsd \
    bash \
    icu-data-full

# Install PHP extensions using official extension installer helper
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl \
    redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy dependency files first for better Docker layer caching
COPY composer*.json composer*.lock ./

# Install PHP dependencies
RUN composer config -g process-timeout 600 \
    && composer config -g max-parallel-http 4 \
    && (composer install --no-dev --no-scripts --no-autoloader --no-interaction || composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-source)

# Copy custom PHP configuration
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# Copy application source code
COPY . /var/www/html

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build /var/www/html/public/build

# Generate optimized autoload classmap
RUN composer dump-autoload --optimize --no-dev

# Set directory permissions for Laravel storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command
CMD ["php-fpm"]
