# 🚀 Installation Guide - Repair Service System

Hướng dẫn cài đặt chi tiết cho **Laravel 11 Repair Service & File/Course Sales System** với **Web Installer**.

---

## 📋 Mục Lục

- [Yêu Cầu Hệ Thống](#yêu-cầu-hệ-thống)
- [Cài Đặt Qua Web Installer](#cài-đặt-qua-web-installer)
- [Cài Đặt Thủ Công](#cài-đặt-thủ-công)
- [Sau Khi Cài Đặt](#sau-khi-cài-đặt)
- [Troubleshooting](#troubleshooting)

---

## 🔧 Yêu Cầu Hệ Thống

### Server Requirements

- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Node.js**: >= 18.x
- **NPM**: >= 9.x
- **Web Server**: Apache / Nginx
- **Database**: MySQL 8.0+ / PostgreSQL 14+ / MariaDB 10.6+

### PHP Extensions (Required)

✅ OpenSSL
✅ PDO
✅ Mbstring
✅ Tokenizer
✅ XML
✅ Ctype
✅ JSON
✅ BCMath
✅ Fileinfo
✅ GD hoặc Imagick

### Folder Permissions

✅ `storage/` - Writable (775)
✅ `bootstrap/cache/` - Writable (775)
✅ `.env` - Writable (644)

---

## 🌐 Cài Đặt Qua Web Installer (Khuyến Nghị)

### **Phương Pháp 1: Upload Lên Server**

1. **Upload Files**
   ```bash
   # Upload toàn bộ source code lên server
   # Sử dụng FTP/SFTP hoặc Git
   ```

2. **Cấu Hình Web Server**
   
   **Apache (.htaccess)**
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

   **Nginx**
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/project/public;

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

3. **Install Dependencies**
   ```bash
   cd /path/to/project
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

4. **Set Permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. **Truy Cập Web Installer**
   ```
   http://your-domain.com/installer
   ```

6. **Follow Installation Wizard**

   **Step 1: Welcome**
   - Click "Start Installation"

   **Step 2: Server Requirements**
   - System will automatically check:
     - PHP version
     - Required extensions
     - Folder permissions
   - Fix any issues if shown
   - Click "Next"

   **Step 3: Database Configuration**
   - Enter database credentials:
     - Database Host (e.g., 127.0.0.1)
     - Database Port (e.g., 3306)
     - Database Name (e.g., repair_service)
     - Database Username
     - Database Password
   - System will test connection automatically
   - Click "Test & Continue"

   **Step 4: Admin Account**
   - Create your admin user:
     - Full Name
     - Email Address
     - Password (minimum 8 characters)
     - Confirm Password
   - Click "Continue"

   **Step 5: Installation**
   - System will automatically:
     1. ✅ Clear caches
     2. ✅ Run database migrations
     3. ✅ Create roles & permissions
     4. ✅ Create admin user
     5. ✅ Seed demo data
     6. ✅ Create storage links
     7. ✅ Optimize application
     8. ✅ Mark as installed
   - Wait for completion (usually 30-60 seconds)

   **Step 6: Complete!**
   - Installation successful
   - Note your admin credentials
   - Click "Visit Website" or "Admin Dashboard"

---

## 🛠️ Cài Đặt Thủ Công (Alternative)

### Option 1: Using Bash Script

```bash
# 1. Clone repository
git clone <your-repo-url>
cd <project-folder>

# 2. Run deploy script
chmod +x deploy.sh
./deploy.sh

# 3. Follow prompts
```

### Option 2: Using Artisan Command

```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Configure database in .env
nano .env  # Edit DB_* values

# 4. Run deployment
php artisan deploy:full

# 5. Build assets
npm run build

# 6. Start server
php artisan serve
```

### Option 3: Manual Step by Step

```bash
# 1. Clone & Install
git clone <repo-url>
cd <project>
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (edit .env first)
php artisan migrate:fresh --seed

# 4. Storage
php artisan storage:link
chmod -R 775 storage bootstrap/cache

# 5. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Build & Run
npm run build
php artisan serve
```

---

## ✨ Sau Khi Cài Đặt

### 1. Truy Cập Hệ Thống

**Frontend (Customer)**
```
http://your-domain.com
```

**Admin Dashboard**
```
http://your-domain.com/admin/dashboard
```

### 2. Demo Credentials

**Admin Account**
- Email: admin@repair.com
- Password: admin123 (hoặc password bạn đã tạo)

**Staff Account**
- Email: staff@repair.com
- Password: staff123

**Customer Account**
- Email: customer1@example.com
- Password: password

### 3. Cấu Hình Bổ Sung

#### Payment Gateways

Edit `.env`:
```env
# VNPay
VNPAY_TMN_CODE=your_code
VNPAY_HASH_SECRET=your_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html

# Momo
MOMO_PARTNER_CODE=your_code
MOMO_ACCESS_KEY=your_key
MOMO_SECRET_KEY=your_secret

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your_id
PAYPAL_SECRET=your_secret

# Stripe
STRIPE_KEY=your_key
STRIPE_SECRET=your_secret
```

#### AI Chatbot (OpenAI)

```env
OPENAI_API_KEY=sk-your-openai-key-here
```

#### Email Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### DHRU API (Phone Unlock)

```env
DHRU_API_URL=https://api.dhru.com
DHRU_USERNAME=your_username
DHRU_API_KEY=your_api_key
```

### 4. Tùy Chỉnh Site Settings

Login vào Admin Dashboard → Settings:

- **General**: Site name, description, contact info
- **Localization**: Language, timezone, currency
- **Features**: Enable/disable chatbot, reviews, etc.
- **Payment**: Configure payment methods
- **API**: Setup API credentials

### 5. Thêm Nội Dung

- **Services**: Admin → Services → Create
- **Products**: Admin → Products → Create
- **Files**: Admin → Files → Upload
- **Courses**: Admin → Courses → Create

---

## 🔍 Troubleshooting

### Issue: "Application is already installed"

**Solution:**
```bash
# Remove installed marker to reinstall
rm storage/installed
php artisan cache:clear
```

### Issue: Database connection failed

**Check:**
1. Database credentials in `.env`
2. Database server is running
3. Database exists
4. User has proper permissions

```bash
# Test database connection
mysql -u root -p
CREATE DATABASE repair_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL ON repair_service.* TO 'your_user'@'localhost';
```

### Issue: Permission denied on storage/

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# Or for your user:
chown -R $USER:www-data storage bootstrap/cache
```

### Issue: 500 Error after installation

**Check:**
1. `.env` file exists and is configured
2. `storage/` has write permissions
3. Clear all caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   ```

### Issue: Blank page or white screen

**Enable debug mode:**
```env
APP_DEBUG=true
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

### Issue: Assets not loading

**Rebuild assets:**
```bash
npm run build
php artisan config:clear
```

---

## 📞 Support

Nếu gặp vấn đề:

1. Check `storage/logs/laravel.log` for errors
2. Run `php artisan route:list` to verify routes
3. Run `composer dump-autoload`
4. Clear all caches: `php artisan optimize:clear`

---

## 🎯 Next Steps

1. ✅ Customize your branding and logo
2. ✅ Add your services and products
3. ✅ Configure payment gateways
4. ✅ Setup email notifications
5. ✅ Enable AI chatbot with OpenAI
6. ✅ Test complete user flow
7. ✅ Go live!

---

**Chúc bạn deploy thành công! 🎉**
