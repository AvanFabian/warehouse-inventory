# 📦 Warehouse Inventory Management System

A modern, secure, and feature-rich warehouse inventory management system built with Laravel 12 and Tailwind CSS.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

---

## ✨ Features

### 📊 **Core Functionality**
- **Product Management** - Categories, suppliers, stock tracking
- **Stock Transactions** - Stock In, Stock Out, Stock Opname
- **User Management** - Role-based access (Admin, Manager, Staff)
- **Advanced Reports** - Stock reports, transactions, inventory value, stock cards
- **PDF Export** - Professional PDF generation for all reports

### 🔒 **Security Features**
- DDoS protection with rate limiting
- Security headers (XSS, Clickjacking, MIME-sniffing protection)
- Suspicious request blocking
- Activity & security logging
- CSRF protection
- Button spam prevention

### 🎨 **User Experience**
- Clean, responsive design
- Real-time search & filtering
- Empty state interfaces
- Loading indicators
- Professional error pages (403, 404, 419, 500, 503)

---

## 🚀 Quick Start

### **Prerequisites**
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 5.7+ or MariaDB 10.3+

### **Installation**

1. **Clone the repository**
```bash
git clone <your-repo-url>
cd warehouse-inventory
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Edit .env file** with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=warehouse_inventory
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Seed default users** (optional)
```bash
php artisan db:seed --class=UserSeeder
```

7. **Build assets**
```bash
npm run build
```

8. **Start development server**
```bash
php artisan serve
```

9. **Visit** http://127.0.0.1:8000

---

## 👥 Default Users

| Email | Password | Role |
|-------|----------|------|
| admin@warehouse.test | password | Admin |
| manager@warehouse.test | password | Manager |
| staff@warehouse.test | password | Staff |

---

## 📁 Project Structure

```
warehouse-inventory/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/               # Eloquent models
│   ├── Middleware/           # Custom middleware
│   └── Exceptions/           # Exception handling
├── resources/
│   ├── views/                # Blade templates
│   ├── js/                   # JavaScript files
│   └── css/                  # Stylesheets
├── routes/
│   └── web.php               # Web routes
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
└── public/                   # Public assets
```

---

## 🛠️ Development

### **Run development server**
```bash
php artisan serve
npm run dev
```

### **Watch for changes**
```bash
npm run dev
```

### **Run tests**
```bash
php artisan test
```

### **Clear cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🚢 Deployment

### **Quick Deployment**
```bash
# 1. Update .env for production
APP_ENV=production
APP_DEBUG=false

# 2. Run deployment script
bash deploy.sh
```

### **Manual Deployment**
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

📖 **Full deployment guide:** See [DEPLOYMENT.md](DEPLOYMENT.md)

---

## 📚 Documentation

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Complete deployment guide with 200+ checklist items
- **[SECURITY.md](SECURITY.md)** - Security features and best practices
- **[LOGGING.md](LOGGING.md)** - Logging configuration and monitoring
- **[QUICKSTART.md](QUICKSTART.md)** - Quick reference for common tasks

---

## 🔐 Security

This application includes:
- ✅ Rate limiting & DDoS protection
- ✅ CSRF & XSS protection
- ✅ SQL injection prevention
- ✅ Security headers
- ✅ Activity logging
- ✅ Role-based access control

**Security issues?** Please email: security@yourdomain.com

---

## 🎯 Key Features by Role

### **Admin**
- Full system access
- User management
- System settings
- All reports & exports

### **Manager**
- Product management
- Stock transactions
- Reports viewing
- Supplier management

### **Staff**
- View products
- Create stock transactions
- Basic reports

---

## 📊 Available Reports

1. **Stock Report** - Current stock levels with filters
2. **Transaction Report** - Stock In/Out history
3. **Inventory Value** - Total inventory valuation by category
4. **Stock Card** - Product movement history

All reports support **PDF export** with company branding.

---

## 🐛 Troubleshooting

### **Permission errors?**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .
```

### **Assets not loading?**
```bash
npm run build
php artisan storage:link
```

### **Database connection failed?**
```bash
# Check .env database credentials
# Verify MySQL is running
php artisan config:clear
```

### **500 Internal Server Error?**
```bash
# Enable debug mode temporarily
APP_DEBUG=true
# Check storage/logs/laravel.log
tail -f storage/logs/laravel.log
```

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License.

---

## 👨‍💻 Built With

- **[Laravel 12](https://laravel.com)** - PHP Framework
- **[Tailwind CSS](https://tailwindcss.com)** - CSS Framework
- **[Alpine.js](https://alpinejs.dev)** - JavaScript Framework
- **[DomPDF](https://github.com/dompdf/dompdf)** - PDF Generation

---

## 💼 Developed By

**Avan Digital**
- Website: [avandigital.id](https://avandigital.id)
- Email: info@avandigital.id

---

## 🙏 Acknowledgments

- Laravel Community
- Tailwind CSS Team
- All contributors and testers

---

## 📞 Support

Need help? 

- 📖 Check [DEPLOYMENT.md](DEPLOYMENT.md) for deployment issues
- 🔒 Check [SECURITY.md](SECURITY.md) for security questions
- 📝 Check [LOGGING.md](LOGGING.md) for logging setup
- 🐛 Open an issue on GitHub

---

**⭐ Star this repo if you find it helpful!**

Made with ❤️ by avandigital.id
