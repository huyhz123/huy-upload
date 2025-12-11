<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CI/CD Dashboard - 24/7 Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-100">
    <div x-data="dashboard()" x-init="init()" class="min-h-screen">
        <!-- Header -->
        <nav class="bg-gradient-to-r from-purple-600 to-blue-600 text-white shadow-lg">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">🚀 CI/CD Dashboard</h1>
                        <p class="text-sm opacity-90">24/7 Automated Monitoring System</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <span class="opacity-75">Last Update:</span>
                            <span x-text="lastUpdate" class="font-mono"></span>
                        </div>
                        <button @click="refreshData()"
                                class="px-4 py-2 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                            🔄 Refresh
                        </button>
                        <button @click="triggerScan()"
                                class="px-4 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition"
                                :disabled="scanning">
                            <span x-show="!scanning">▶️ Run Scan</span>
                            <span x-show="scanning">⏳ Scanning...</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Statistics Cards -->
        <div class="container mx-auto px-6 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Modules -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">Total Modules</p>
                            <p class="text-3xl font-bold text-gray-800" x-text="stats.total_modules">0</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Layout Merged -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">Layout Merged</p>
                            <p class="text-3xl font-bold" :class="stats.layout_merged_percent === 100 ? 'text-green-600' : 'text-yellow-600'" x-text="stats.layout_merged_percent + '%'">0%</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- In Menu -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">In Menu</p>
                            <p class="text-3xl font-bold" :class="stats.in_menu_percent === 100 ? 'text-green-600' : 'text-orange-600'" x-text="stats.in_menu_percent + '%'">0%</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- UI Issues -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">UI Issues</p>
                            <p class="text-3xl font-bold" :class="stats.ui_issues === 0 ? 'text-green-600' : 'text-red-600'" x-text="stats.ui_issues">0</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules List -->
            <div class="bg-white rounded-lg shadow-lg mb-8">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">📦 Modules Overview</h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Module</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Layout</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">CSS</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">JS</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Menu</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Modified</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="module in modules" :key="module.name">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" x-text="module.name"></div>
                                            <div class="text-xs text-gray-500" x-text="module.path"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                                  :class="module.type === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                                  x-text="module.type"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span x-show="module.layout_merged" class="text-green-600">✓</span>
                                            <span x-show="!module.layout_merged" class="text-red-600">✗</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span x-show="module.css_merged" class="text-green-600">✓</span>
                                            <span x-show="!module.css_merged" class="text-red-600">✗</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span x-show="module.js_merged" class="text-green-600">✓</span>
                                            <span x-show="!module.js_merged" class="text-red-600">✗</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span x-show="module.in_menu" class="text-green-600">✓</span>
                                            <span x-show="!module.in_menu" class="text-yellow-600">✗</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500" x-text="module.modified"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Function Status & UI Status -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Function Status -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-800">⚙️ Function Status</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Forms</span>
                                <span class="text-sm" x-text="functionStatus.forms_working + ' / ' + functionStatus.forms"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">AJAX Calls</span>
                                <span class="text-sm" x-text="functionStatus.ajax_working + ' / ' + functionStatus.ajax_calls"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Tables</span>
                                <span class="text-sm" x-text="functionStatus.tables"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Dropdowns</span>
                                <span class="text-sm" x-text="functionStatus.dropdowns"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- UI Status -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-800">🎨 UI/UX Status</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Total Issues</span>
                                <span class="text-lg font-bold" :class="uiStatus.total_issues === 0 ? 'text-green-600' : 'text-red-600'" x-text="uiStatus.total_issues"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-red-600">Errors</span>
                                <span class="text-sm" x-text="uiStatus.errors"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-yellow-600">Warnings</span>
                                <span class="text-sm" x-text="uiStatus.warnings"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Git Status -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">📝 Git Status</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Branch</p>
                            <p class="text-lg font-semibold" x-text="gitStatus.branch"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="text-lg font-semibold" :class="gitStatus.clean ? 'text-green-600' : 'text-yellow-600'" x-text="gitStatus.clean ? 'Clean' : 'Modified'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Unpushed Commits</p>
                            <p class="text-lg font-semibold" x-text="gitStatus.unpushed_count"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dashboard() {
            return {
                lastUpdate: '',
                scanning: false,
                stats: @json($data['statistics'] ?? []),
                modules: Object.values(@json($data['modules'] ?? [])),
                functionStatus: @json($data['function_status'] ?? []),
                uiStatus: @json($data['ui_status'] ?? []),
                gitStatus: @json($data['git_status'] ?? []),

                init() {
                    this.updateTimestamp();
                    this.startAutoRefresh();
                },

                updateTimestamp() {
                    this.lastUpdate = new Date().toLocaleTimeString();
                },

                async refreshData() {
                    try {
                        const response = await fetch('/cicd/api/data');
                        const data = await response.json();
                        this.updateData(data);
                        this.updateTimestamp();
                    } catch (error) {
                        console.error('Failed to refresh data:', error);
                    }
                },

                async triggerScan() {
                    this.scanning = true;
                    try {
                        const response = await fetch('/cicd/api/scan', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const result = await response.json();
                        await this.refreshData();
                        alert('Scan completed successfully!');
                    } catch (error) {
                        console.error('Scan failed:', error);
                        alert('Scan failed. Check console for details.');
                    } finally {
                        this.scanning = false;
                    }
                },

                updateData(data) {
                    this.stats = data.statistics || {};
                    this.modules = Object.values(data.modules || {});
                    this.functionStatus = data.function_status || {};
                    this.uiStatus = data.ui_status || {};
                    this.gitStatus = data.git_status || {};
                },

                startAutoRefresh() {
                    // Auto-refresh every 30 seconds
                    setInterval(() => {
                        this.refreshData();
                    }, 30000);
                }
            }
        }
    </script>
</body>
</html>
