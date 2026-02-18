@echo off
echo ========================================
echo PFDA Contract Monitoring System
echo Installation Script for Windows
echo ========================================
echo.

echo [1/8] Installing Composer dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)
echo.

echo [2/8] Installing QR Code and PDF packages...
call composer require simplesoftwareio/simple-qrcode barryvdh/laravel-dompdf
if %errorlevel% neq 0 (
    echo ERROR: Package installation failed!
    pause
    exit /b 1
)
echo.

echo [3/8] Creating environment file...
if not exist .env (
    copy .env.example .env
    echo .env file created from .env.example
) else (
    echo .env file already exists, skipping...
)
echo.

echo [4/8] Generating application key...
call php artisan key:generate
echo.

echo [5/8] Creating storage link...
call php artisan storage:link
echo.

echo ========================================
echo IMPORTANT: Database Configuration
echo ========================================
echo.
echo You need to manually:
echo 1. Create a MySQL database named: pfda_contract_db
echo 2. Update database credentials in .env file
echo.
echo After completing the above, press any key to continue...
pause > nul
echo.

echo [6/8] Running database migrations...
call php artisan migrate
if %errorlevel% neq 0 (
    echo ERROR: Migration failed! Please check your database configuration.
    pause
    exit /b 1
)
echo.

echo [7/8] Seeding initial data...
call php artisan db:seed
if %errorlevel% neq 0 (
    echo ERROR: Seeding failed!
    pause
    exit /b 1
)
echo.

echo [8/8] Clearing caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear
echo.

echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo Default Admin Credentials:
echo Email: admin@pfda.gov.ph
echo Password: password123
echo.
echo Default Staff Credentials:
echo Email: staff@pfda.gov.ph
echo Password: password123
echo.
echo IMPORTANT: Change these passwords after first login!
echo.
echo To start the development server, run:
echo php artisan serve
echo.
echo Then visit: http://localhost:8000
echo.
echo For detailed documentation, see DOCUMENTATION.md
echo ========================================
echo.
pause
