release: php artisan migrate --force && php artisan config:cache && php artisan event:cache && php artisan route:cache && php artisan view:cache
web: php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
