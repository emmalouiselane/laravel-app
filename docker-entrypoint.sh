#!/bin/sh
set -e

# Wait for the database to be ready
echo "Waiting for database..."
until php artisan db:monitor > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 1
done

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Clear and cache routes and config
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the application
exec "$@"
