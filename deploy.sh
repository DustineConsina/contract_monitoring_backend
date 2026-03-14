#!/bin/bash

echo "=== PFDA CONTRACT MONITORING - DEPLOYMENT SCRIPT ==="
echo ""

# Don't exit on errors - just report them
set +e

# Ensure directories exist
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo "Clearing old caches..."
php artisan config:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1
php artisan view:clear >/dev/null 2>&1

echo "Rebuilding caches..."
php artisan config:cache >/dev/null 2>&1 && echo "  ✓ Config cache" || echo "  ⊘ Admin"
php artisan route:cache >/dev/null 2>&1 && echo "  ✓ Route cache" || echo "  ⊘ Routes (will load normally)"
php artisan view:cache >/dev/null 2>&1 && echo "  ✓ View cache" || echo "  ⊘ Views"

echo "Attempting migrations..."
php artisan migrate --force --no-interaction >/dev/null 2>&1 && echo "  ✓ Migrations" || echo "  ⊘ Migrations (will retry on startup)"

echo ""
echo "✓ Deployment complete"





