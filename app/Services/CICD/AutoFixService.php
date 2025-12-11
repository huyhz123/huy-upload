<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AutoFixService
{
    protected array $config;
    protected array $fixReport = [];
    protected array $scanReport = [];

    public function __construct()
    {
        $this->config = config('cicd.autofix');
    }

    /**
     * Auto-fix all detected errors
     */
    public function fix(array $scanReport): array
    {
        $this->scanReport = $scanReport;
        $this->fixReport = [
            'timestamp' => now()->toDateTimeString(),
            'fixed_errors' => [],
            'created_stubs' => [],
            'modified_files' => [],
            'status' => 'in_progress',
        ];

        try {
            // Fix broken links
            if ($this->config['fix_broken_links']) {
                $this->fixBrokenLinks();
            }

            // Fix missing CSRF tokens
            if ($this->config['fix_missing_csrf']) {
                $this->fixMissingCsrf();
            }

            // Fix asset paths
            if ($this->config['fix_asset_paths']) {
                $this->fixAssetPaths();
            }

            // Create missing routes
            if ($this->config['create_missing_routes']) {
                $this->createMissingRoutes();
            }

            // Create missing controllers
            if ($this->config['create_missing_controllers']) {
                $this->createMissingControllers();
            }

            // Create missing views
            if ($this->config['create_missing_views']) {
                $this->createMissingViews();
            }

            $this->fixReport['status'] = 'completed';

        } catch (\Exception $e) {
            $this->fixReport['status'] = 'failed';
            $this->fixReport['error'] = $e->getMessage();
        }

        return $this->fixReport;
    }

    /**
     * Fix broken links
     */
    protected function fixBrokenLinks(): void
    {
        if (empty($this->scanReport['broken_links'])) {
            return;
        }

        foreach ($this->scanReport['broken_links'] as $brokenLink) {
            if ($brokenLink['type'] === 'empty_link') {
                $filePath = resource_path($brokenLink['file']);
                $content = File::get($filePath);

                // Replace href="#" with href="javascript:void(0)"
                $newContent = preg_replace('/href=["\']#["\']/', 'href="javascript:void(0)"', $content);

                // Replace empty href
                $newContent = preg_replace('/href=["\']{2}/', 'href="javascript:void(0)"', $newContent);

                if ($content !== $newContent) {
                    File::put($filePath, $newContent);

                    $this->fixReport['fixed_errors'][] = [
                        'type' => 'broken_link',
                        'file' => $brokenLink['file'],
                        'action' => 'replaced_empty_href',
                    ];

                    $this->fixReport['modified_files'][] = $brokenLink['file'];
                }
            }
        }
    }

    /**
     * Fix missing CSRF tokens
     */
    protected function fixMissingCsrf(): void
    {
        if (empty($this->scanReport['form_errors'])) {
            return;
        }

        foreach ($this->scanReport['form_errors'] as $formError) {
            if ($formError['error'] !== 'missing_csrf_token') {
                continue;
            }

            $filePath = resource_path($formError['file']);
            $content = File::get($filePath);

            // Find POST forms and add @csrf after opening form tag
            $newContent = preg_replace_callback(
                '/<form([^>]+method=["\']post["\'][^>]*)>/i',
                function($matches) use ($content) {
                    $formTag = $matches[0];
                    $formStart = strpos($content, $formTag);
                    $formContext = substr($content, $formStart, 500);

                    // Check if @csrf already exists in context
                    if (preg_match('/@csrf|csrf_token\(\)|csrf_field\(\)/', $formContext)) {
                        return $formTag;
                    }

                    // Add @csrf after form tag
                    return $formTag . "\n    @csrf";
                },
                $content
            );

            if ($content !== $newContent) {
                File::put($filePath, $newContent);

                $this->fixReport['fixed_errors'][] = [
                    'type' => 'missing_csrf',
                    'file' => $formError['file'],
                    'action' => 'added_csrf_token',
                ];

                $this->fixReport['modified_files'][] = $formError['file'];
            }
        }
    }

    /**
     * Fix asset paths
     */
    protected function fixAssetPaths(): void
    {
        if (empty($this->scanReport['missing_assets'])) {
            return;
        }

        foreach ($this->scanReport['missing_assets'] as $missingAsset) {
            $filePath = resource_path($missingAsset['file']);

            if (!File::exists($filePath)) {
                continue;
            }

            $content = File::get($filePath);
            $assetPath = $missingAsset['asset'];

            // Try to fix common asset path issues
            $newContent = $content;

            // Fix relative paths (change /css/style.css to {{ asset('css/style.css') }})
            if (preg_match('/^\/[^\/]/', $assetPath)) {
                $correctedPath = ltrim($assetPath, '/');
                $newContent = str_replace(
                    "asset('{$assetPath}')",
                    "asset('{$correctedPath}')",
                    $newContent
                );
            }

            // Create placeholder asset if it doesn't exist
            $publicPath = public_path($assetPath);
            $publicDir = dirname($publicPath);

            if (!File::exists($publicDir)) {
                File::makeDirectory($publicDir, 0755, true);
            }

            if (!File::exists($publicPath)) {
                $extension = pathinfo($publicPath, PATHINFO_EXTENSION);

                if ($extension === 'css') {
                    File::put($publicPath, "/* Auto-generated placeholder CSS */\n");
                } elseif ($extension === 'js') {
                    File::put($publicPath, "// Auto-generated placeholder JS\n");
                } else {
                    File::put($publicPath, "");
                }

                $this->fixReport['created_stubs'][] = [
                    'type' => 'asset',
                    'path' => $assetPath,
                ];
            }

            if ($content !== $newContent) {
                File::put($filePath, $newContent);
                $this->fixReport['modified_files'][] = $missingAsset['file'];
            }
        }
    }

    /**
     * Create missing routes
     */
    protected function createMissingRoutes(): void
    {
        if (empty($this->scanReport['missing_routes'])) {
            return;
        }

        $routesFile = base_path('routes/web.php');
        $routesContent = File::get($routesFile);

        $newRoutes = [];

        foreach ($this->scanReport['missing_routes'] as $missingRoute) {
            $routeName = $missingRoute['route'];

            // Parse route name to determine controller and method
            $parts = explode('.', $routeName);
            $resource = $parts[0] ?? 'home';
            $action = $parts[1] ?? 'index';

            // Determine controller name
            $controllerName = Str::studly($resource) . 'Controller';
            $controllerClass = "App\\Http\\Controllers\\Admin\\{$controllerName}";

            // Generate route
            $method = $this->getHttpMethodForAction($action);
            $uri = $this->getUriForRoute($routeName);

            $newRoutes[] = [
                'name' => $routeName,
                'uri' => $uri,
                'method' => $method,
                'controller' => $controllerClass,
                'action' => $action,
            ];
        }

        // Add new routes to routes file
        if (!empty($newRoutes)) {
            $routesAddition = "\n// Auto-generated routes by CI/CD\n";

            foreach ($newRoutes as $route) {
                $routesAddition .= sprintf(
                    "Route::%s('%s', [%s::class, '%s'])->name('%s');\n",
                    strtolower($route['method']),
                    $route['uri'],
                    $route['controller'],
                    $route['action'],
                    $route['name']
                );

                $this->fixReport['created_stubs'][] = [
                    'type' => 'route',
                    'name' => $route['name'],
                    'uri' => $route['uri'],
                ];
            }

            // Append to routes file (before the closing PHP tag if exists)
            if (str_contains($routesContent, '?>')) {
                $routesContent = str_replace('?>', $routesAddition . "\n?>", $routesContent);
            } else {
                $routesContent .= $routesAddition;
            }

            File::put($routesFile, $routesContent);
            $this->fixReport['modified_files'][] = 'routes/web.php';
        }
    }

    /**
     * Create missing controllers
     */
    protected function createMissingControllers(): void
    {
        if (empty($this->scanReport['missing_modules'])) {
            return;
        }

        foreach ($this->scanReport['missing_modules'] as $module) {
            if ($module['controller_exists']) {
                continue;
            }

            $controllerName = $module['title'];
            $controllerClass = str_replace(' ', '', $controllerName) . 'Controller';
            $controllerPath = app_path('Http/Controllers/Admin/' . $controllerClass . '.php');

            if (!File::exists($controllerPath)) {
                $stub = $this->getControllerStub($controllerClass, $module);
                File::put($controllerPath, $stub);

                $this->fixReport['created_stubs'][] = [
                    'type' => 'controller',
                    'name' => $controllerClass,
                    'path' => 'app/Http/Controllers/Admin/' . $controllerClass . '.php',
                ];
            }
        }
    }

    /**
     * Create missing views
     */
    protected function createMissingViews(): void
    {
        if (empty($this->scanReport['missing_modules'])) {
            return;
        }

        foreach ($this->scanReport['missing_modules'] as $module) {
            if ($module['view_exists']) {
                continue;
            }

            $routeName = $module['route'] ?? Str::kebab($module['title']);
            $viewDir = resource_path('views/admin/' . $routeName);

            if (!File::exists($viewDir)) {
                File::makeDirectory($viewDir, 0755, true);
            }

            // Create index view
            $indexView = $viewDir . '/index.blade.php';
            if (!File::exists($indexView)) {
                $stub = $this->getViewStub($module, 'index');
                File::put($indexView, $stub);

                $this->fixReport['created_stubs'][] = [
                    'type' => 'view',
                    'name' => $routeName . '.index',
                    'path' => 'resources/views/admin/' . $routeName . '/index.blade.php',
                ];
            }

            // Create other CRUD views
            foreach (['create', 'edit', 'show'] as $viewType) {
                $viewPath = $viewDir . '/' . $viewType . '.blade.php';
                if (!File::exists($viewPath)) {
                    $stub = $this->getViewStub($module, $viewType);
                    File::put($viewPath, $stub);

                    $this->fixReport['created_stubs'][] = [
                        'type' => 'view',
                        'name' => $routeName . '.' . $viewType,
                        'path' => 'resources/views/admin/' . $routeName . '/' . $viewType . '.blade.php',
                    ];
                }
            }
        }
    }

    /**
     * Get HTTP method for action
     */
    protected function getHttpMethodForAction(string $action): string
    {
        $methodMap = [
            'index' => 'get',
            'create' => 'get',
            'store' => 'post',
            'show' => 'get',
            'edit' => 'get',
            'update' => 'put',
            'destroy' => 'delete',
        ];

        return $methodMap[$action] ?? 'get';
    }

    /**
     * Get URI for route
     */
    protected function getUriForRoute(string $routeName): string
    {
        $parts = explode('.', $routeName);
        return '/' . implode('/', $parts);
    }

    /**
     * Get controller stub
     */
    protected function getControllerStub(string $className, array $module): string
    {
        $modelName = $module['model'] ?? 'Item';

        return <<<PHP
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class {$className} extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implement index logic
        return view('admin.{$module['route']}.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: Implement create logic
        return view('admin.{$module['route']}.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request)
    {
        // TODO: Implement store logic
        return redirect()->route('admin.{$module['route']}.index')
            ->with('success', '{$module['title']} created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string \$id)
    {
        // TODO: Implement show logic
        return view('admin.{$module['route']}.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string \$id)
    {
        // TODO: Implement edit logic
        return view('admin.{$module['route']}.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, string \$id)
    {
        // TODO: Implement update logic
        return redirect()->route('admin.{$module['route']}.index')
            ->with('success', '{$module['title']} updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string \$id)
    {
        // TODO: Implement destroy logic
        return redirect()->route('admin.{$module['route']}.index')
            ->with('success', '{$module['title']} deleted successfully');
    }
}

PHP;
    }

    /**
     * Get view stub
     */
    protected function getViewStub(array $module, string $type): string
    {
        $title = $module['title'];

        $stubs = [
            'index' => <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{$title}</h1>
        <a href="{{ route('admin.{$module['route']}.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New
        </a>
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <!-- TODO: Implement list view -->
        <p class="text-gray-500">No items found. This is an auto-generated stub view.</p>
    </div>
</div>
@endsection
BLADE,

            'create' => <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Create {$title}</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <form action="{{ route('admin.{$module['route']}.store') }}" method="POST">
            @csrf

            <!-- TODO: Add form fields -->

            <div class="flex items-center justify-between mt-6">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">
                    Create
                </button>
                <a href="{{ route('admin.{$module['route']}.index') }}" class="text-blue-500 hover:text-blue-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE,

            'edit' => <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Edit {$title}</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <form action="{{ route('admin.{$module['route']}.update', \$id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- TODO: Add form fields -->

            <div class="flex items-center justify-between mt-6">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">
                    Update
                </button>
                <a href="{{ route('admin.{$module['route']}.index') }}" class="text-blue-500 hover:text-blue-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE,

            'show' => <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{$title} Details</h1>
        <div>
            <a href="{{ route('admin.{$module['route']}.edit', \$id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                Edit
            </a>
            <a href="{{ route('admin.{$module['route']}.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <!-- TODO: Display item details -->
        <p class="text-gray-500">Item details will be displayed here.</p>
    </div>
</div>
@endsection
BLADE,
        ];

        return $stubs[$type] ?? '';
    }

    /**
     * Get fix report
     */
    public function getReport(): array
    {
        return $this->fixReport;
    }
}

PHP;
    }
}
