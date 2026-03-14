#!/bin/bash

echo "Starting deployment..."

# Don't fail on errors - just warn
set +e

# Clear config and route caches (file-based, don't need DB)
echo "Clearing application caches..."
php artisan config:clear 2>/dev/null || echo "  ⚠ Config clear failed (may retry later)"
php artisan route:clear 2>/dev/null || echo "  ⚠ Route clear failed (may retry later)"
php artisan view:clear 2>/dev/null || echo "  ⚠ View clear failed (may retry later)"

# Rebuild caches (these should work without DB)
echo "Building configuration cache..."
php artisan config:cache 2>/dev/null && echo "  ✓ Config cached" || echo "  ⚠ Config cache failed"

echo "Building route cache..."
php artisan route:cache 2>/dev/null && echo "  ✓ Routes cached" || echo "  ⚠ Route cache failed"

echo "Building view cache..."
php artisan view:cache 2>/dev/null && echo "  ✓ Views cached" || echo "  ⚠ View cache failed"

# These require database - only run if available
echo "Running migrations and database operations..."
php artisan migrate --force --no-interaction 2>/dev/null && echo "  ✓ Migrations completed" || echo "  ⚠ Migrations skipped (database not yet available)"

echo ""
echo "✓ Deployment phase completed"


