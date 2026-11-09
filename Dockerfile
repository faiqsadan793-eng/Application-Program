# Gunakan image resmi PHP 8.4 CLI yang sangat ringan (Debian Slim)
FROM php:8.4-cli

# Install system dependencies dan NodeJS untuk compile assets
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nodejs \
    npm \
    ca-certificates \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip bcmath pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Salin semua kode aplikasi
COPY . .

# Install PHP dependensi (tanpa dev, dioptimalkan untuk production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install node dependencies dan build file CSS/JS Vite
RUN npm ci --prefer-offline --no-audit || npm install
RUN npm run build

# Atur permissions storage dan bootstrap/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port (default port 8000 untuk Laravel artisan serve)
EXPOSE 8000

# Start command: migrate database dan jalankan server pada $PORT dinamis Railway
CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=\${PORT:-8000}"

