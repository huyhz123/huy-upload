# 🔧 Laravel Repair Service & File/Course Sales System

**Full-stack Laravel 11 skeleton** cho website **Dịch vụ sửa chữa** (Device Repair) & **Bán File/Khóa học** với tích hợp đầy đủ:
- ✅ **Multi-role authentication** (Admin, Staff, Customer)
- ✅ **Service management** (Dịch vụ sửa chữa)
- ✅ **Product/File/Course sales** 
- ✅ **Ticket system** với API integration (DHRU, GSM)
- ✅ **Multi-payment gateways** (VNPay, Momo, PayPal, Stripe, Alipay, WeChat Pay)
- ✅ **Invoice & Financial management**
- ✅ **AI Chatbot 24/7** với OpenAI
- ✅ **Multi-language** (Vietnamese, English, Chinese)
- ✅ **Responsive UI** với TailwindCSS + Alpine.js
- ✅ **PDF generation, Watermark, Token download**

---

## 📋 Table of Contents

- [Requirements](#requirements)
- [Quick Start - Deploy in 1 Command](#quick-start)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Structure](#database-structure)
- [API Integration](#api-integration)
- [Payment Gateways](#payment-gateways)
- [Chatbot Setup](#chatbot-setup)
- [Testing](#testing)
- [Deployment](#deployment)
- [License](#license)

---

## 🔧 Requirements

- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Node.js**: >= 18.x
- **NPM**: >= 9.x
- **Database**: MySQL 8.0+ / PostgreSQL 14+ / MariaDB 10.6+
- **Extensions**: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, GD/Imagick

---

## 🚀 Quick Start - Deploy in 1 Command

### Option 1: Using Bash Script (Recommended)

```bash
# Clone repository
git clone <your-repo-url>
cd <project-folder>

# Make deploy script executable
chmod +x deploy.sh

# Run full deployment
./deploy.sh
```

### Option 2: Using Artisan Command

```bash
# After composer install
composer install
php artisan deploy:full
npm run build
```

### Option 3: Manual Installation

See [Installation](#installation) section below.

---

## ✨ Features

### 🏠 **Customer Frontend**
- **Homepage**: Slider dịch vụ, featured services/products, call-to-action
- **Services Page**: Browse repair services, filter by category, add to cart/create ticket
- **Files & Courses**: Browse files and courses, filter, rating, review system
- **Checkout & Payment**: Multi-gateway payment, auto verify, generate PDF invoice
- **Customer Dashboard**: View tickets, download history, balance, debt tracking
- **AI Chatbot 24/7**: Multi-language support, auto quote, appointment scheduling

### 🛠️ **Admin Dashboard**
- **Overview**: Revenue, profit/loss, inventory, debt, new tickets, responsive charts
- **Service Management**: Add/edit/delete services, custom fields, drag-drop sorting, API integration
- **Product Management**: Inventory tracking, low stock alerts, cost/profit analysis
- **File & Course Management**: Upload files, tokenized download with watermark, enrollment tracking
- **Ticket Management**: Assign staff, status updates, API order sync (DHRU/GSM)
- **User Management**: Customer/staff management, role permissions, activity logs
- **Financial Management**: Revenue, profit/loss, debt tracking, expense management
- **Settings**: Multi-language, payment config, site settings, drag-drop layout

### 💰 **Payment & Financial**
- **Multi-Gateway**: VNPay, Momo, PayPal, Stripe, Alipay, WeChat Pay
- **Auto Invoice**: PDF generation with customizable templates
- **Financial Tracking**: Revenue, expenses, profit/loss calculation
- **Debt Management**: Track customer balances and payments

### 🤖 **AI & API Integration**
- **Chatbot**: OpenAI-powered, multi-language, context-aware responses
- **DHRU API**: Device unlock services integration
- **GSM API**: Mobile service provider integration
- **Custom APIs**: Extensible webhook system

### 🎨 **UI/UX Design**
- **Figma-style**: Modern, clean design with TailwindCSS
- **Responsive**: Mobile-first, fully responsive across all devices
- **Components**: Gradient buttons, cards, modals, hover effects
- **Typography**: Professional font hierarchy and spacing
- **Dark Mode**: Optional dark mode support

---

## 📦 Installation

### Step 1: Clone & Install Dependencies

```bash
# Clone repository
git clone <your-repo-url>
cd <project-folder>

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Step 2: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file with your configuration:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=repair_service
DB_USERNAME=root
DB_PASSWORD=your_password

# OpenAI for Chatbot
OPENAI_API_KEY=your_openai_key

# Payment Gateways
VNPAY_TMN_CODE=your_vnpay_code
VNPAY_HASH_SECRET=your_vnpay_secret
MOMO_PARTNER_CODE=your_momo_code
MOMO_ACCESS_KEY=your_momo_key
PAYPAL_CLIENT_ID=your_paypal_id
PAYPAL_SECRET=your_paypal_secret

# DHRU API
DHRU_API_URL=https://api.dhru.com
DHRU_USERNAME=your_username
DHRU_API_KEY=your_api_key

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### Step 3: Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE repair_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed
```

### Step 4: Build Assets

```bash
# Build for development
npm run dev

# Build for production
npm run build
```

### Step 5: Storage & Permissions

```bash
# Create storage link
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

### Step 6: Start Application

```bash
# Development server
php artisan serve

# Access at: http://localhost:8000
```

---

## ⚙️ Configuration

### Multi-Language Setup

Language files are in `resources/lang/`. Supported languages:
- 🇻🇳 Vietnamese (vi)
- 🇬🇧 English (en)
- 🇨🇳 Chinese (zh)

Add translations in language files and set default in `config/app.php`.

### Role & Permissions

Three main roles:
- **Admin**: Full access to all features
- **Staff**: Service and ticket management
- **Customer**: View services, create tickets, purchase files/courses

Customize permissions in `database/seeders/RoleAndPermissionSeeder.php`.

---

## 🗄️ Database Structure

### Main Tables
- **users**: User accounts with roles
- **services**: Repair services
- **products**: Physical products/parts
- **files**: Downloadable files with watermark
- **courses**: Online courses with curriculum
- **tickets**: Service tickets with API integration
- **invoices**: Orders and invoices
- **payments**: Payment transactions
- **api_logs**: API request/response logs
- **chatbot_messages**: AI chatbot conversations
- **financial_records**: Revenue, expenses, profit tracking
- **downloads**: Token-based file downloads
- **reviews**: Product/service reviews
- **carts**: Shopping cart items
- **settings**: Site-wide settings

See `database/migrations/` for complete schema.

---

## 🔌 API Integration

### DHRU API Integration

```php
// config/services.php
'dhru' => [
    'url' => env('DHRU_API_URL'),
    'username' => env('DHRU_USERNAME'),
    'api_key' => env('DHRU_API_KEY'),
],
```

Usage in controllers:
```php
use App\Services\DhruApiService;

$dhru = new DhruApiService();
$result = $dhru->placeOrder($imei, $serviceId);
```

### GSM API Integration

Similar configuration in `config/services.php`. See `app/Services/GsmApiService.php`.

---

## 💳 Payment Gateways

### VNPay Configuration

```env
VNPAY_TMN_CODE=your_code
VNPAY_HASH_SECRET=your_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost:8000/payment/callback/vnpay
```

### Momo Configuration

```env
MOMO_PARTNER_CODE=your_code
MOMO_ACCESS_KEY=your_key
MOMO_SECRET_KEY=your_secret
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
MOMO_RETURN_URL=http://localhost:8000/payment/callback/momo
```

See `app/Services/PaymentService.php` for implementation.

---

## 🤖 Chatbot Setup

1. Get OpenAI API key: https://platform.openai.com/api-keys
2. Add to `.env`:
   ```env
   OPENAI_API_KEY=sk-your-key-here
   ```
3. Configure chatbot behavior in `app/Services/ChatbotService.php`

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## 🚢 Deployment

### Production Deployment

```bash
# 1. Pull latest code
git pull origin main

# 2. Run deploy script
./deploy.sh

# 3. Set production mode in .env
APP_ENV=production
APP_DEBUG=false

# 4. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Server Requirements
- Web server: Nginx / Apache
- PHP-FPM
- Supervisor (for queues)
- SSL certificate (Let's Encrypt)

### Queue Worker Setup

```bash
# Supervisor config example
[program:repair-service-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

---

## 📧 Demo Credentials

After seeding:
- **Admin**: admin@repair.com / admin123
- **Staff**: staff@repair.com / staff123
- **Customer**: customer1@example.com / password

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🙏 Support

For issues and questions:
- GitHub Issues: [Create an issue](https://github.com/your-repo/issues)
- Email: support@yourcompany.com

---

## 🎯 Roadmap

- [ ] Mobile app (React Native / Flutter)
- [ ] Advanced reporting & analytics
- [ ] Multi-currency support
- [ ] SMS notifications
- [ ] Inventory management enhancement
- [ ] Customer loyalty program

---

**Made with ❤️ using Laravel 11**
