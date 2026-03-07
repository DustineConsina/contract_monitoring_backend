#!/bin/bash
set -e

echo "=== Starting PFDA Contract Monitoring Backend ==="

# Set production environment
export APP_ENV=${APP_ENV:-production}
export APP_DEBUG=${APP_DEBUG:-true}

# Set APP_KEY - Critical for Laravel encryption
if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:2rLGpIMc5ziU63SxFHD6m+cE7sA842HiRBqt8CqQWQ0="
fi

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

# Set APP_URL based on the application domain
if [ -z "$APP_URL" ]; then
  export APP_URL="https://contractmonitoringbackend-production.up.railway.app"
fi

# If no Railway variables, use defaults from .env
export DB_HOST=${DB_HOST:-127.0.0.1}
export DB_PORT=${DB_PORT:-3306}
export DB_DATABASE=${DB_DATABASE:-pfda_contract_db}
export DB_USERNAME=${DB_USERNAME:-root}
export DB_PASSWORD=${DB_PASSWORD:-}

echo "Environment: $APP_ENV"
echo "Debug: $APP_DEBUG"
echo "APP_KEY: ${APP_KEY:0:20}..." 
echo "Database Host: $DB_HOST"
echo "Database Port: $DB_PORT"
echo "Database Name: $DB_DATABASE"
echo "Database User: $DB_USERNAME"
echo "App URL: $APP_URL"

# Override .env file with Railway environment variables (critical for Railway to work)
cat > .env << EOF
APP_NAME="PFDA Contract Monitoring System"
APP_ENV=$APP_ENV
APP_KEY=$APP_KEY
APP_DEBUG=$APP_DEBUG
APP_URL=$APP_URL
FRONTEND_URL=${FRONTEND_URL:-https://contract-monitoring-frontend-b8t2.vercel.app}
QR_BASE_URL=$APP_URL

DB_CONNECTION=mysql
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD

LOG_CHANNEL=stack
LOG_LEVEL=debug
EOF

# Ensure storage directories exist and are writable
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache public 2>/dev/null || true

echo "Clearing Laravel caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

echo "Running database migrations..."
php artisan migrate --force 2>&1 || echo "Migration warning (DB might already exist)"

echo "Seeding database..."
php artisan db:seed --force 2>&1 || echo "Seeding warning (data might already exist)"

echo "App is ready. Starting PHP server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
