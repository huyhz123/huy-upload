<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CI/CD Automation Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all configuration for the automated CI/CD system
    | including backup, scanning, auto-fix, testing, git, and deployment.
    |
    */

    // Backup Configuration
    'backup' => [
        'enabled' => true,
        'path' => storage_path('backups'),
        'database' => true,
        'files' => true,
        'retention_days' => 30, // Keep backups for 30 days

        // Directories to backup
        'directories' => [
            'frontend' => [
                'resources/views/frontend',
                'resources/css',
                'resources/js',
                'public/css',
                'public/js',
                'public/images',
            ],
            'backend' => [
                'app/Http/Controllers',
                'app/Models',
                'routes',
                'database/migrations',
            ],
        ],
    ],

    // Scanner Configuration
    'scanner' => [
        'enabled' => true,
        'watch_directories' => [
            'resources/views',
            'resources/css',
            'resources/js',
            'public',
            'app/Http/Controllers',
            'routes',
        ],

        // Error detection patterns
        'error_patterns' => [
            'broken_links' => [
                'href=["\']\#["\']',
                'href=["\']{2}',
                'src=["\']{2}',
            ],
            'missing_csrf' => [
                '<form[^>]+method=["\']post["\'][^>]*>(?!.*@csrf)',
            ],
            'broken_assets' => [
                'asset\(["\']([^"\']+)["\']\)',
                'mix\(["\']([^"\']+)["\']\)',
            ],
        ],

        // API endpoint patterns to scan
        'api_patterns' => [
            'fetch\(["\']([^"\']+)["\']',
            'axios\.(get|post|put|delete)\(["\']([^"\']+)["\']',
            '\.ajax\(\{[^}]*url:\s*["\']([^"\']+)["\']',
        ],
    ],

    // Auto-Fix Configuration
    'autofix' => [
        'enabled' => true,
        'fix_broken_links' => true,
        'fix_missing_csrf' => true,
        'fix_asset_paths' => true,
        'create_missing_routes' => true,
        'create_missing_controllers' => true,
        'create_missing_views' => true,
    ],

    // Stub Generator Configuration
    'stub_generator' => [
        'enabled' => true,
        'modules' => [
            'sales-orders' => [
                'title' => 'Sales Orders',
                'route' => 'sales-orders',
                'controller' => 'SalesOrderController',
                'model' => 'SalesOrder',
                'icon' => 'shopping-cart',
            ],
            'purchase-orders' => [
                'title' => 'Purchase Orders',
                'route' => 'purchase-orders',
                'controller' => 'PurchaseOrderController',
                'model' => 'PurchaseOrder',
                'icon' => 'shopping-bag',
            ],
            'inventory' => [
                'title' => 'Inventory Management',
                'route' => 'inventory',
                'controller' => 'InventoryController',
                'model' => 'Inventory',
                'icon' => 'archive',
            ],
            'reports' => [
                'title' => 'Reports',
                'route' => 'reports',
                'controller' => 'ReportController',
                'model' => null,
                'icon' => 'chart-bar',
            ],
            'users' => [
                'title' => 'User Management',
                'route' => 'users',
                'controller' => 'UserManagementController',
                'model' => 'User',
                'icon' => 'users',
            ],
            'settings' => [
                'title' => 'Settings',
                'route' => 'settings',
                'controller' => 'SettingsController',
                'model' => 'Setting',
                'icon' => 'cog',
            ],
        ],
    ],

    // QA/Testing Configuration
    'qa' => [
        'enabled' => true,
        'test_routes' => true,
        'test_api_endpoints' => true,
        'test_forms' => true,
        'test_links' => true,
        'test_assets' => true,
        'test_layout' => true,

        // Browser testing (requires Chrome/Chromium)
        'browser_testing' => false,
        'browser_headless' => true,
    ],

    // Git Configuration
    'git' => [
        'enabled' => true,
        'auto_commit' => true,
        'auto_push' => true,
        'branch' => env('CICD_GIT_BRANCH', 'claude/laravel-repair-service-site-017WCFWxmLQZu6YFaGEwghdM'),
        'remote' => env('CICD_GIT_REMOTE', 'origin'),
        'commit_message_prefix' => '[CI/CD Auto]',

        // Conflict resolution
        'auto_resolve_conflicts' => true,
        'create_temp_branch' => true,
    ],

    // Deployment Configuration
    'deployment' => [
        'enabled' => false, // Disable by default for safety
        'method' => env('CICD_DEPLOY_METHOD', 'manual'), // manual, ssh, ftp, git

        // SSH Deployment
        'ssh' => [
            'host' => env('CICD_SSH_HOST'),
            'port' => env('CICD_SSH_PORT', 22),
            'user' => env('CICD_SSH_USER'),
            'key_path' => env('CICD_SSH_KEY'),
            'deploy_path' => env('CICD_DEPLOY_PATH', '/var/www/html'),
        ],

        // Pre-deployment commands
        'pre_deploy' => [
            'php artisan down',
            'git pull origin main',
            'composer install --no-dev --optimize-autoloader',
            'npm install',
            'npm run build',
        ],

        // Post-deployment commands
        'post_deploy' => [
            'php artisan migrate --force',
            'php artisan config:cache',
            'php artisan route:cache',
            'php artisan view:cache',
            'php artisan up',
        ],

        // Database migration
        'run_migrations' => true,
        'backup_before_deploy' => true,
    ],

    // Reporting Configuration
    'reporting' => [
        'enabled' => true,
        'log_path' => storage_path('logs/cicd.log'),
        'report_path' => storage_path('cicd/reports'),
        'format' => 'html', // html, json, markdown
        'email_reports' => false,
        'email_to' => env('CICD_REPORT_EMAIL'),
    ],

    // Monitoring Configuration
    'monitoring' => [
        'enabled' => true,
        'watch_interval' => 60, // seconds
        'file_change_detection' => true,
        'error_detection' => true,
        'performance_monitoring' => true,
    ],
];
