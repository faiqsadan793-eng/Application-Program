#!/bin/sh
set -e

echo "==> Starting Laravel Container Setup..."

# Set proper permissions for storage & cache
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
