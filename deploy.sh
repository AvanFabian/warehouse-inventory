#!/bin/bash

# Warehouse Inventory Deployment Script
echo "🚀 Starting deployment of Warehouse Inventory System..."

# Configuration
DOMAIN="gudangbiasa.avandigital.id"
CPANEL_USER="avanfabi"
DB_NAME="avandigital_warehouse_management_db"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Pre-deployment checks
print_status "Running pre-deployment checks..."

# Check if .env.production exists
if [ ! -f ".env.production" ]; then
    print_error ".env.production file not found!"
    exit 1
fi

# Run tests
print_status "Running tests..."
if ! vendor/bin/phpunit; then
    print_error "Tests failed! Deployment aborted."
    exit 1
fi

# Build assets
print_status "Building production assets..."
npm run build

# Create optimized vendor
print_status "Installing production dependencies..."
composer install --no-dev --optimize-autoloader

# Create deployment package
print_status "Creating deployment package..."
tar -czf warehouse-deploy.tar.gz \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='tests' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    .

print_status "Deployment package created: warehouse-deploy.tar.gz"
print_warning "Now upload this package to your cPanel and extract it."

echo ""
print_status "Post-deployment checklist:"
echo "1. Upload warehouse-deploy.tar.gz to cPanel"
echo "2. Extract to private/warehouse-inventory/"
echo "3. Copy .env.production to .env"
echo "4. Run: php artisan migrate --force"
echo "5. Run: php artisan config:cache"
echo "6. Run: php artisan route:cache"
echo "7. Run: php artisan view:cache"
echo "8. Run: php artisan storage:link"
echo "9. Set proper file permissions"
echo "10. Test the application"

print_status "Deployment preparation completed!"