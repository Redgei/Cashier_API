#!/bin/sh
set -e

echo "Clearing Laravel caches..."
php artisan optimize:clear || true
echo "Running migrations..."
php artisan migrate --force

echo "Resetting OPcache..."
php -r "if (function_exists('opcache_reset')) { opcache_reset(); }"

echo "Starting PHP-FPM..."
exec php-fpm

