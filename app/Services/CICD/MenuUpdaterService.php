<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MenuUpdaterService
{
    protected array $report = [];

    /**
     * Update menu with all modules
     */
    public function updateMenu(array $modules): array
    {
        $this->report = [
            'timestamp' => now()->toDateTimeString(),
            'modules_added' => 0,
            'menu_files_updated' => [],
        ];

        // Find navigation file
        $navFile = resource_path('views/layouts/navigation.blade.php');

        if (!File::exists($navFile)) {
            // Create navigation file if it doesn't exist
            $this->createNavigationFile($navFile, $modules);
            $this->report['menu_files_updated'][] = 'layouts/navigation.blade.php';
            $this->report['modules_added'] = count($modules);
        } else {
            // Update existing navigation
            $this->updateNavigationFile($navFile, $modules);
        }

        return $this->report;
    }

    /**
     * Create new navigation file
     */
    protected function createNavigationFile(string $navFile, array $modules): void
    {
        $content = <<<'BLADE'
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

BLADE;

        // Add module links
        foreach ($modules as $module) {
            $moduleName = $module['name'];
            $routeName = Str::kebab($moduleName);
            $displayName = Str::title(str_replace(['-', '_'], ' ', $moduleName));

            // Determine route prefix
            $prefix = $module['type'] === 'admin' ? 'admin.' : '';
            $fullRoute = $prefix . $routeName . '.index';

            $content .= <<<BLADE

                    <x-nav-link :href="route('{$fullRoute}')" :active="request()->routeIs('{$fullRoute}')">
                        {{ __('{$displayName}') }}
                    </x-nav-link>
BLADE;
        }

        $content .= <<<'BLADE'

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
BLADE;

        // Add responsive module links
        foreach ($modules as $module) {
            $moduleName = $module['name'];
            $routeName = Str::kebab($moduleName);
            $displayName = Str::title(str_replace(['-', '_'], ' ', $moduleName));
            $prefix = $module['type'] === 'admin' ? 'admin.' : '';
            $fullRoute = $prefix . $routeName . '.index';

            $content .= <<<BLADE

            <x-responsive-nav-link :href="route('{$fullRoute}')" :active="request()->routeIs('{$fullRoute}')">
                {{ __('{$displayName}') }}
            </x-responsive-nav-link>
BLADE;
        }

        $content .= <<<'BLADE'

        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
BLADE;

        $navDir = dirname($navFile);
        if (!File::exists($navDir)) {
            File::makeDirectory($navDir, 0755, true);
        }

        File::put($navFile, $content);
    }

    /**
     * Update existing navigation file
     */
    protected function updateNavigationFile(string $navFile, array $modules): void
    {
        $content = File::get($navFile);
        $modulesInMenu = 0;

        foreach ($modules as $module) {
            $moduleName = $module['name'];
            $routeName = Str::kebab($moduleName);
            $prefix = $module['type'] === 'admin' ? 'admin.' : '';
            $fullRoute = $prefix . $routeName . '.index';

            // Check if module is already in menu
            if (str_contains($content, $fullRoute)) {
                continue;
            }

            // Add module to menu (after Dashboard link)
            $displayName = Str::title(str_replace(['-', '_'], ' ', $moduleName));
            $newLink = <<<BLADE

                    <x-nav-link :href="route('{$fullRoute}')" :active="request()->routeIs('{$fullRoute}')">
                        {{ __('{$displayName}') }}
                    </x-nav-link>
BLADE;

            // Insert after Dashboard link in desktop menu
            $content = preg_replace(
                '/(Dashboard.*?<\/x-nav-link>)/s',
                "$1{$newLink}",
                $content,
                1
            );

            // Insert after Dashboard link in mobile menu
            $responsiveLink = <<<BLADE

            <x-responsive-nav-link :href="route('{$fullRoute}')" :active="request()->routeIs('{$fullRoute}')">
                {{ __('{$displayName}') }}
            </x-responsive-nav-link>
BLADE;

            $content = preg_replace(
                '/(Dashboard.*?<\/x-responsive-nav-link>)/s',
                "$1{$responsiveLink}",
                $content,
                1
            );

            $modulesInMenu++;
        }

        if ($modulesInMenu > 0) {
            File::put($navFile, $content);
            $this->report['menu_files_updated'][] = 'layouts/navigation.blade.php';
            $this->report['modules_added'] = $modulesInMenu;
        }
    }

    /**
     * Get report
     */
    public function getReport(): array
    {
        return $this->report;
    }
}
