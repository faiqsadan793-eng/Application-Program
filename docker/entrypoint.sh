#!/bin/sh
set -e

echo "==> Starting Laravel Container Setup..."

# Ensure vendor folder exists (in case host directory mount shadowed it)
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "==> Vendor folder not found. Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Always sync the latest compiled frontend assets from the image to public/build
if [ -d /var/www/build-dist ]; then
    echo "==> Syncing fresh compiled assets to public/build..."
    mkdir -p /var/www/html/public/build
    cp -rf /var/www/build-dist/* /var/www/html/public/build/
fi

# Set proper permissions for storage & cache
mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Wait for database connection if DB_HOST is set
if [ -n "$DB_HOST" ]; then
    echo "==> Waiting for Database ($DB_HOST:${DB_PORT:-3306}) to be ready..."
    until nc -z -v -w30 "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null; do
        echo "Database is unavailable - sleeping 2s"
        sleep 2
    done
    echo "==> Database connection established!"
fi

# Ensure storage link exists
if [ ! -L /var/www/html/public/storage ]; then
    echo "==> Creating storage symlink..."
    php artisan storage:link || true
fi

# Optimize Laravel if in production
if [ "$APP_ENV" = "production" ]; then
    echo "==> Optimizing Laravel cache for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan event:cache || true
fi

echo "==> Container initialization finished. Starting PHP-FPM..."
exec "$@"
