#!/bin/bash

# Exit on any error
set -e
echo "🚀 Starting deployment..."

# Install system dependencies
echo "📦 Installing system dependencies..."
apt-get update
apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libpq-dev

# Install PHP and Composer
echo "🐘 Installing PHP and Composer..."
apt-get install -y php8.2 php8.2-{bcmath,ctype,fileinfo,json,mbstring,openssl,pdo_mysql,tokenizer,xml,zip,pgsql,gd}

# Install Node.js
echo "⬢ Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs

# Install Composer
echo "🎼 Installing Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Install application dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install and build assets
echo "🔨 Building assets..."
npm ci
npm run build

# Set up storage and cache
echo "💾 Setting up storage..."
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🔄 Running migrations..."
php artisan migrate --force

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
