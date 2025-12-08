<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Exception;

class InstallerController extends Controller
{
    public function index()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect('/')->with('error', 'Application is already installed!');
        }
        
        return view('installer.welcome');
    }

    public function requirements()
    {
        $requirements = [
            'PHP Version >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'Ctype Extension' => extension_loaded('ctype'),
            'JSON Extension' => extension_loaded('json'),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
            'GD Extension' => extension_loaded('gd'),
        ];

        $permissions = [
            'storage/' => is_writable(storage_path()),
            'bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
            '.env file' => is_writable(base_path('.env')) || !file_exists(base_path('.env')),
        ];

        return view('installer.requirements', compact('requirements', 'permissions'));
    }

    public function database()
    {
        return view('installer.database');
    }

    public function databasePost(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_name' => 'required',
            'db_username' => 'required',
        ]);

        try {
            // Test database connection
            $connection = @new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}",
                $request->db_username,
                $request->db_password
            );

            // Update .env file
            $this->updateEnv([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_name,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password,
            ]);

            return redirect()->route('installer.admin');
        } catch (\PDOException $e) {
            return back()->withErrors(['error' => 'Database connection failed: ' . $e->getMessage()]);
        }
    }

    public function admin()
    {
        return view('installer.admin');
    }

    public function adminPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        session([
            'admin_name' => $request->name,
            'admin_email' => $request->email,
            'admin_password' => $request->password,
        ]);

        return redirect()->route('installer.install');
    }

    public function install()
    {
        return view('installer.install');
    }

    public function process(Request $request)
    {
        try {
            $step = $request->input('step', 1);

            switch ($step) {
                case 1:
                    // Clear caches
                    Artisan::call('config:clear');
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');
                    return response()->json(['success' => true, 'message' => 'Caches cleared']);

                case 2:
                    // Run migrations
                    Artisan::call('migrate:fresh', ['--force' => true]);
                    return response()->json(['success' => true, 'message' => 'Database migrated']);

                case 3:
                    // Seed roles and permissions
                    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder', '--force' => true]);
                    return response()->json(['success' => true, 'message' => 'Roles & permissions created']);

                case 4:
                    // Create admin user
                    $this->createAdminUser();
                    return response()->json(['success' => true, 'message' => 'Admin user created']);

                case 5:
                    // Seed demo data
                    Artisan::call('db:seed', ['--class' => 'UserSeeder', '--force' => true]);
                    return response()->json(['success' => true, 'message' => 'Demo users created']);

                case 6:
                    // Create storage link
                    Artisan::call('storage:link');
                    return response()->json(['success' => true, 'message' => 'Storage linked']);

                case 7:
                    // Optimize application
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    return response()->json(['success' => true, 'message' => 'Application optimized']);

                case 8:
                    // Mark as installed
                    File::put(storage_path('installed'), date('Y-m-d H:i:s'));
                    return response()->json(['success' => true, 'message' => 'Installation completed', 'redirect' => route('installer.complete')]);

                default:
                    return response()->json(['success' => false, 'message' => 'Invalid step']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function complete()
    {
        $adminEmail = session('admin_email', 'admin@repair.com');
        return view('installer.complete', compact('adminEmail'));
    }

    private function createAdminUser()
    {
        $name = session('admin_name', 'Admin User');
        $email = session('admin_email', 'admin@repair.com');
        $password = session('admin_password', 'admin123');

        $admin = \App\Models\User::create([
            'name' => $name,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'user_type' => 'admin',
            'is_active' => true,
            'language' => 'vi',
        ]);

        $admin->assignRole('admin');
    }

    private function updateEnv($data)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    private function isInstalled()
    {
        return File::exists(storage_path('installed'));
    }
}
