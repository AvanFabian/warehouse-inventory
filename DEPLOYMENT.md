# 🚀 Production Deployment Checklist

## 📋 Pre-Deployment (Do This Before Uploading)

### Environment Configuration
- [ ] Copy `.env.example` to `.env` on server
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` (run: `php artisan key:generate`)
- [ ] Set correct `APP_URL` with HTTPS
- [ ] Configure database credentials (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] Set `LOG_STACK=daily,security`
- [ ] Set `LOG_LEVEL=error`
- [ ] Add `LOG_DAILY_DAYS=30`
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `SESSION_SAME_SITE=strict`
- [ ] Configure email settings (MAIL_MAILER, MAIL_HOST, etc.)

### Code Preparation
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm run build` (compile assets)
- [ ] Remove development dependencies
- [ ] Test all features locally in production mode
- [ ] Check for any hardcoded localhost URLs
- [ ] Review all routes work correctly

### Security Check
- [ ] Ensure `.env` is in `.gitignore`
- [ ] Remove any debug code or dd()/dump() statements
- [ ] Verify no sensitive data in code
- [ ] Check all user inputs are validated
- [ ] Confirm CSRF protection is working
- [ ] Test rate limiting works

---

## 🌐 Deployment Steps

### 1. Upload Files
```bash
# Upload all files EXCEPT:
- .env (configure separately)
- /node_modules (not needed)
- /storage (preserve existing if updating)
- /.git (optional)
```

### 2. Server Configuration
- [ ] PHP Version: 8.2 or higher
- [ ] Required PHP Extensions:
  - [ ] BCMath
  - [ ] Ctype
  - [ ] Fileinfo
  - [ ] JSON
  - [ ] Mbstring
  - [ ] OpenSSL
  - [ ] PDO
  - [ ] Tokenizer
  - [ ] XML
  - [ ] GD (for images)
  - [ ] ZIP

### 3. Document Root
- [ ] Set document root to `/public` directory
- [ ] NOT the project root!
- [ ] Example: `/home/user/warehouse-inventory/public`

### 4. File Permissions
```bash
# Run these on server:
chmod -R 755 /path/to/project
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data /path/to/project  # or your web server user
```

### 5. Database Setup
```bash
# On server:
php artisan migrate --force
php artisan db:seed --class=UserSeeder --force  # If needed
```

### 6. Storage Link
```bash
php artisan storage:link
```

### 7. Cache & Optimize
```bash
# Run deployment script:
bash deploy.sh

# Or manually:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer dump-autoload --optimize
```

---

## ✅ Post-Deployment Testing

### Functionality Tests
- [ ] Visit homepage - loads correctly
- [ ] Login works
- [ ] Register works (if enabled)
- [ ] Dashboard displays properly
- [ ] Create product works
- [ ] Create category works
- [ ] Create supplier works
- [ ] Stock In transaction works
- [ ] Stock Out transaction works
- [ ] Stock Opname works
- [ ] Reports generate correctly
- [ ] PDF export works
- [ ] User management works (admin)
- [ ] Settings page works (admin)
- [ ] Logout works

### Security Tests
- [ ] HTTPS is working (green padlock)
- [ ] Mixed content warnings fixed
- [ ] Login rate limiting works (try 6+ failed attempts)
- [ ] XSS protection works
- [ ] CSRF tokens working on forms
- [ ] Session timeout works
- [ ] 404 page displays correctly
- [ ] 403 page displays for unauthorized access
- [ ] Error pages don't show sensitive info

### Performance Tests
- [ ] Page load times are acceptable
- [ ] Images load properly
- [ ] CSS/JS files load from /build directory
- [ ] No console errors in browser DevTools
- [ ] Database queries are efficient

### Email Tests (if configured)
- [ ] Password reset email sends
- [ ] Email notifications work
- [ ] Email sender name is correct

---

## 🔧 Server-Specific Configuration

### Apache (.htaccess already included)
- [ ] `mod_rewrite` enabled
- [ ] `AllowOverride All` in VirtualHost
- [ ] Uncomment HTTPS redirect in `.htaccess` if needed

### Nginx (if using)
Create nginx config:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### SSL Certificate
- [ ] Install SSL certificate (Let's Encrypt recommended)
- [ ] Force HTTPS redirect
- [ ] Update `APP_URL` to use https://
- [ ] Test SSL with https://www.ssllabs.com/ssltest/

---

## 📊 Monitoring Setup

### Log Monitoring
```bash
# Watch logs in real-time:
tail -f storage/logs/laravel.log
tail -f storage/logs/security.log
tail -f storage/logs/activity.log
```

### Health Check
- [ ] Set up monitoring (UptimeRobot, Pingdom, etc.)
- [ ] Monitor `/up` endpoint (Laravel health check)
- [ ] Set up email alerts for downtime

### Backup Strategy
- [ ] Set up automated database backups
- [ ] Set up file backups (storage/app)
- [ ] Test backup restoration
- [ ] Document backup location

---

## 🐛 Common Issues & Solutions

### Issue: 500 Internal Server Error
**Solutions:**
1. Check storage permissions: `chmod -R 775 storage bootstrap/cache`
2. Check .env file exists and is readable
3. Run: `php artisan config:clear`
4. Check Laravel logs: `storage/logs/laravel.log`

### Issue: 404 on all routes
**Solutions:**
1. Check document root points to `/public`
2. Enable mod_rewrite (Apache)
3. Check .htaccess file exists in public folder
4. Run: `php artisan route:clear`

### Issue: Assets not loading (CSS/JS)
**Solutions:**
1. Run: `npm run build`
2. Check `public/build` directory exists
3. Clear browser cache
4. Check APP_URL in .env matches actual URL

### Issue: Database connection error
**Solutions:**
1. Verify database credentials in .env
2. Check database exists
3. Check database user has proper permissions
4. Test connection: `php artisan migrate:status`

### Issue: "No application encryption key"
**Solutions:**
1. Run: `php artisan key:generate`
2. Check APP_KEY in .env is set
3. Run: `php artisan config:clear`

### Issue: Session not persisting
**Solutions:**
1. Check SESSION_DRIVER in .env
2. If using database: `php artisan migrate` (sessions table)
3. Check storage/framework/sessions permissions
4. Run: `php artisan session:table` then `php artisan migrate`

---

## 🔐 Security Hardening

### Additional Security Measures
- [ ] Keep Laravel and dependencies updated
- [ ] Enable firewall on server
- [ ] Disable unused PHP functions
- [ ] Use SSH keys instead of passwords
- [ ] Implement fail2ban for brute force protection
- [ ] Regular security audits
- [ ] Monitor logs for suspicious activity

### Rate Limiting Verification
- [ ] Test login rate limit (5 attempts/minute)
- [ ] Test general rate limit (60 requests/minute)
- [ ] Verify 429 error page displays

---

## 📝 Documentation

### For Team/Client
- [ ] Document admin credentials (securely)
- [ ] Create user manual
- [ ] Document backup procedures
- [ ] Document update procedures
- [ ] Create troubleshooting guide

### For Developers
- [ ] Document deployment process
- [ ] Document environment variables
- [ ] Document database schema
- [ ] Document API endpoints (if any)
- [ ] Document custom artisan commands

---

## ✨ Final Verification

### Before Going Live
- [ ] All tests pass
- [ ] No errors in logs
- [ ] Performance is acceptable
- [ ] Security measures in place
- [ ] Backups configured
- [ ] Monitoring active
- [ ] SSL certificate valid
- [ ] Admin account works
- [ ] Email notifications work
- [ ] Error pages display correctly

### Go-Live
- [ ] Announce maintenance window (if applicable)
- [ ] Deploy to production
- [ ] Run post-deployment tests
- [ ] Monitor for 24 hours
- [ ] Document any issues
- [ ] Celebrate! 🎉

---

## 📞 Support Contacts

**Developer:** [Your Name]
**Email:** [Your Email]
**Phone:** [Your Phone]

**Hosting Provider:** [Provider Name]
**Support:** [Provider Support Contact]

**Database:** [Database Details]
**Backup Location:** [Backup Location]

---

## 🔄 Regular Maintenance

### Weekly
- [ ] Check error logs
- [ ] Monitor disk space
- [ ] Review security logs
- [ ] Check backup status

### Monthly
- [ ] Update dependencies: `composer update`
- [ ] Review and optimize database
- [ ] Clean old logs (automated)
- [ ] Security review

### Quarterly
- [ ] Full security audit
- [ ] Performance optimization
- [ ] User feedback review
- [ ] Feature updates

---

**Last Updated:** [Date]
**Version:** 1.0
**Status:** Ready for Production ✅
