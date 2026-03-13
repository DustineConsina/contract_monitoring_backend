#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Migrations completed successfully!"
