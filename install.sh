#!/bin/bash

echo "========================================"
echo "PFDA Contract Monitoring System"
echo "Installation Script for Linux/Mac"
echo "========================================"
echo ""

echo "[1/8] Installing Composer dependencies..."
composer install
if [ $? -ne 0 ]; then
    echo "ERROR: Composer install failed!"
    exit 1
fi
echo ""

echo "[2/8] Installing QR Code and PDF packages..."
composer require simplesoftwareio/simple-qrcode barryvdh/laravel-dompdf
if [ $? -ne 0 ]; then
    echo "ERROR: Package installation failed!"
    exit 1
fi
echo ""

echo "[3/8] Creating environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created from .env.example"
else
    echo ".env file already exists, skipping..."
fi
echo ""

echo "[4/8] Generating application key..."
php artisan key:generate
echo ""

echo "[5/8] Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
echo "Permissions set"
echo ""

echo "[6/8] Creating storage link..."
php artisan storage:link
echo ""

echo "========================================"
echo "IMPORTANT: Database Configuration"
echo "========================================"
echo ""
echo "You need to manually:"
echo "1. Create a MySQL database named: pfda_contract_db"
echo "2. Update database credentials in .env file"
echo ""
read -p "After completing the above, press Enter to continue..."
echo ""

echo "[7/8] Running database migrations..."
php artisan migrate
if [ $? -ne 0 ]; then
    echo "ERROR: Migration failed! Please check your database configuration."
    exit 1
fi
echo ""

echo "[8/8] Seeding initial data..."
php artisan db:seed
if [ $? -ne 0 ]; then
    echo "ERROR: Seeding failed!"
    exit 1
fi
echo ""

echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo ""

echo "========================================"
echo "Installation Complete!"
echo "========================================"
echo ""
echo "Default Admin Credentials:"
echo "Email: admin@pfda.gov.ph"
echo "Password: password123"
echo ""
echo "Default Staff Credentials:"
echo "Email: staff@pfda.gov.ph"
echo "Password: password123"
echo ""
echo "IMPORTANT: Change these passwords after first login!"
echo ""
echo "To start the development server, run:"
echo "php artisan serve"
echo ""
echo "Then visit: http://localhost:8000"
echo ""
echo "For detailed documentation, see DOCUMENTATION.md"
echo "========================================"
echo ""
