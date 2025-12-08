# 📥 Hướng Dẫn Download Source Code

## 🎯 **3 Cách Lấy Source Code**

---

### **Phương Pháp 1: Download ZIP Trực Tiếp** ⚡ (Nhanh nhất)

```bash
# File ZIP đã được tạo sẵn tại:
public/laravel-repair-service.zip (196KB - chỉ code, không có vendor/)

# Nếu đang chạy server:
http://localhost:8000/laravel-repair-service.zip

# Hoặc copy trực tiếp:
cp public/laravel-repair-service.zip ~/Desktop/
```

**Lưu ý:** File ZIP này chỉ chứa source code, KHÔNG bao gồm:
- `vendor/` folder (Composer dependencies)
- `node_modules/` folder (NPM dependencies)
- `.env` file

Sau khi extract, bạn cần chạy:
```bash
composer install
npm install
```

---

### **Phương Pháp 2: Clone Git Repository** 🔄 (Khuyến nghị)

```bash
# Clone repository
git clone <repository-url> repair-service
cd repair-service

# Checkout branch này
git checkout claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM

# Install dependencies
composer install
npm install

# Setup
cp .env.example .env
php artisan key:generate
```

**Ưu điểm:**
- Có toàn bộ Git history
- Dễ dàng update code sau này
- Có thể switch giữa các branches

---

### **Phương Pháp 3: Tạo ZIP Đầy Đủ** 📦 (Bao gồm vendor/)

```bash
# Nếu muốn ZIP có cả vendor/ và node_modules/
cd /home/user/huy-upload
zip -r ../laravel-repair-full.zip . \
    -x ".git/*" \
    -x "storage/logs/*" \
    -x "storage/framework/cache/*" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/views/*"

# File sẽ được tạo tại:
# /home/user/laravel-repair-full.zip (khoảng 60-80MB)
```

---

## 📂 **Cấu Trúc Source Code**

```
laravel-repair-service/
├── app/                          # Application code
│   ├── Console/Commands/         # Artisan commands
│   ├── Http/
│   │   ├── Controllers/          # All controllers
│   │   │   ├── Admin/            # Admin controllers
│   │   │   ├── Customer/         # Customer controllers
│   │   │   └── InstallerController.php
│   │   └── Middleware/           # CheckInstalled middleware
│   └── Models/                   # All 14 models
├── bootstrap/                    # Bootstrap files
├── config/                       # Configuration files
├── database/
│   ├── migrations/               # 15+ migration files
│   └── seeders/                  # 7 seeder files
├── public/                       # Public assets
│   └── laravel-repair-service.zip
├── resources/
│   ├── css/                      # TailwindCSS
│   ├── js/                       # Alpine.js
│   └── views/
│       ├── auth/                 # Breeze auth views
│       ├── installer/            # 7 installer views
│       └── ...
├── routes/
│   └── web.php                   # All routes
├── storage/                      # Storage folder
├── tests/                        # Tests
├── .env.example                  # Environment template
├── composer.json                 # PHP dependencies
├── package.json                  # JS dependencies
├── deploy.sh                     # Bash deploy script
├── README.md                     # Full documentation
├── INSTALLATION_GUIDE.md         # Installation guide
└── DOWNLOAD_GUIDE.md            # This file
```

---

## 🚀 **Quick Start Sau Khi Download**

### **Từ ZIP File:**

```bash
# 1. Extract ZIP
unzip laravel-repair-service.zip -d repair-service
cd repair-service

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database
nano .env  # Edit DB_* values

# 5. Run installer
# Option A: Web installer
php artisan serve
# Then access: http://localhost:8000/installer

# Option B: Command line
php artisan deploy:full
npm run build
```

---

## 📝 **File Sizes**

| File | Size | Bao gồm |
|------|------|---------|
| `laravel-repair-service.zip` | 196KB | Code only (no vendor/) |
| `laravel-repair-full.zip` | 60-80MB | Code + vendor/ + node_modules/ |
| Git clone | ~500KB | Code + history |

---

## 💡 **Khuyến Nghị**

**Cho Development:**
- ✅ Dùng Git Clone (để có thể update dễ dàng)
- ✅ Hoặc download ZIP nhỏ và chạy `composer install`

**Cho Production:**
- ✅ Dùng Git Clone
- ✅ Chạy `composer install --no-dev --optimize-autoloader`
- ✅ Chạy `npm run build`

**Cho Testing:**
- ✅ Download ZIP đầy đủ (nếu muốn test nhanh)

---

## 🔗 **Links Hữu Ích**

- **Web Installer**: `http://your-domain.com/installer`
- **Admin Dashboard**: `http://your-domain.com/admin/dashboard`
- **API Documentation**: Coming soon
- **Video Tutorial**: Coming soon

---

## 📞 **Support**

Nếu gặp vấn đề khi download hoặc setup:

1. Check `README.md` - Full documentation
2. Check `INSTALLATION_GUIDE.md` - Step by step guide
3. Check Laravel logs: `storage/logs/laravel.log`

---

**Happy Coding! 🎉**
