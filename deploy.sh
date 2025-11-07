#!/bin/bash

# Deployment Script for Warehouse Inventory System
# Run this script after uploading files to server

echo "🚀 Starting deployment process..."

# 1. Set proper permissions
echo "📁 Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 2. Install/Update Composer dependencies (production only)
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# 3. Clear and cache config
echo "⚙️ Optimizing configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. Create symbolic link for storage
echo "🔗 Creating storage link..."
php artisan storage:link

# 6. Install NPM dependencies and build assets
echo "🎨 Building frontend assets..."
npm ci --production
npm run build

# 7. Clear all caches
echo "🧹 Clearing application cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 8. Re-cache for production
echo "💾 Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Optimize autoloader
echo "⚡ Optimizing autoloader..."
composer dump-autoload --optimize

# 10. Set final permissions
echo "🔒 Setting final permissions..."
chmod -R 755 .
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment completed successfully!"
echo ""
echo "⚠️  Important: Please verify:"
echo "   1. .env file is configured correctly"
echo "   2. Database credentials are correct"
echo "   3. APP_KEY is set"
echo "   4. APP_DEBUG=false"
echo "   5. HTTPS is working"
echo ""
echo "🎉 Your application is ready!"
