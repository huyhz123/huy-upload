<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class LiveMonitorService
{
    protected array $config;
    protected array $monitorData = [];
    protected string $cacheKey = 'cicd_monitor_data';

    public function __construct()
    {
        $this->config = config('cicd.monitoring');
        $this->loadMonitorData();
    }

    /**
     * Monitor project 24/7
     */
    public function monitor(): array
    {
        $this->monitorData = [
            'timestamp' => now()->toDateTimeString(),
            'modules' => $this->discoverModules(),
            'menu_status' => $this->checkMenuStatus(),
            'function_status' => $this->checkFunctionStatus(),
            'ui_status' => $this->checkUIStatus(),
            'git_status' => $this->checkGitStatus(),
            'deploy_status' => $this->checkDeployStatus(),
        ];

        $this->saveMonitorData();

        return $this->monitorData;
    }

    /**
     * Discover all modules (old + new)
     */
    protected function discoverModules(): array
    {
        $modules = [];

        // Scan frontend views
        $frontendPath = resource_path('views/frontend');
        if (File::exists($frontendPath)) {
            $files = File::allFiles($frontendPath);
            foreach ($files as $file) {
                $moduleName = $file->getFilenameWithoutExtension();
                $modules[$moduleName] = [
                    'name' => $moduleName,
                    'type' => 'frontend',
                    'path' => str_replace(base_path() . '/', '', $file->getPathname()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'size' => $file->getSize(),
                    'status' => 'detected',
                    'layout_merged' => $this->checkLayoutMerged($file->getPathname()),
                    'css_merged' => $this->checkCSSMerged($file->getPathname()),
                    'js_merged' => $this->checkJSMerged($file->getPathname()),
                    'in_menu' => false, // Will be updated by checkMenuStatus
                    'functions_working' => false, // Will be updated by checkFunctionStatus
                    'ui_validated' => false, // Will be updated by checkUIStatus
                ];
            }
        }

        // Scan admin views
        $adminPath = resource_path('views/admin');
        if (File::exists($adminPath)) {
            $directories = File::directories($adminPath);
            foreach ($directories as $dir) {
                $moduleName = basename($dir);
                if (!isset($modules[$moduleName])) {
                    $modules[$moduleName] = [
                        'name' => $moduleName,
                        'type' => 'admin',
                        'path' => str_replace(base_path() . '/', '', $dir),
                        'modified' => date('Y-m-d H:i:s', filemtime($dir)),
                        'status' => 'detected',
                        'layout_merged' => true, // Admin usually has layout
                        'css_merged' => true,
                        'js_merged' => true,
                        'in_menu' => false,
                        'functions_working' => false,
                        'ui_validated' => false,
                    ];
                }
            }
        }

        return $modules;
    }

    /**
     * Check if layout is merged
     */
    protected function checkLayoutMerged(string $filePath): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        $content = File::get($filePath);

        // Check for @extends or layout usage
        return preg_match('/@extends\([\'"]layouts\.(app|frontend|admin)[\'"]/', $content) > 0;
    }

    /**
     * Check if CSS is merged
     */
    protected function checkCSSMerged(string $filePath): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        $content = File::get($filePath);

        // Check for proper CSS asset loading
        return preg_match('/@vite|asset\([\'"]css\/|mix\([\'"]css\//', $content) > 0 ||
               preg_match('/<link[^>]+href=["\'][^"\']*\.css["\']/', $content) > 0;
    }

    /**
     * Check if JS is merged
     */
    protected function checkJSMerged(string $filePath): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        $content = File::get($filePath);

        // Check for proper JS asset loading
        return preg_match('/@vite|asset\([\'"]js\/|mix\([\'"]js\//', $content) > 0 ||
               preg_match('/<script[^>]+src=["\'][^"\']*\.js["\']/', $content) > 0;
    }

    /**
     * Check menu status
     */
    protected function checkMenuStatus(): array
    {
        $menuFiles = [
            resource_path('views/layouts/navigation.blade.php'),
            resource_path('views/partials/header.blade.php'),
            resource_path('views/partials/sidebar.blade.php'),
            resource_path('views/partials/menu.blade.php'),
        ];

        $menuItems = [];
        $menuContent = '';

        foreach ($menuFiles as $menuFile) {
            if (File::exists($menuFile)) {
                $menuContent .= File::get($menuFile);
            }
        }

        // Parse menu items
        preg_match_all('/route\([\'"]([^\'"]+)[\'"]\)/', $menuContent, $matches);
        if (!empty($matches[1])) {
            $menuItems = array_unique($matches[1]);
        }

        // Update modules with menu status
        foreach ($this->monitorData['modules'] ?? [] as $key => $module) {
            $inMenu = false;
            foreach ($menuItems as $item) {
                if (str_contains($item, $module['name'])) {
                    $inMenu = true;
                    break;
                }
            }
            $this->monitorData['modules'][$key]['in_menu'] = $inMenu;
        }

        return [
            'total_items' => count($menuItems),
            'menu_files_found' => count(array_filter($menuFiles, fn($f) => File::exists($f))),
            'items' => $menuItems,
            'needs_update' => $this->menuNeedsUpdate(),
        ];
    }

    /**
     * Check if menu needs update
     */
    protected function menuNeedsUpdate(): bool
    {
        $modulesNotInMenu = 0;
        foreach ($this->monitorData['modules'] ?? [] as $module) {
            if (!$module['in_menu']) {
                $modulesNotInMenu++;
            }
        }
        return $modulesNotInMenu > 0;
    }

    /**
     * Check function status
     */
    protected function checkFunctionStatus(): array
    {
        $status = [
            'forms' => 0,
            'forms_working' => 0,
            'ajax_calls' => 0,
            'ajax_working' => 0,
            'tables' => 0,
            'dropdowns' => 0,
            'modals' => 0,
            'validations' => 0,
        ];

        // Scan all view files
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());

            // Count forms
            $status['forms'] += preg_match_all('/<form/', $content);

            // Count forms with proper action and CSRF
            $formsWithAction = preg_match_all('/<form[^>]+action=/', $content);
            $formsWithCsrf = preg_match_all('/@csrf|csrf_token/', $content);
            $status['forms_working'] += min($formsWithAction, $formsWithCsrf);

            // Count AJAX calls
            $status['ajax_calls'] += preg_match_all('/fetch\(|axios\.|\.ajax\(|\$\.ajax/', $content);

            // Count tables
            $status['tables'] += preg_match_all('/<table/', $content);

            // Count dropdowns
            $status['dropdowns'] += preg_match_all('/<select/', $content);

            // Count modals
            $status['modals'] += preg_match_all('/modal|Modal/', $content);

            // Count validation
            $status['validations'] += preg_match_all('/required|validate|validation/', $content);
        }

        // Check JS files for AJAX working status
        $jsPath = public_path('js');
        if (File::exists($jsPath)) {
            $jsFiles = File::allFiles($jsPath);
            foreach ($jsFiles as $file) {
                $content = File::get($file->getPathname());
                // Count AJAX calls that have error handling
                $ajaxWithError = preg_match_all('/\.catch\(|\.then\([^)]+catch/', $content);
                $status['ajax_working'] += $ajaxWithError;
            }
        }

        return $status;
    }

    /**
     * Check UI status
     */
    protected function checkUIStatus(): array
    {
        $issues = [];

        // Check for common UI issues
        $viewFiles = File::allFiles(resource_path('views'));

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path('views/'), '', $file->getPathname());

            // Check for inline styles (should use CSS classes)
            if (preg_match_all('/style=["\']/i', $content) > 5) {
                $issues[] = [
                    'file' => $relativePath,
                    'type' => 'inline_styles',
                    'severity' => 'warning',
                    'message' => 'Too many inline styles, should use CSS classes',
                ];
            }

            // Check for responsive classes
            if (!preg_match('/sm:|md:|lg:|xl:|2xl:/', $content) && strlen($content) > 1000) {
                $issues[] = [
                    'file' => $relativePath,
                    'type' => 'responsive',
                    'severity' => 'warning',
                    'message' => 'No responsive classes detected',
                ];
            }

            // Check for accessibility
            if (preg_match('/<img[^>]+>/', $content) && !preg_match('/<img[^>]+alt=/', $content)) {
                $issues[] = [
                    'file' => $relativePath,
                    'type' => 'accessibility',
                    'severity' => 'error',
                    'message' => 'Images missing alt attributes',
                ];
            }
        }

        return [
            'total_issues' => count($issues),
            'errors' => count(array_filter($issues, fn($i) => $i['severity'] === 'error')),
            'warnings' => count(array_filter($issues, fn($i) => $i['severity'] === 'warning')),
            'issues' => $issues,
        ];
    }

    /**
     * Check git status
     */
    protected function checkGitStatus(): array
    {
        try {
            $status = trim(shell_exec('git status --short 2>&1') ?? '');
            $branch = trim(shell_exec('git branch --show-current 2>&1') ?? '');
            $lastCommit = trim(shell_exec('git log -1 --oneline 2>&1') ?? '');
            $unpushed = trim(shell_exec('git log --branches --not --remotes --oneline 2>&1') ?? '');

            return [
                'branch' => $branch,
                'clean' => empty($status),
                'modified_files' => $status ? count(explode("\n", $status)) : 0,
                'last_commit' => $lastCommit,
                'has_unpushed' => !empty($unpushed),
                'unpushed_count' => $unpushed ? count(explode("\n", $unpushed)) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check deploy status
     */
    protected function checkDeployStatus(): array
    {
        $deployConfig = config('cicd.deployment');

        return [
            'enabled' => $deployConfig['enabled'] ?? false,
            'method' => $deployConfig['method'] ?? 'manual',
            'last_deploy' => Cache::get('cicd_last_deploy', null),
            'status' => Cache::get('cicd_deploy_status', 'idle'),
        ];
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData(): array
    {
        $data = $this->loadMonitorData();

        if (empty($data)) {
            return $this->monitor();
        }

        // Calculate statistics
        $modules = $data['modules'] ?? [];
        $totalModules = count($modules);
        $layoutMerged = count(array_filter($modules, fn($m) => $m['layout_merged']));
        $cssMerged = count(array_filter($modules, fn($m) => $m['css_merged']));
        $jsMerged = count(array_filter($modules, fn($m) => $m['js_merged']));
        $inMenu = count(array_filter($modules, fn($m) => $m['in_menu']));

        $data['statistics'] = [
            'total_modules' => $totalModules,
            'layout_merged_percent' => $totalModules > 0 ? round(($layoutMerged / $totalModules) * 100) : 0,
            'css_merged_percent' => $totalModules > 0 ? round(($cssMerged / $totalModules) * 100) : 0,
            'js_merged_percent' => $totalModules > 0 ? round(($jsMerged / $totalModules) * 100) : 0,
            'in_menu_percent' => $totalModules > 0 ? round(($inMenu / $totalModules) * 100) : 0,
            'ui_issues' => $data['ui_status']['total_issues'] ?? 0,
            'git_clean' => $data['git_status']['clean'] ?? false,
        ];

        return $data;
    }

    /**
     * Save monitor data to cache
     */
    protected function saveMonitorData(): void
    {
        Cache::put($this->cacheKey, $this->monitorData, now()->addDay());
    }

    /**
     * Load monitor data from cache
     */
    protected function loadMonitorData(): array
    {
        return Cache::get($this->cacheKey, []);
    }
}
