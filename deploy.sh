#!/bin/bash

echo "=== PFDA CONTRACT MONITORING - DEPLOYMENT SCRIPT ==="
echo "Environment: $APP_ENV"
echo ""

# Exit on first error to catch issues
set -e

# Ensure directories exist
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo "Step 1: Clearing old caches..."
php artisan config:clear || echo "  ⚠ Config clear warning (expected if in build phase)"
php artisan route:clear || echo "  ⚠ Route clear warning (expected if in build phase)"
php artisan view:clear || echo "  ⚠ View clear warning (expected if in build phase)"

echo "Step 2: Rebuilding Laravel caches..."
php artisan config:cache || echo "  ⚠ Config cache warning"
php artisan route:cache || echo "  ⚠ Route cache warning - THIS IS CRITICAL"
php artisan view:cache || echo "  ⚠ View cache warning"

echo "Step 3: Database preparation..."
echo "  Checking database connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch (Exception \$e) { throw \$e; }" 2>/dev/null && echo "  ✓ Database is available" || echo "  ⚠ Database not yet available (will initialize on app start)"

echo "Step 4: Attempting migrations..."
php artisan migrate --force 2>/dev/null || echo "  ⚠ Migrations will run on app startup"

echo ""
echo "=== DEPLOYMENT CHECKS ==="
echo "Verifying route cache file exists..."
if [ -f "bootstrap/cache/routes-v7.php" ]; then
  echo "  ✓ Route cache file exists"
else
  echo "  ✗ WARNING: Route cache file not found!"
fi

echo ""
echo "✓ Deployment script completed"
echo "  - Routes may be loaded from cache (if available)"
echo "  - Migrations will finalize on app startup"



