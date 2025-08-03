#!/bin/bash

# Exit on any error
set -e

echo "Deploying application..."

# Change to the project directory
cd /var/www/html/laravel-app

# Get the latest changes from the repository
git fetch origin
git reset --hard origin/main

# Install PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install NPM dependencies
npm install
npm run build

# Set up storage and cache
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Set proper permissions
chown -R www-data:www-data /var/www/html/laravel-app
chmod -R 755 /var/www/html/laravel-app/storage
chmod -R 755 /var/www/html/laravel-app/bootstrap/cache

echo "Application deployed successfully!"
