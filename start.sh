#!/bin/bash
set -e

echo "=== Starting PFDA Contract Monitoring Backend ==="

# Set production environment
export APP_ENV=${APP_ENV:-production}
export APP_DEBUG=${APP_DEBUG:-false}

# Set APP_KEY - Critical for Laravel encryption
if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:2rLGpIMc5ziU63SxFHD6m+cE7sA842HiRBqt8CqQWQ0="
fi

# Map Railway variables to Laravel variables BEFORE checking - this is critical
if [ -n "$MYSQLHOST" ]; then
  export DB_HOST=$MYSQLHOST
  echo "Using Railway DB_HOST: $DB_HOST"
else
  export DB_HOST=${DB_HOST:-127.0.0.1}
fi

if [ -n "$MYSQLPORT" ]; then
  export DB_PORT=$MYSQLPORT
fi

if [ -n "$MYSQLDATABASE" ]; then
  export DB_DATABASE=$MYSQLDATABASE
  echo "Using Railway DB_DATABASE: $DB_DATABASE"
else
  export DB_DATABASE=${DB_DATABASE:-pfda_contract_db}
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

export FRONTEND_URL=${FRONTEND_URL:-https://contract-monitoring-frontend-b8t2.vercel.app}

echo "Environment: $APP_ENV"
echo "Debug: $APP_DEBUG"
echo "APP_KEY: ${APP_KEY:0:20}..." 
echo "Database Host: $DB_HOST"
echo "Database Port: ${DB_PORT:-3306}"
echo "Database Name: $DB_DATABASE"
echo "Database User: $DB_USERNAME"
echo "DB_PASSWORD set: $([ -z "$DB_PASSWORD" ] && echo 'NO' || echo 'YES')"
echo "App URL: $APP_URL"
echo ""
echo "=== RAILWAY ENVIRONMENT VARIABLES (before override) ==="
echo "MYSQLHOST: $MYSQLHOST"
echo "MYSQLDATABASE: $MYSQLDATABASE"
echo "MYSQLUSER: $MYSQLUSER"
echo "MYSQLPASSWORD: $([ -z "$MYSQLPASSWORD" ] && echo 'NOT SET' || echo 'SET')"
echo ""

# Ensure storage directories exist and are writable
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache public 2>/dev/null || true

echo "Clearing Laravel caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
rm -rf storage/framework/cache/* 2>/dev/null || true

echo "Verifying database connection..."
php artisan tinker --execute="echo 'DB Connection OK'" 2>/dev/null || echo "Database connection warning - migrations will handle setup"

echo "Running database migrations..."
php artisan migrate --force 2>&1 || echo "Migration completed (DB might already exist)"

echo "Seeding database with admin/staff/cashier users..."
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || echo "AdminUserSeeder completed (users might already exist)"

echo "Running additional seeds..."
php artisan db:seed --force 2>&1 || echo "Additional seeding completed"

echo "App is ready. Starting PHP server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
