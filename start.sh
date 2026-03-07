#!/bin/bash
set -e

echo "Starting Laravel application..."

# Ensure storage directories exist and are writable
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Run artisan commands
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the server
echo "Starting PHP server on port $PORT"
exec php artisan serve --host=0.0.0.0 --port=$PORT
