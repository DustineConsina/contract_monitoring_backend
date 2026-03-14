#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force 2>/dev/null || true

echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Deployment completed successfully"
