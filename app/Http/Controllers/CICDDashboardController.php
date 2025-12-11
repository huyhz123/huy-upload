<?php

namespace App\Http\Controllers;

use App\Services\CICD\LiveMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CICDDashboardController extends Controller
{
    protected LiveMonitorService $monitor;

    public function __construct(LiveMonitorService $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        $data = $this->monitor->getDashboardData();
        return view('cicd.dashboard', compact('data'));
    }

    /**
     * Get real-time data (API)
     */
    public function getData()
    {
        $data = $this->monitor->getDashboardData();
        return response()->json($data);
    }

    /**
     * Trigger manual scan
     */
    public function triggerScan()
    {
        $data = $this->monitor->monitor();
        return response()->json([
            'success' => true,
            'message' => 'Scan completed',
            'data' => $data,
        ]);
    }

    /**
     * Get module details
     */
    public function getModuleDetails($moduleName)
    {
        $data = $this->monitor->getDashboardData();
        $module = $data['modules'][$moduleName] ?? null;

        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        return response()->json($module);
    }

    /**
     * Get logs
     */
    public function getLogs()
    {
        $logFile = storage_path('logs/cicd.log');

        if (!file_exists($logFile)) {
            return response()->json(['logs' => []]);
        }

        $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_slice(array_reverse($logs), 0, 100); // Last 100 lines

        return response()->json(['logs' => $logs]);
    }
}
