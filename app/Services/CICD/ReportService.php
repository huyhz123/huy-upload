<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\File;

class ReportService
{
    protected array $config;
    protected string $reportPath;

    public function __construct()
    {
        $this->config = config('cicd.reporting');
        $this->reportPath = $this->config['report_path'];

        if (!File::exists($this->reportPath)) {
            File::makeDirectory($this->reportPath, 0755, true);
        }
    }

    /**
     * Generate comprehensive report
     */
    public function generate(array $data): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $reportFile = $this->reportPath . '/cicd_report_' . $timestamp . '.html';

        $html = $this->generateHtml($data, $timestamp);
        File::put($reportFile, $html);

        // Also log to file
        $this->log($data);

        return $reportFile;
    }

    /**
     * Generate HTML report
     */
    protected function generateHtml(array $data, string $timestamp): string
    {
        $backup = $data['backup'] ?? [];
        $scan = $data['scan'] ?? [];
        $autofix = $data['autofix'] ?? [];
        $git = $data['git'] ?? [];

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>CI/CD Report - {$timestamp}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #666; margin-top: 30px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #4CAF50; color: white; }
        .badge-error { background: #f44336; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
        .stat-box { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .stat-label { font-size: 14px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 CI/CD Automation Report</h1>
        <p><strong>Generated:</strong> {$timestamp}</p>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-number">{$this->countErrors($scan)}</div>
                <div class="stat-label">Errors Detected</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{$this->countFixed($autofix)}</div>
                <div class="stat-label">Errors Fixed</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{$this->countStubs($autofix)}</div>
                <div class="stat-label">Stubs Created</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{$this->countFiles($backup)}</div>
                <div class="stat-label">Files Backed Up</div>
            </div>
        </div>

        <!-- Backup Section -->
        <div class="section">
            <h2>📦 Backup Report</h2>
            {$this->renderBackupSection($backup)}
        </div>

        <!-- Scan Section -->
        <div class="section">
            <h2>🔍 Scan Report</h2>
            {$this->renderScanSection($scan)}
        </div>

        <!-- AutoFix Section -->
        <div class="section">
            <h2>🔧 Auto-Fix Report</h2>
            {$this->renderAutoFixSection($autofix)}
        </div>

        <!-- Git Section -->
        <div class="section">
            <h2>📝 Git Report</h2>
            {$this->renderGitSection($git)}
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render backup section
     */
    protected function renderBackupSection(array $backup): string
    {
        if (empty($backup)) {
            return '<p class="warning">No backup performed</p>';
        }

        $status = $backup['status'] ?? 'unknown';
        $filesCount = count($backup['files'] ?? []);
        $size = $this->formatBytes($backup['size'] ?? 0);

        $html = "<p><strong>Status:</strong> <span class='badge badge-{$status}'>{$status}</span></p>";
        $html .= "<p><strong>Files Backed Up:</strong> {$filesCount}</p>";
        $html .= "<p><strong>Total Size:</strong> {$size}</p>";

        if (!empty($backup['database'])) {
            $dbSize = $this->formatBytes($backup['database']['size'] ?? 0);
            $html .= "<p><strong>Database Backup:</strong> {$dbSize}</p>";
        }

        return $html;
    }

    /**
     * Render scan section
     */
    protected function renderScanSection(array $scan): string
    {
        if (empty($scan)) {
            return '<p class="warning">No scan performed</p>';
        }

        $html = '';

        // Errors
        if (!empty($scan['errors'])) {
            $html .= '<h3>Errors Found</h3>';
            $html .= '<table><tr><th>Type</th><th>File</th><th>Count</th></tr>';
            foreach ($scan['errors'] as $error) {
                $html .= "<tr><td>{$error['type']}</td><td>{$error['file']}</td><td>{$error['matches']}</td></tr>";
            }
            $html .= '</table>';
        }

        // Missing Modules
        if (!empty($scan['missing_modules'])) {
            $html .= '<h3>Missing Modules</h3>';
            $html .= '<table><tr><th>Module</th><th>Route</th><th>Controller</th><th>View</th></tr>';
            foreach ($scan['missing_modules'] as $module) {
                $html .= "<tr>";
                $html .= "<td>{$module['title']}</td>";
                $html .= "<td>" . ($module['route_exists'] ? '✓' : '✗') . "</td>";
                $html .= "<td>" . ($module['controller_exists'] ? '✓' : '✗') . "</td>";
                $html .= "<td>" . ($module['view_exists'] ? '✓' : '✗') . "</td>";
                $html .= "</tr>";
            }
            $html .= '</table>';
        }

        return $html ?: '<p class="success">No errors found!</p>';
    }

    /**
     * Render auto-fix section
     */
    protected function renderAutoFixSection(array $autofix): string
    {
        if (empty($autofix)) {
            return '<p class="warning">No auto-fix performed</p>';
        }

        $html = '';

        // Fixed Errors
        if (!empty($autofix['fixed_errors'])) {
            $html .= '<h3>Fixed Errors (' . count($autofix['fixed_errors']) . ')</h3>';
            $html .= '<table><tr><th>Type</th><th>File</th><th>Action</th></tr>';
            foreach ($autofix['fixed_errors'] as $fix) {
                $html .= "<tr><td>{$fix['type']}</td><td>{$fix['file']}</td><td>{$fix['action']}</td></tr>";
            }
            $html .= '</table>';
        }

        // Created Stubs
        if (!empty($autofix['created_stubs'])) {
            $html .= '<h3>Created Stubs (' . count($autofix['created_stubs']) . ')</h3>';
            $html .= '<table><tr><th>Type</th><th>Path/Name</th></tr>';
            foreach ($autofix['created_stubs'] as $stub) {
                $path = $stub['path'] ?? $stub['name'] ?? 'N/A';
                $html .= "<tr><td>{$stub['type']}</td><td>{$path}</td></tr>";
            }
            $html .= '</table>';
        }

        return $html ?: '<p class="success">No fixes needed!</p>';
    }

    /**
     * Render git section
     */
    protected function renderGitSection(array $git): string
    {
        if (empty($git)) {
            return '<p class="warning">No git operations performed</p>';
        }

        $html = "<p><strong>Status:</strong> <span class='badge badge-{$git['status']}'>{$git['status']}</span></p>";
        $html .= "<p><strong>Committed:</strong> " . ($git['committed'] ? '✓ Yes' : '✗ No') . "</p>";
        $html .= "<p><strong>Pushed:</strong> " . ($git['pushed'] ? '✓ Yes' : '✗ No') . "</p>";

        if (!empty($git['commit_hash'])) {
            $hash = substr($git['commit_hash'], 0, 8);
            $html .= "<p><strong>Commit:</strong> {$hash}</p>";
        }

        return $html;
    }

    /**
     * Count errors
     */
    protected function countErrors(array $scan): int
    {
        return count($scan['errors'] ?? []) +
               count($scan['broken_links'] ?? []) +
               count($scan['missing_routes'] ?? []) +
               count($scan['missing_assets'] ?? []) +
               count($scan['form_errors'] ?? []);
    }

    /**
     * Count fixed
     */
    protected function countFixed(array $autofix): int
    {
        return count($autofix['fixed_errors'] ?? []);
    }

    /**
     * Count stubs
     */
    protected function countStubs(array $autofix): int
    {
        return count($autofix['created_stubs'] ?? []);
    }

    /**
     * Count files
     */
    protected function countFiles(array $backup): int
    {
        return count($backup['files'] ?? []);
    }

    /**
     * Format bytes
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * Log to file
     */
    protected function log(array $data): void
    {
        $logFile = $this->config['log_path'];
        $logDir = dirname($logFile);

        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0755, true);
        }

        $logEntry = sprintf(
            "[%s] CI/CD Run - Errors: %d, Fixed: %d, Stubs: %d, Backup: %d files\n",
            now()->toDateTimeString(),
            $this->countErrors($data['scan'] ?? []),
            $this->countFixed($data['autofix'] ?? []),
            $this->countStubs($data['autofix'] ?? []),
            $this->countFiles($data['backup'] ?? [])
        );

        File::append($logFile, $logEntry);
    }
}
