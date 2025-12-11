<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ScannerService
{
    protected array $config;
    protected array $scanReport = [];
    protected array $errors = [];
    protected array $changes = [];
    protected array $missingModules = [];

    public function __construct()
    {
        $this->config = config('cicd.scanner');
    }

    /**
     * Run comprehensive scan
     */
    public function scan(): array
    {
        $this->scanReport = [
            'timestamp' => now()->toDateTimeString(),
            'errors' => [],
            'changes' => [],
            'missing_modules' => [],
            'broken_links' => [],
            'missing_routes' => [],
            'missing_assets' => [],
            'form_errors' => [],
            'api_errors' => [],
            'layout_errors' => [],
        ];

        // Scan for file changes
        $this->scanFileChanges();

        // Scan for errors
        $this->scanForErrors();

        // Scan for missing modules
        $this->scanForMissingModules();

        // Scan for broken links
        $this->scanForBrokenLinks();

        // Scan for missing routes
        $this->scanForMissingRoutes();

        // Scan for missing assets
        $this->scanForMissingAssets();

        // Scan for form errors
        $this->scanForFormErrors();

        // Scan for API errors
        $this->scanForApiErrors();

        // Scan for layout errors
        $this->scanForLayoutErrors();

        return $this->scanReport;
    }

    /**
     * Scan for file changes
     */
    protected function scanFileChanges(): void
    {
        $watchDirs = $this->config['watch_directories'];

        foreach ($watchDirs as $dir) {
            $fullPath = base_path($dir);

            if (!File::exists($fullPath)) {
                continue;
            }

            $files = File::isDirectory($fullPath) ? File::allFiles($fullPath) : [$fullPath];

            foreach ($files as $file) {
                $filePath = is_string($file) ? $file : $file->getPathname();
                $relativePath = str_replace(base_path() . '/', '', $filePath);

                // Check if file is recently modified (last 24 hours)
                if (File::lastModified($filePath) > now()->subDay()->timestamp) {
                    $this->scanReport['changes'][] = [
                        'file' => $relativePath,
                        'modified' => date('Y-m-d H:i:s', File::lastModified($filePath)),
                        'size' => File::size($filePath),
                    ];
                }
            }
        }
    }

    /**
     * Scan for errors
     */
    protected function scanForErrors(): void
    {
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path() . '/', '', $file->getPathname());

            // Check for error patterns
            foreach ($this->config['error_patterns'] as $errorType => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match_all('/' . $pattern . '/i', $content, $matches)) {
                        $this->scanReport['errors'][] = [
                            'type' => $errorType,
                            'file' => $relativePath,
                            'pattern' => $pattern,
                            'matches' => count($matches[0]),
                        ];
                    }
                }
            }
        }
    }

    /**
     * Scan for missing modules
     */
    protected function scanForMissingModules(): void
    {
        $modules = config('cicd.stub_generator.modules');
        $existingRoutes = collect(Route::getRoutes())->map(fn($route) => $route->getName())->filter();

        foreach ($modules as $moduleKey => $moduleConfig) {
            $routeName = $moduleConfig['route'];
            $controller = $moduleConfig['controller'];

            // Check if route exists
            $routeExists = $existingRoutes->contains(fn($name) => str_contains($name, $routeName));

            // Check if controller exists
            $controllerPath = app_path('Http/Controllers/Admin/' . $controller . '.php');
            $controllerExists = File::exists($controllerPath);

            // Check if view exists
            $viewPath = resource_path('views/admin/' . $routeName . '/index.blade.php');
            $viewExists = File::exists($viewPath);

            if (!$routeExists || !$controllerExists || !$viewExists) {
                $this->scanReport['missing_modules'][] = [
                    'module' => $moduleKey,
                    'title' => $moduleConfig['title'],
                    'route_exists' => $routeExists,
                    'controller_exists' => $controllerExists,
                    'view_exists' => $viewExists,
                    'needs_creation' => true,
                ];
            }
        }
    }

    /**
     * Scan for broken links
     */
    protected function scanForBrokenLinks(): void
    {
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path() . '/', '', $file->getPathname());

            // Find route() calls
            if (preg_match_all('/route\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                foreach ($matches[1] as $routeName) {
                    if (!Route::has($routeName)) {
                        $this->scanReport['broken_links'][] = [
                            'file' => $relativePath,
                            'route' => $routeName,
                            'type' => 'missing_route',
                        ];
                    }
                }
            }

            // Find href="#" or empty href
            if (preg_match_all('/href=["\']#["\']|href=["\']{2}/', $content, $matches)) {
                $this->scanReport['broken_links'][] = [
                    'file' => $relativePath,
                    'type' => 'empty_link',
                    'count' => count($matches[0]),
                ];
            }
        }
    }

    /**
     * Scan for missing routes
     */
    protected function scanForMissingRoutes(): void
    {
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);
        $existingRoutes = collect(Route::getRoutes())->map(fn($route) => $route->getName())->filter();

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());

            // Find all route() calls
            if (preg_match_all('/route\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                foreach ($matches[1] as $routeName) {
                    if (!$existingRoutes->contains($routeName)) {
                        $this->scanReport['missing_routes'][] = [
                            'route' => $routeName,
                            'found_in' => str_replace(resource_path() . '/', '', $file->getPathname()),
                        ];
                    }
                }
            }
        }

        // Remove duplicates
        $this->scanReport['missing_routes'] = collect($this->scanReport['missing_routes'])
            ->unique('route')
            ->values()
            ->toArray();
    }

    /**
     * Scan for missing assets
     */
    protected function scanForMissingAssets(): void
    {
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path() . '/', '', $file->getPathname());

            // Find asset() calls
            if (preg_match_all('/asset\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                foreach ($matches[1] as $assetPath) {
                    $fullPath = public_path($assetPath);
                    if (!File::exists($fullPath)) {
                        $this->scanReport['missing_assets'][] = [
                            'file' => $relativePath,
                            'asset' => $assetPath,
                            'type' => 'asset',
                        ];
                    }
                }
            }

            // Find mix() calls
            if (preg_match_all('/mix\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                foreach ($matches[1] as $mixPath) {
                    $manifestPath = public_path('mix-manifest.json');
                    if (File::exists($manifestPath)) {
                        $manifest = json_decode(File::get($manifestPath), true);
                        if (!isset($manifest[$mixPath])) {
                            $this->scanReport['missing_assets'][] = [
                                'file' => $relativePath,
                                'asset' => $mixPath,
                                'type' => 'mix',
                            ];
                        }
                    }
                }
            }

            // Find Vite asset calls
            if (preg_match_all('/@vite\(\[?[\'"]([^\'"]+)[\'"]\]?\)/', $content, $matches)) {
                foreach ($matches[1] as $vitePath) {
                    $fullPath = base_path($vitePath);
                    if (!File::exists($fullPath)) {
                        $this->scanReport['missing_assets'][] = [
                            'file' => $relativePath,
                            'asset' => $vitePath,
                            'type' => 'vite',
                        ];
                    }
                }
            }
        }
    }

    /**
     * Scan for form errors
     */
    protected function scanForFormErrors(): void
    {
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path() . '/', '', $file->getPathname());

            // Check for POST forms without CSRF token
            if (preg_match_all('/<form[^>]+method=["\']post["\'][^>]*>/i', $content, $formMatches)) {
                foreach ($formMatches[0] as $formTag) {
                    // Get context around form tag (next 500 chars)
                    $formStart = strpos($content, $formTag);
                    $formContext = substr($content, $formStart, 500);

                    if (!preg_match('/@csrf|csrf_token\(\)|csrf_field\(\)/', $formContext)) {
                        $this->scanReport['form_errors'][] = [
                            'file' => $relativePath,
                            'error' => 'missing_csrf_token',
                            'form_tag' => substr($formTag, 0, 100) . '...',
                        ];
                    }
                }
            }

            // Check for forms without action
            if (preg_match_all('/<form[^>]*>/i', $content, $formMatches)) {
                foreach ($formMatches[0] as $formTag) {
                    if (!preg_match('/action=["\'][^"\']+["\']/', $formTag)) {
                        $this->scanReport['form_errors'][] = [
                            'file' => $relativePath,
                            'error' => 'missing_action',
                            'form_tag' => substr($formTag, 0, 100) . '...',
                        ];
                    }
                }
            }
        }
    }

    /**
     * Scan for API errors
     */
    protected function scanForApiErrors(): void
    {
        $jsPath = resource_path('js');
        if (!File::exists($jsPath)) {
            $jsPath = public_path('js');
        }

        if (!File::exists($jsPath)) {
            return;
        }

        $jsFiles = File::allFiles($jsPath);

        foreach ($jsFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(base_path() . '/', '', $file->getPathname());

            // Find API calls
            foreach ($this->config['api_patterns'] as $pattern) {
                if (preg_match_all('/' . $pattern . '/i', $content, $matches)) {
                    foreach ($matches[1] ?? $matches[2] ?? [] as $url) {
                        // Check if URL is a route
                        if (str_starts_with($url, '/')) {
                            $routeName = ltrim($url, '/');
                            $this->scanReport['api_errors'][] = [
                                'file' => $relativePath,
                                'url' => $url,
                                'type' => 'api_call',
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * Scan for layout errors
     */
    protected function scanForLayoutErrors(): void
    {
        $viewsPath = resource_path('views');
        $viewFiles = File::allFiles($viewsPath);

        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path() . '/', '', $file->getPathname());

            // Check for @extends without corresponding layout
            if (preg_match_all('/@extends\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                foreach ($matches[1] as $layout) {
                    $layoutPath = resource_path('views/' . str_replace('.', '/', $layout) . '.blade.php');
                    if (!File::exists($layoutPath)) {
                        $this->scanReport['layout_errors'][] = [
                            'file' => $relativePath,
                            'layout' => $layout,
                            'error' => 'missing_layout',
                        ];
                    }
                }
            }

            // Check for @include without corresponding partial
            if (preg_match_all('/@include\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
                foreach ($matches[1] as $partial) {
                    $partialPath = resource_path('views/' . str_replace('.', '/', $partial) . '.blade.php');
                    if (!File::exists($partialPath)) {
                        $this->scanReport['layout_errors'][] = [
                            'file' => $relativePath,
                            'partial' => $partial,
                            'error' => 'missing_partial',
                        ];
                    }
                }
            }
        }
    }

    /**
     * Get scan report
     */
    public function getReport(): array
    {
        return $this->scanReport;
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return count($this->scanReport['errors']) +
               count($this->scanReport['broken_links']) +
               count($this->scanReport['missing_routes']) +
               count($this->scanReport['missing_assets']) +
               count($this->scanReport['form_errors']) +
               count($this->scanReport['api_errors']) +
               count($this->scanReport['layout_errors']);
    }

    /**
     * Has errors?
     */
    public function hasErrors(): bool
    {
        return $this->getErrorCount() > 0;
    }
}
