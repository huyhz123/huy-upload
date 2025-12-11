# 🚀 CI/CD Automation System - Complete Documentation

## 📋 Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Usage](#usage)
6. [Services](#services)
7. [Examples](#examples)
8. [Troubleshooting](#troubleshooting)

---

## Overview

Hệ thống CI/CD tự động hoàn toàn cho Laravel, tự động hóa toàn bộ quy trình từ backup → scan → fix → git → deploy.

### Architecture

```
┌─────────────────────────────────────────────────────────┐
│                 php artisan cicd:run                    │
└───────────────────┬─────────────────────────────────────┘
                    │
    ┌───────────────┼───────────────┬───────────────┐
    │               │               │               │
┌───▼───┐    ┌─────▼─────┐   ┌────▼────┐   ┌─────▼──────┐
│Backup │───▶│  Scanner  │──▶│ AutoFix │──▶│ Git Service│
│Service│    │  Service  │   │ Service │   │            │
└───────┘    └───────────┘   └─────────┘   └────────────┘
                                                  │
                                            ┌─────▼──────┐
                                            │   Report   │
                                            │  Service   │
                                            └────────────┘
```

---

## Features

### ✅ **1. Backup System**
- ✓ Backup files (frontend, backend, resources)
- ✓ Backup database (MySQL, SQLite)
- ✓ Versioning với suffix `_file1`, `_file2`
- ✓ Auto cleanup old backups (configurable retention)
- ✓ Restore functionality

### ✅ **2. Live Scanner**
- ✓ Detect file changes (last 24h)
- ✓ Scan broken links (`href="#"`, empty links)
- ✓ Scan missing routes
- ✓ Scan missing assets (CSS/JS/images)
- ✓ Scan forms without CSRF tokens
- ✓ Scan API endpoints
- ✓ Scan layout errors (@extends, @include)
- ✓ Detect missing modules

### ✅ **3. Auto-Fix**
- ✓ Fix broken links (replace `#` with `javascript:void(0)`)
- ✓ Add missing CSRF tokens to forms
- ✓ Fix asset paths
- ✓ Create missing routes automatically
- ✓ Generate controller stubs
- ✓ Generate view stubs (CRUD: index, create, edit, show)
- ✓ Create placeholder assets

### ✅ **4. Stub Generator**
- ✓ Auto-create missing modules:
  - Sales Orders
  - Purchase Orders
  - Inventory Management
  - Reports
  - User Management
  - Settings
- ✓ Generate complete CRUD structure
- ✓ Create routes, controllers, views
- ✓ Professional Tailwind CSS styling

### ✅ **5. Git Automation**
- ✓ Auto commit changed files
- ✓ Intelligent commit messages
- ✓ Auto push to remote
- ✓ Conflict detection
- ✓ Detailed git logs

### ✅ **6. Reporting**
- ✓ Beautiful HTML reports
- ✓ Statistics dashboard
- ✓ Detailed error logs
- ✓ Fix history
- ✓ Git operation logs

---

## Installation

### Step 1: Files Already Created ✅

The following files have been created in your project:

```
config/
  └── cicd.php                              ← Configuration file

app/Services/CICD/
  ├── BackupService.php                     ← Backup automation
  ├── ScannerService.php                    ← Error detection
  ├── AutoFixService.php                    ← Auto-fix errors
  ├── GitService.php                        ← Git automation
  └── ReportService.php                     ← Reporting system

app/Console/Commands/
  └── CICDRunCommand.php                    ← Main command
```

### Step 2: No Additional Dependencies Required ✅

All services use built-in Laravel features. No need to install additional packages!

---

## Configuration

Edit `config/cicd.php` to customize your CI/CD pipeline:

### Backup Configuration

```php
'backup' => [
    'enabled' => true,
    'path' => storage_path('backups'),
    'database' => true,
    'files' => true,
    'retention_days' => 30,

    'directories' => [
        'frontend' => [
            'resources/views/frontend',
            'resources/css',
            'resources/js',
            'public/css',
            'public/js',
        ],
        'backend' => [
            'app/Http/Controllers',
            'app/Models',
            'routes',
        ],
    ],
],
```

### Scanner Configuration

```php
'scanner' => [
    'enabled' => true,
    'watch_directories' => [
        'resources/views',
        'app/Http/Controllers',
        'routes',
    ],
],
```

### Git Configuration

```php
'git' => [
    'enabled' => true,
    'auto_commit' => true,
    'auto_push' => true,
    'branch' => 'claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM',
    'remote' => 'origin',
],
```

---

## Usage

### Basic Usage

Run the complete CI/CD pipeline once:

```bash
php artisan cicd:run
```

### Skip Backup

```bash
php artisan cicd:run --skip-backup
```

### Skip Git

```bash
php artisan cicd:run --skip-git
```

### Watch Mode (Continuous Monitoring)

Run CI/CD every 60 seconds:

```bash
php artisan cicd:run --watch
```

Custom interval (e.g., every 5 minutes):

```bash
php artisan cicd:run --watch --interval=300
```

---

## Services

### 1. BackupService

```php
use App\Services\CICD\BackupService;

$backup = new BackupService();

// Create full backup
$result = $backup->createFullBackup();

// Get backup list
$backups = $backup->getBackupList();

// Restore from backup
$backup->restore('2025-12-11_10-30-00');
```

### 2. ScannerService

```php
use App\Services\CICD\ScannerService;

$scanner = new ScannerService();

// Run comprehensive scan
$report = $scanner->scan();

// Check if errors exist
if ($scanner->hasErrors()) {
    $errorCount = $scanner->getErrorCount();
}
```

### 3. AutoFixService

```php
use App\Services\CICD\AutoFixService;

$autoFix = new AutoFixService();

// Fix all errors from scan report
$result = $autoFix->fix($scanReport);
```

### 4. GitService

```php
use App\Services\CICD\GitService;

$git = new GitService();

// Commit and push
$result = $git->commitAndPush([
    'resources/views/admin/dashboard.blade.php',
    'app/Http/Controllers/Admin/DashboardController.php',
]);
```

### 5. ReportService

```php
use App\Services\CICD\ReportService;

$reporter = new ReportService();

// Generate HTML report
$reportFile = $reporter->generate([
    'backup' => $backupReport,
    'scan' => $scanReport,
    'autofix' => $autoFixReport,
    'git' => $gitReport,
]);
```

---

## Examples

### Example 1: Daily Auto-Fix

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Run CI/CD daily at 2 AM
    $schedule->command('cicd:run')->dailyAt('02:00');

    // Or every hour
    $schedule->command('cicd:run')->hourly();
}
```

### Example 2: Git Hook Integration

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash
php artisan cicd:run --skip-git
```

### Example 3: Manual Backup Before Deployment

```bash
php artisan cicd:run --skip-git
git add .
git commit -m "Manual deployment"
git push
```

---

## Output Example

When you run `php artisan cicd:run`:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 CI/CD AUTOMATION SYSTEM
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📦 Step 1: Creating Backup...
✓ Backup completed: 182 files, 12.5 MB

🔍 Step 2: Scanning for errors and changes...
   • File changes: 5
   • Errors found: 12
   • Missing modules: 3
⚠  12 errors detected

🔧 Step 3: Auto-fixing errors...
   • Errors fixed: 10
   • Stubs created: 15
   • Files modified: 8
✓ Auto-fix completed

📝 Step 4: Committing and pushing to Git...
   • Commit: a1b2c3d4
   • Pushed to: claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM
✓ Git operations completed

📊 Step 5: Generating report...
✓ Report generated: storage/cicd/reports/cicd_report_2025-12-11_10-30-00.html

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ CI/CD pipeline completed in 15.43s
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## Generated Files Structure

After running CI/CD, you'll have:

```
storage/
├── backups/
│   └── 2025-12-11_10-30-00/
│       ├── resources/
│       │   └── views/
│       │       └── dashboard.blade.php_file1
│       ├── app/
│       │   └── Http/
│       │       └── Controllers/
│       └── database_2025-12-11_10-30-00.sql
│
├── cicd/
│   └── reports/
│       └── cicd_report_2025-12-11_10-30-00.html
│
└── logs/
    └── cicd.log
```

---

## Troubleshooting

### Issue: "Class not found"

**Solution**: Clear config cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: "Permission denied" on backups

**Solution**: Fix permissions

```bash
chmod -R 755 storage/backups
chmod -R 755 storage/cicd
```

### Issue: Git push fails

**Solution**: Check git configuration in `config/cicd.php`

```php
'git' => [
    'enabled' => true,
    'branch' => 'your-branch-name',
    'remote' => 'origin',
],
```

### Issue: Database backup fails

**Solution**: Ensure `mysqldump` is available or use manual backup mode

```bash
which mysqldump
```

---

## Advanced Features

### Customize Module Stubs

Edit `config/cicd.php` → `stub_generator` → `modules`:

```php
'modules' => [
    'my-custom-module' => [
        'title' => 'My Custom Module',
        'route' => 'my-module',
        'controller' => 'MyModuleController',
        'model' => 'MyModule',
        'icon' => 'star',
    ],
],
```

### Add Custom Error Patterns

Edit `config/cicd.php` → `scanner` → `error_patterns`:

```php
'error_patterns' => [
    'custom_error' => [
        'pattern_here',
    ],
],
```

---

## Security Best Practices

1. **Never commit sensitive data**:
   - Add `storage/backups` to `.gitignore`
   - Add `storage/cicd` to `.gitignore`

2. **Use environment variables**:
   ```env
   CICD_GIT_BRANCH=your-branch
   CICD_GIT_REMOTE=origin
   ```

3. **Limit backup retention**:
   ```php
   'retention_days' => 7, // Keep only 1 week
   ```

---

## Performance Tips

1. **Skip backup for minor changes**:
   ```bash
   php artisan cicd:run --skip-backup
   ```

2. **Use watch mode with longer intervals**:
   ```bash
   php artisan cicd:run --watch --interval=600  # 10 minutes
   ```

3. **Exclude large directories from backup**:
   Edit `config/cicd.php` and remove unwanted directories

---

## Support

For issues or questions:
1. Check logs: `storage/logs/cicd.log`
2. Review report: `storage/cicd/reports/`
3. Enable debug mode in `.env`: `APP_DEBUG=true`

---

## Changelog

### Version 1.0.0 (2025-12-11)

✅ **Initial Release**:
- Backup Service (files + database)
- Scanner Service (error detection)
- AutoFix Service (automatic fixes)
- Git Service (auto commit & push)
- Report Service (HTML reports)
- Main CI/CD Command
- Watch mode support
- Complete documentation

---

**Happy Automating! 🎉**

For more information, see individual service files in `app/Services/CICD/`
