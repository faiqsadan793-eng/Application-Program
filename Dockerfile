FROM php:8.4-fpm-alpine

# Install sistem dependensi & ekstensi PHP yang dibutuhkan Laravel & TiDB (SSL)
RUN apk update && apk add --no-cache \
    nginx \
    nodejs \
    npm \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    ca-certificates \
    icu-dev \
    oniguruma-dev \
    linux-headers

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Direktori kerja
WORKDIR /var/www/html

# Salin source code
COPY . .

# Install PHP dependensi
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependensi & build frontend assets
RUN npm ci --prefer-offline --no-audit || npm install
RUN npm run build

# Atur permission storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Konfigurasi Nginx ringkas
RUN mkdir -p /run/nginx
COPY <<EOF /etc/nginx/http.d/default.conf
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Port ekspose
EXPOSE 80

# Script start container
CMD php-fpm -D && nginx -g "daemon off;"

