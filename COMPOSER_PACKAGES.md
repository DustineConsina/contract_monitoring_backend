# Required Composer Packages for PFDA Contract Monitoring System

## Installation Instructions

After running `composer install`, you need to install these additional packages:

### 1. QR Code Generator
```bash
composer require simplesoftwareio/simple-qrcode
```

This package is used for generating QR codes for each tenant.

### 2. PDF Generator
```bash
composer require barryvdh/laravel-dompdf
```

This package is used for generating PDF reports.

## Complete Installation Command

You can install all required packages at once:

```bash
composer require simplesoftwareio/simple-qrcode barryvdh/laravel-dompdf
```

## Verification

After installation, verify that these packages are listed in your `composer.json` under the `require` section:

```json
{
    "require": {
        "simplesoftwareio/simple-qrcode": "^4.2",
        "barryvdh/laravel-dompdf": "^2.0"
    }
}
```

## Publishing Configuration Files

After installation, you may want to publish the configuration files:

```bash
php artisan vendor:publish --provider="SimpleSoftwareIO\QrCode\QrCodeServiceProvider"
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## Troubleshooting

### QR Code Issues
If you encounter issues with QR code generation, ensure the GD extension is enabled in your php.ini:
```ini
extension=gd
```

### PDF Generation Issues
If PDF generation fails, check that you have the required fonts:
```bash
# The package should handle this automatically, but if issues persist:
php artisan config:clear
php artisan cache:clear
```

## Laravel Sanctum (Already Included)

Laravel Sanctum is included with Laravel 11 by default and is used for API authentication. No additional installation needed.

## All Core Packages

The following are already included with Laravel 11:
- Laravel Framework 11.x
- Laravel Sanctum (API Authentication)
- Laravel Tinker (REPL)
- Pest PHP (Testing)

## Optional Packages for Development

For development, you might want to install:

```bash
composer require --dev barryvdh/laravel-debugbar
composer require --dev laravel/telescope
```

These are optional and should only be used in development environments.
