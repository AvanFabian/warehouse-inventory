# 🚀 Quick Deployment Guide

## For Shared Hosting (cPanel / Plesk)

### Step 1: Upload Files
1. Upload all files EXCEPT `/node_modules` and `/vendor`
2. Upload to: `/home/username/` (NOT public_html)

### Step 2: Set Document Root
In cPanel:
1. Go to "Select PHP Version" or "MultiPHP Manager"
2. Set document root to: `/home/username/warehouse-inventory/public`

### Step 3: Configure .env
1. Copy `.env.production` to `.env`
2. Edit `.env` with correct values:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   DB_HOST=localhost
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_pass
   ```

### Step 4: Install Dependencies
SSH into server:
```bash
cd /home/username/warehouse-inventory
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
bash deploy.sh
```

### Step 5: Set Permissions
```bash
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

---

## For VPS (Ubuntu/Debian)

### Prerequisites
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
sudo apt install -y mysql-server
```

### Deploy Application
```bash
# Clone or upload files
cd /var/www
sudo mkdir warehouse-inventory
sudo chown $USER:$USER warehouse-inventory
cd warehouse-inventory

# Upload files here or git clone

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# Configure .env
cp .env.production .env
nano .env  # Edit database and other settings

# Generate key & migrate
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data /var/www/warehouse-inventory
sudo chmod -R 755 /var/www/warehouse-inventory
sudo chmod -R 775 /var/www/warehouse-inventory/storage
sudo chmod -R 775 /var/www/warehouse-inventory/bootstrap/cache

# Optimize
bash deploy.sh
```

### Configure Nginx
```bash
# Copy nginx config
sudo cp nginx.conf /etc/nginx/sites-available/warehouse
sudo ln -s /etc/nginx/sites-available/warehouse /etc/nginx/sites-enabled/

# Edit domain name in config
sudo nano /etc/nginx/sites-available/warehouse

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

### Install SSL (Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## Common Commands

### Clear & Optimize
```bash
php artisan optimize:clear  # Clear all caches
php artisan optimize        # Cache config, routes, views
```

### Check Status
```bash
php artisan migrate:status  # Check migrations
php artisan route:list      # List all routes
php artisan config:show     # Show configuration
```

### Logs
```bash
tail -f storage/logs/laravel.log      # Watch app logs
tail -f storage/logs/security.log     # Watch security logs
tail -f /var/log/nginx/error.log      # Watch nginx errors
```

### Maintenance Mode
```bash
php artisan down            # Enable maintenance mode
php artisan up              # Disable maintenance mode
```

---

## Troubleshooting

### 500 Error
```bash
php artisan config:clear
chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
```

### Database Error
```bash
php artisan migrate:status  # Check migrations
php artisan migrate:fresh --seed --force  # Reset (DANGER!)
```

### Assets Not Loading
```bash
npm run build
php artisan optimize:clear
```

---

## Security Reminders

✅ Set `APP_DEBUG=false`
✅ Set `APP_ENV=production`  
✅ Enable HTTPS
✅ Set `SESSION_SECURE_COOKIE=true`
✅ Keep `.env` secure (not in web root)
✅ Regular backups
✅ Monitor logs daily

---

## Support

**Documentation:** See DEPLOYMENT.md for complete guide
**Security:** See SECURITY.md for security features
**Logging:** See LOGGING.md for logging guide

**Developer:** [Your Name]
**Email:** [Your Email]
