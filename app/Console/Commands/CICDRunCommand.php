<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CICD\BackupService;
use App\Services\CICD\ScannerService;
use App\Services\CICD\AutoFixService;
use App\Services\CICD\GitService;
use App\Services\CICD\ReportService;
use App\Services\CICD\LiveMonitorService;
use App\Services\CICD\LayoutMergerService;
use App\Services\CICD\MenuUpdaterService;

class CICDRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cicd:run
                            {--skip-backup : Skip backup step}
                            {--skip-git : Skip git commit and push}
                            {--watch : Run in watch mode (continuous monitoring)}
                            {--interval=60 : Watch interval in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run complete CI/CD automation pipeline';

    protected array $report = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🚀 CI/CD AUTOMATION SYSTEM');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if ($this->option('watch')) {
            $this->runWatchMode();
        } else {
            $this->runOnce();
        }

        return 0;
    }

    /**
     * Run once
     */
    protected function runOnce(): void
    {
        $startTime = microtime(true);

        // Step 0: Live Monitor
        $this->step0LiveMonitor();

        // Step 1: Backup
        if (!$this->option('skip-backup')) {
            $this->step1Backup();
        } else {
            $this->warn('⏭️  Skipping backup step');
        }

        // Step 2: Scan
        $this->step2Scan();

        // Step 3: Auto-Fix
        $this->step3AutoFix();

        // Step 3.5: Layout & CSS/JS Merger
        $this->step3HalfLayoutMerger();

        // Step 3.6: Menu Updater
        $this->step3SixMenuUpdater();

        // Step 4: Git Commit & Push
        if (!$this->option('skip-git')) {
            $this->step4Git();
        } else {
            $this->warn('⏭️  Skipping git step');
        }

        // Step 5: Generate Report
        $this->step5Report();

        // Summary
        $duration = round(microtime(true) - $startTime, 2);
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("✅ CI/CD pipeline completed in {$duration}s");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    /**
     * Run in watch mode
     */
    protected function runWatchMode(): void
    {
        $this->info('👀 Starting watch mode...');
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        $interval = (int) $this->option('interval');

        while (true) {
            $this->info('[' . now()->format('H:i:s') . '] Running CI/CD pipeline...');
            $this->runOnce();

            $this->info("Waiting {$interval} seconds...");
            $this->newLine();

            sleep($interval);
        }
    }

    /**
     * Step 1: Backup
     */
    protected function step1Backup(): void
    {
        $this->info('📦 Step 1: Creating Backup...');

        $backup = new BackupService();
        $result = $backup->createFullBackup();

        $this->report['backup'] = $result;

        if ($result['status'] === 'completed') {
            $filesCount = count($result['files']);
            $size = $this->formatBytes($result['size']);
            $this->success("✓ Backup completed: {$filesCount} files, {$size}");
        } else {
            $this->error('✗ Backup failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $this->newLine();
    }

    /**
     * Step 2: Scan
     */
    protected function step2Scan(): void
    {
        $this->info('🔍 Step 2: Scanning for errors and changes...');

        $scanner = new ScannerService();
        $result = $scanner->scan();

        $this->report['scan'] = $result;

        $errorCount = $scanner->getErrorCount();
        $changesCount = count($result['changes']);
        $missingModules = count($result['missing_modules']);

        $this->info("   • File changes: {$changesCount}");
        $this->info("   • Errors found: {$errorCount}");
        $this->info("   • Missing modules: {$missingModules}");

        if ($errorCount > 0) {
            $this->warn("⚠  {$errorCount} errors detected");
        } else {
            $this->success('✓ No errors found');
        }

        $this->newLine();
    }

    /**
     * Step 3: Auto-Fix
     */
    protected function step3AutoFix(): void
    {
        $this->info('🔧 Step 3: Auto-fixing errors...');

        $autoFix = new AutoFixService();
        $result = $autoFix->fix($this->report['scan']);

        $this->report['autofix'] = $result;

        $fixedCount = count($result['fixed_errors']);
        $stubsCount = count($result['created_stubs']);
        $modifiedCount = count(array_unique($result['modified_files']));

        $this->info("   • Errors fixed: {$fixedCount}");
        $this->info("   • Stubs created: {$stubsCount}");
        $this->info("   • Files modified: {$modifiedCount}");

        if ($result['status'] === 'completed') {
            $this->success('✓ Auto-fix completed');
        } else {
            $this->error('✗ Auto-fix failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $this->newLine();
    }

    /**
     * Step 4: Git
     */
    protected function step4Git(): void
    {
        $this->info('📝 Step 4: Committing and pushing to Git...');

        $modifiedFiles = array_unique($this->report['autofix']['modified_files'] ?? []);

        if (empty($modifiedFiles)) {
            $this->warn('⏭️  No files to commit');
            $this->newLine();
            return;
        }

        $git = new GitService();
        $result = $git->commitAndPush($modifiedFiles);

        $this->report['git'] = $result;

        if ($result['status'] === 'success') {
            $this->success('✓ Git operations completed');
            if ($result['committed']) {
                $hash = substr($result['commit_hash'] ?? '', 0, 8);
                $this->info("   • Commit: {$hash}");
            }
            if ($result['pushed']) {
                $this->info("   • Pushed to: {$result['branch']}");
            }
        } else {
            $this->error('✗ Git operations failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $this->newLine();
    }

    /**
     * Step 0: Live Monitor
     */
    protected function step0LiveMonitor(): void
    {
        $this->info('👁️  Step 0: Running Live Monitor...');

        $monitor = new LiveMonitorService();
        $result = $monitor->monitor();

        $this->report['monitor'] = $result;

        $moduleCount = count($result['modules']);
        $this->success("✓ Detected {$moduleCount} modules");
        $this->newLine();
    }

    /**
     * Step 3.5: Layout Merger
     */
    protected function step3HalfLayoutMerger(): void
    {
        $this->info('🎨 Step 3.5: Merging layout, CSS, and JS...');

        $modules = $this->report['monitor']['modules'] ?? [];
        $merger = new LayoutMergerService();
        $result = $merger->merge($modules);

        $this->report['layout_merger'] = $result;

        $this->info("   • Layout merged: {$result['layout_merged']}");
        $this->info("   • CSS merged: {$result['css_merged']}");
        $this->info("   • JS merged: {$result['js_merged']}");
        $this->success('✓ Layout merge completed');
        $this->newLine();
    }

    /**
     * Step 3.6: Menu Updater
     */
    protected function step3SixMenuUpdater(): void
    {
        $this->info('📋 Step 3.6: Updating menu/navigation...');

        $modules = $this->report['monitor']['modules'] ?? [];
        $updater = new MenuUpdaterService();
        $result = $updater->updateMenu($modules);

        $this->report['menu_updater'] = $result;

        $this->info("   • Modules added to menu: {$result['modules_added']}");
        $this->success('✓ Menu update completed');
        $this->newLine();
    }

    /**
     * Step 5: Generate Report
     */
    protected function step5Report(): void
    {
        $this->info('📊 Step 5: Generating report...');

        $reporter = new ReportService();
        $reportFile = $reporter->generate($this->report);

        $this->success('✓ Report generated: ' . $reportFile);
        $this->newLine();
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
     * Success message
     */
    protected function success(string $message): void
    {
        $this->line("<fg=green>{$message}</>");
    }
}
