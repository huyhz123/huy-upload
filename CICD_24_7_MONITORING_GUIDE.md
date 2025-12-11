# 🚀 CI/CD 24/7 Monitoring System - Complete Guide

## 🎯 Overview

**AI-Powered 24/7 Monitoring & Automation System** với Real-time Dashboard trực quan để quản lý toàn bộ project Laravel tự động.

---

## ✨ **TÍNH NĂNG MỚI**

### **1. Live Monitor Service** 👁️
- Theo dõi 24/7 toàn bộ project
- Phát hiện modules cũ + mới tự động
- Kiểm tra layout/CSS/JS merge status
- Kiểm tra modules có trong menu chưa
- Monitor function status (forms, AJAX, tables, dropdowns)
- Monitor UI/UX issues
- Monitor Git status
- Real-time cache với statistics

### **2. Layout Merger Service** 🎨
- Tự động merge layout cho modules mới
- Wrap content với `@extends('layouts.app')`
- Extract và clean inline styles
- Add proper CSS/JS asset loading
- Đồng bộ frontend với backend layout

### **3. Menu Updater Service** 📋
- Tự động phát hiện modules chưa có trong menu
- Tạo navigation file nếu chưa tồn tại
- Thêm modules vào menu (desktop + mobile)
- Sắp xếp hợp lý với phân quyền
- Responsive navigation

### **4. Real-time Dashboard** 📊
- Web interface trực quan tại `/cicd/dashboard`
- Statistics cards với color coding (green/yellow/red)
- Modules list với status indicators
- Function status monitoring
- UI/UX issues tracking
- Git status display
- Auto-refresh every 30 seconds
- Manual scan trigger
- Real-time API endpoints

---

## 📦 **CÀI ĐẶT**

### **Files Đã Tạo:**

```
app/Services/CICD/
├── BackupService.php           ← Backup tự động
├── ScannerService.php          ← Error detection
├── AutoFixService.php          ← Auto-fix errors
├── GitService.php              ← Git automation
├── ReportService.php           ← HTML reports
├── LiveMonitorService.php      ← NEW: 24/7 monitoring
├── LayoutMergerService.php     ← NEW: Layout/CSS/JS merger
└── MenuUpdaterService.php      ← NEW: Menu updater

app/Http/Controllers/
└── CICDDashboardController.php ← NEW: Dashboard controller

resources/views/cicd/
└── dashboard.blade.php         ← NEW: Real-time dashboard

app/Console/Commands/
└── CICDRunCommand.php          ← Updated với new services

routes/
└── web.php                     ← Updated với dashboard routes
```

---

## 🚀 **CÁCH SỬ DỤNG**

### **1. Chạy CI/CD Pipeline (Command Line)**

#### **Chạy 1 lần:**
```bash
php artisan cicd:run
```

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 CI/CD AUTOMATION SYSTEM
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

👁️  Step 0: Running Live Monitor...
✓ Detected 15 modules

📦 Step 1: Creating Backup...
✓ Backup completed: 182 files, 12.5 MB

🔍 Step 2: Scanning for errors and changes...
   • File changes: 5
   • Errors found: 12
   • Missing modules: 3

🔧 Step 3: Auto-fixing errors...
   • Errors fixed: 10
   • Stubs created: 15
   • Files modified: 8

🎨 Step 3.5: Merging layout, CSS, and JS...
   • Layout merged: 3
   • CSS merged: 3
   • JS merged: 2
✓ Layout merge completed

📋 Step 3.6: Updating menu/navigation...
   • Modules added to menu: 5
✓ Menu update completed

📝 Step 4: Committing and pushing to Git...
   • Commit: a1b2c3d4
   • Pushed to: main
✓ Git operations completed

📊 Step 5: Generating report...
✓ Report generated: storage/cicd/reports/cicd_report_2025-12-11.html

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ CI/CD pipeline completed in 18.52s
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

#### **Chế độ Watch (24/7):**
```bash
php artisan cicd:run --watch
```

**Chạy mỗi 5 phút:**
```bash
php artisan cicd:run --watch --interval=300
```

---

### **2. Truy Cập Dashboard (Web Interface)**

#### **URL:**
```
http://your-domain.com/cicd/dashboard
```

**Authentication:** Cần login với role `admin`

#### **Dashboard Features:**

**Statistics Cards:**
- 📦 **Total Modules** - Tổng số modules
- 🎨 **Layout Merged** - % modules đã merge layout
- 📋 **In Menu** - % modules có trong menu
- ⚠️  **UI Issues** - Số lỗi UI/UX

**Modules Table:**
- Module name & path
- Type (admin/frontend)
- Layout/CSS/JS status (✓/✗)
- In menu status
- Last modified time

**Function Status:**
- Forms count & working status
- AJAX calls count
- Tables & dropdowns count

**UI/UX Status:**
- Total issues
- Errors vs Warnings
- Issue details

**Git Status:**
- Current branch
- Clean/Modified status
- Unpushed commits count

**Actions:**
- 🔄 **Refresh** - Manual refresh data
- ▶️ **Run Scan** - Trigger new scan immediately
- ⏰ **Auto-refresh** - Every 30 seconds

---

## 🎨 **DASHBOARD SCREENSHOTS**

### **Statistics Overview:**
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│ Total       │ Layout      │ In Menu     │ UI Issues   │
│ Modules     │ Merged      │             │             │
│    15       │    100%     │    80%      │      3      │
│             │   (Green)   │  (Yellow)   │    (Red)    │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

### **Modules Status:**
```
Module          Type     Layout  CSS  JS  Menu
─────────────────────────────────────────────
sales-orders    admin      ✓     ✓   ✓    ✓
dashboard       frontend   ✓     ✓   ✓    ✓
reports         admin      ✗     ✗   ✗    ✗  ← Needs attention!
```

---

## 🔧 **QUY TRÌNH TỰ ĐỘNG**

```
┌─────────────────────────────────────────────────────────┐
│                   CONTINUOUS LOOP                       │
└─────────────────────────────────────────────────────────┘
              │
              ▼
    ┌─────────────────┐
    │  Live Monitor   │ ← Detect all modules (old + new)
    │     Service     │   Check layout/CSS/JS/menu status
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │  Backup Files   │ ← Backup before changes
    │   & Database    │   Versioning: _file1, _file2
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │  Scan Errors    │ ← Find broken links, missing CSRF
    │   & Changes     │   Detect API errors, missing routes
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │   Auto-Fix      │ ← Fix errors automatically
    │    Errors       │   Create missing routes/controllers
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │ Layout Merger   │ ← Wrap with @extends('layouts.app')
    │   CSS/JS Fix    │   Merge styles, clean inline CSS
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │ Menu Updater    │ ← Add modules to navigation
    │  Navigation     │   Desktop + Mobile menus
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │  Git Commit     │ ← Smart commit messages
    │   & Push        │   Auto push to GitHub
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │ Update Cache    │ ← Save to cache for dashboard
    │  & Dashboard    │   Real-time display
    └────────┬────────┘
             │
             └──────────────┐
                            │
             ┌──────────────┘
             │
     [Wait interval seconds]
             │
             └────────────► REPEAT
```

---

## 📊 **API ENDPOINTS**

### **GET /cicd/api/data**
Lấy dashboard data real-time

**Response:**
```json
{
    "timestamp": "2025-12-11 10:30:00",
    "statistics": {
        "total_modules": 15,
        "layout_merged_percent": 100,
        "css_merged_percent": 100,
        "js_merged_percent": 93,
        "in_menu_percent": 80,
        "ui_issues": 3
    },
    "modules": { ... },
    "function_status": { ... },
    "ui_status": { ... },
    "git_status": { ... }
}
```

### **POST /cicd/api/scan**
Trigger manual scan

**Response:**
```json
{
    "success": true,
    "message": "Scan completed",
    "data": { ... }
}
```

### **GET /cicd/api/module/{name}**
Lấy chi tiết module

### **GET /cicd/api/logs**
Lấy 100 log entries gần nhất

---

## 🎯 **USE CASES**

### **1. Module Mới Được Thêm Vào**
```
User thêm file: resources/views/frontend/sales-orders.blade.php
                              ↓
            [Live Monitor phát hiện module mới]
                              ↓
          [Backup file trước khi chỉnh sửa]
                              ↓
         [Layout Merger wrap với @extends]
                              ↓
    [Menu Updater thêm vào navigation menu]
                              ↓
            [Auto commit + push lên Git]
                              ↓
            [Dashboard hiển thị module mới]
                              ↓
              [User truy cập menu → Thấy ngay!]
```

### **2. Form Thiếu CSRF Token**
```
Scanner phát hiện form không có @csrf
              ↓
AutoFix thêm @csrf vào form
              ↓
Commit changes với message "fix: Add CSRF tokens to forms"
              ↓
Push lên GitHub
              ↓
Dashboard shows: "✓ CSRF tokens fixed"
```

### **3. Module Chưa Có Trong Menu**
```
LiveMonitor: sales-orders module tồn tại nhưng không trong menu
              ↓
MenuUpdater: Thêm link vào navigation.blade.php
              ↓
Git commit: "feat: Add sales-orders to navigation menu"
              ↓
Dashboard: in_menu_percent = 100%
```

---

## ⚙️ **CẤU HÌNH**

### **Monitoring Interval:**
Edit `config/cicd.php`:
```php
'monitoring' => [
    'enabled' => true,
    'watch_interval' => 60, // seconds
],
```

### **Dashboard Auto-refresh:**
Edit `resources/views/cicd/dashboard.blade.php`:
```javascript
startAutoRefresh() {
    setInterval(() => {
        this.refreshData();
    }, 30000); // 30 seconds
}
```

---

## 🔐 **BẢO MẬT**

### **Dashboard Access:**
- ✅ Protected by auth middleware
- ✅ Requires admin role
- ✅ CSRF protection on all POST requests

### **API Endpoints:**
- ✅ Same authentication as dashboard
- ✅ CORS configured
- ✅ Rate limiting recommended

**Add to `.env`:**
```env
CICD_DASHBOARD_ENABLED=true
```

---

## 🚨 **TROUBLESHOOTING**

### **Issue: Dashboard không load**
**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### **Issue: Modules không được detect**
**Solution:** Check paths in `LiveMonitorService.php`
```php
$frontendPath = resource_path('views/frontend');
$adminPath = resource_path('views/admin');
```

### **Issue: Layout merge không hoạt động**
**Solution:** Ensure `layouts/app.blade.php` exists

---

## 📈 **PERFORMANCE**

### **Optimization Tips:**

1. **Cache Duration:**
```php
Cache::put($this->cacheKey, $this->monitorData, now()->addHour());
```

2. **Scan Frequency:**
```bash
# Reduce interval for production
php artisan cicd:run --watch --interval=300  # 5 minutes
```

3. **Dashboard Refresh:**
```javascript
// Increase interval for less load
setInterval(() => this.refreshData(), 60000); // 1 minute
```

---

## 🎉 **KẾT LUẬN**

Bây giờ bạn có:

✅ **Live monitoring 24/7** tự động
✅ **Auto layout/CSS/JS merge** cho tất cả modules
✅ **Auto menu update** đầy đủ
✅ **Real-time dashboard** trực quan
✅ **Complete automation** từ detect → fix → git → display

**Chỉ cần:**
1. Start monitoring: `php artisan cicd:run --watch`
2. Open dashboard: `http://your-domain.com/cicd/dashboard`
3. Sit back and watch! ☕

---

**Happy Monitoring! 🎊**
