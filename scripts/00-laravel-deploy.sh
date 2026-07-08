#!/usr/bin/env bash
set -e

echo "Running composer install..."
composer install --no-dev --working-dir=/var/www/html --optimize-autoloader

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Linking public storage (for uploaded menu item images)..."
php artisan storage:link || true

echo "Running migrations..."
php artisan migrate --force

# Uncomment the line below ONLY for your very first deploy, to seed the
# admin user and starter menu, then remove it again so redeploys don't
# reset your data.
# php artisan db:seed --force
