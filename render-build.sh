#!/usr/bin/env bash
# Exit on error
set -e

echo "=== Memulai Build Aplikasi Laravel Klinik Lala Medicare ==="

# 1. Install Composer dependencies (production optimize)
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Install Node.js dependencies & Build Assets (Vite)
npm ci --prefer-offline --no-audit || npm install
npm run build

# 3. Jalankan Migrasi Database
echo "=== Menjalankan Database Migration ==="
php artisan migrate --force

# 4. Cache Konfigurasi & Views untuk Kecepatan Maksimal
echo "=== Mengoptimalkan Cache Laravel ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Build Selesai & Sukses! ==="

