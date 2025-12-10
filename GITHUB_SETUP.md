# 🚀 Hướng Dẫn Push Code Lên GitHub

## 📋 **BƯỚC 1: Tạo Repository Trên GitHub**

### **Cách 1: Qua Web Interface**

1. Đăng nhập vào https://github.com
2. Click nút **"+"** ở góc phải trên → **"New repository"**
3. Điền thông tin:
   ```
   Repository name: laravel-repair-service
   Description: Laravel 11 - Repair Service & File/Course Sales System
   Visibility: Public hoặc Private
   
   ❌ KHÔNG chọn: Initialize with README
   ❌ KHÔNG chọn: Add .gitignore
   ❌ KHÔNG chọn: Choose a license
   ```
4. Click **"Create repository"**

### **Cách 2: Qua GitHub CLI** (nếu có `gh` installed)

```bash
gh repo create laravel-repair-service \
    --public \
    --description "Laravel 11 - Repair Service & File/Course Sales System" \
    --source=.
```

---

## 📋 **BƯỚC 2: Add GitHub Remote**

Sau khi tạo repository, GitHub sẽ hiển thị URL. Copy URL đó.

### **Format URL:**

**HTTPS:**
```
https://github.com/YOUR_USERNAME/laravel-repair-service.git
```

**SSH:**
```
git@github.com:YOUR_USERNAME/laravel-repair-service.git
```

### **Add Remote:**

```bash
# Kiểm tra remote hiện tại
git remote -v

# Add GitHub remote
git remote add github https://github.com/YOUR_USERNAME/laravel-repair-service.git

# Hoặc nếu muốn thay thế origin
git remote set-url origin https://github.com/YOUR_USERNAME/laravel-repair-service.git

# Verify
git remote -v
```

---

## 📋 **BƯỚC 3: Push Code Lên GitHub**

```bash
# Push tất cả branches
git push github --all

# Hoặc push branch hiện tại
git push github claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM

# Set upstream để push dễ hơn sau này
git push -u github claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM

# Push tags (nếu có)
git push github --tags
```

---

## 📋 **BƯỚC 4: Tạo Main/Master Branch**

GitHub thường dùng `main` hoặc `master` làm default branch.

```bash
# Tạo branch main từ branch hiện tại
git checkout -b main

# Push lên GitHub
git push -u github main

# Hoặc rename branch hiện tại thành main
git branch -m claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM main
git push -u github main
```

---

## 🔧 **SCRIPT TỰ ĐỘNG**

Tạo file `push-to-github.sh`:

```bash
#!/bin/bash

echo "🚀 GitHub Setup Script"
echo "====================="
echo ""

# Check if GitHub URL is provided
if [ -z "$1" ]; then
    echo "❌ Error: GitHub URL not provided"
    echo ""
    echo "Usage: ./push-to-github.sh <github-url>"
    echo "Example: ./push-to-github.sh https://github.com/username/repo.git"
    exit 1
fi

GITHUB_URL=$1

echo "📍 Adding GitHub remote..."
git remote add github "$GITHUB_URL" 2>/dev/null || git remote set-url github "$GITHUB_URL"

echo "✅ Remote added/updated"
echo ""

echo "📤 Pushing code to GitHub..."
git push -u github claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM

echo ""
echo "🎉 Done! Your code is now on GitHub!"
echo ""
echo "GitHub URL: $GITHUB_URL"
echo ""
```

**Sử dụng:**
```bash
chmod +x push-to-github.sh
./push-to-github.sh https://github.com/YOUR_USERNAME/laravel-repair-service.git
```

---

## 📋 **BƯỚC 5: Setup GitHub Actions (Optional)**

Tạo file `.github/workflows/laravel.yml`:

```yaml
name: Laravel CI/CD

on:
  push:
    branches: [ main, master ]
  pull_request:
    branches: [ main, master ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, bcmath, gd
        
    - name: Install Dependencies
      run: composer install --no-interaction --prefer-dist
      
    - name: Run Tests
      run: php artisan test
```

---

## 🔐 **Authentication**

### **HTTPS (Username + Password/Token):**

```bash
# GitHub sẽ hỏi username và password
# Password = Personal Access Token (PAT)

# Tạo PAT tại:
# https://github.com/settings/tokens
# Permissions: repo (full control)
```

### **SSH (Recommended):**

```bash
# 1. Tạo SSH key
ssh-keygen -t ed25519 -C "your_email@example.com"

# 2. Copy public key
cat ~/.ssh/id_ed25519.pub

# 3. Add vào GitHub:
# https://github.com/settings/keys
# Click "New SSH key" và paste

# 4. Test connection
ssh -T git@github.com

# 5. Use SSH URL
git remote set-url github git@github.com:YOUR_USERNAME/laravel-repair-service.git
```

---

## 📝 **Complete Commands Reference**

```bash
# 1. Kiểm tra status
git status

# 2. Add GitHub remote
git remote add github https://github.com/YOUR_USERNAME/laravel-repair-service.git

# 3. Verify remote
git remote -v

# 4. Push branch hiện tại
git push -u github claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM

# 5. Push tất cả branches
git push github --all

# 6. Push tags
git push github --tags

# 7. Set default remote
git remote set-url origin https://github.com/YOUR_USERNAME/laravel-repair-service.git

# 8. Future pushes (sau khi set upstream)
git push
```

---

## 🎯 **Quick Start (Tóm Tắt)**

```bash
# Tạo repo trên GitHub trước
# Sau đó chạy:

cd /home/user/huy-upload

# Add remote
git remote add github https://github.com/YOUR_USERNAME/laravel-repair-service.git

# Push
git push -u github claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM

# Done! 🎉
```

---

## 📋 **Sau Khi Push Lên GitHub**

### **Cập Nhật README.md trên GitHub:**

Add badges:
```markdown
# Laravel Repair Service

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Installation Guide](INSTALLATION_GUIDE.md) | [Download Guide](DOWNLOAD_GUIDE.md)
```

### **Setup GitHub Pages (Optional):**

1. Go to Settings → Pages
2. Source: Deploy from branch
3. Branch: main / docs (if you have documentation)

### **Enable Issues:**

Settings → Features → Issues ✓

### **Add Topics:**

- laravel
- php
- repair-service
- e-commerce
- file-sales
- course-platform

---

## ❓ **Troubleshooting**

### Issue: "Authentication failed"

**Solution:**
```bash
# Use Personal Access Token instead of password
# Create at: https://github.com/settings/tokens
```

### Issue: "Remote already exists"

**Solution:**
```bash
# Remove old remote
git remote remove github

# Add again
git remote add github <url>
```

### Issue: "Push rejected"

**Solution:**
```bash
# Pull first
git pull github main --allow-unrelated-histories

# Then push
git push github
```

---

## 🎉 **Your Repository Will Be Available At:**

```
https://github.com/YOUR_USERNAME/laravel-repair-service
```

**Clone URL:**
```bash
git clone https://github.com/YOUR_USERNAME/laravel-repair-service.git
```

**ZIP Download:**
```
https://github.com/YOUR_USERNAME/laravel-repair-service/archive/refs/heads/main.zip
```

---

**Happy Coding! 🚀**
