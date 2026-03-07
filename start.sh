#!/bin/bash
set -e

echo "Setting up environment variables..."

# Map Railway variables to Laravel variables if they exist
if [ -n "$MYSQLHOST" ]; then
  export DB_HOST=$MYSQLHOST
fi

if [ -n "$MYSQLPORT" ]; then
  export DB_PORT=$MYSQLPORT
fi

if [ -n "$MYSQLDATABASE" ]; then
  export DB_DATABASE=$MYSQLDATABASE
fi

if [ -n "$MYSQLUSER" ]; then
  export DB_USERNAME=$MYSQLUSER
fi

if [ -n "$MYSQLPASSWORD" ]; then
  export DB_PASSWORD=$MYSQLPASSWORD
fi

# If no Railway variables, use defaults from .env
export DB_HOST=${DB_HOST:-127.0.0.1}
export DB_PORT=${DB_PORT:-3306}
export DB_DATABASE=${DB_DATABASE:-pfda_contract_db}
export DB_USERNAME=${DB_USERNAME:-root}
export DB_PASSWORD=${DB_PASSWORD:-}

echo "Database Host: $DB_HOST"
echo "Database Port: $DB_PORT"
echo "Database Name: $DB_DATABASE"
echo "Starting Laravel application..."

# Ensure storage directories exist and are writable
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Clear old caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Re-cache configuration
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start the server
echo "Starting PHP server on port $PORT"
exec php artisan serve --host=0.0.0.0 --port=$PORT
