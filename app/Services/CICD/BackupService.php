<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    protected array $config;
    protected string $backupPath;
    protected array $backupReport = [];

    public function __construct()
    {
        $this->config = config('cicd.backup');
        $this->backupPath = $this->config['path'];

        // Ensure backup directory exists
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Create complete backup (files + database)
     */
    public function createFullBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "backup_{$timestamp}";

        $this->backupReport = [
            'timestamp' => $timestamp,
            'backup_name' => $backupName,
            'files' => [],
            'database' => null,
            'size' => 0,
            'status' => 'in_progress',
        ];

        try {
            // Backup files
            if ($this->config['files']) {
                $this->backupFiles($backupName);
            }

            // Backup database
            if ($this->config['database']) {
                $this->backupDatabase($backupName);
            }

            $this->backupReport['status'] = 'completed';
            $this->cleanOldBackups();

        } catch (\Exception $e) {
            $this->backupReport['status'] = 'failed';
            $this->backupReport['error'] = $e->getMessage();
        }

        return $this->backupReport;
    }

    /**
     * Backup files
     */
    protected function backupFiles(string $backupName): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        foreach ($this->config['directories'] as $type => $directories) {
            foreach ($directories as $directory) {
                $fullPath = base_path($directory);

                if (!File::exists($fullPath)) {
                    continue;
                }

                // For individual files
                if (File::isFile($fullPath)) {
                    $this->backupFile($fullPath, $timestamp);
                    continue;
                }

                // For directories
                $files = File::allFiles($fullPath);

                foreach ($files as $file) {
                    $this->backupFile($file->getPathname(), $timestamp);
                }
            }
        }
    }

    /**
     * Backup single file
     */
    protected function backupFile(string $filePath, string $timestamp): void
    {
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        $backupFilePath = $this->backupPath . '/' . $timestamp . '/' . $relativePath;

        // Create backup with _file1, _file2 suffix
        $pathInfo = pathinfo($backupFilePath);
        $counter = 1;
        $originalBackupPath = $backupFilePath;

        while (File::exists($backupFilePath)) {
            $backupFilePath = $pathInfo['dirname'] . '/' .
                             $pathInfo['filename'] . '_file' . $counter .
                             (isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '');
            $counter++;
        }

        // Create directory if not exists
        $backupDir = dirname($backupFilePath);
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Copy file
        File::copy($filePath, $backupFilePath);

        $this->backupReport['files'][] = [
            'original' => $relativePath,
            'backup' => str_replace($this->backupPath . '/', '', $backupFilePath),
            'size' => File::size($filePath),
        ];

        $this->backupReport['size'] += File::size($filePath);
    }

    /**
     * Backup database
     */
    protected function backupDatabase(string $backupName): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $dbName = env('DB_DATABASE');
        $backupFile = $this->backupPath . '/' . $timestamp . '/database_' . $timestamp . '.sql';

        // Create directory
        $backupDir = dirname($backupFile);
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver === 'mysql') {
                $this->backupMysql($backupFile);
            } elseif ($driver === 'sqlite') {
                $this->backupSqlite($backupFile);
            } else {
                throw new \Exception("Database driver {$driver} not supported for backup");
            }

            $this->backupReport['database'] = [
                'file' => basename($backupFile),
                'size' => File::size($backupFile),
                'tables' => $this->getTableCount(),
            ];

            $this->backupReport['size'] += File::size($backupFile);

        } catch (\Exception $e) {
            $this->backupReport['database'] = [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Backup MySQL database
     */
    protected function backupMysql(string $backupFile): void
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // Fallback: Manual SQL dump
            $this->manualDatabaseDump($backupFile);
        }
    }

    /**
     * Backup SQLite database
     */
    protected function backupSqlite(string $backupFile): void
    {
        $dbPath = config('database.connections.sqlite.database');
        File::copy($dbPath, $backupFile);
    }

    /**
     * Manual database dump (fallback)
     */
    protected function manualDatabaseDump(string $backupFile): void
    {
        $tables = DB::select('SHOW TABLES');
        $sql = "-- Database Backup\n-- Generated: " . now() . "\n\n";

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];

            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, (array)$row);

                $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        File::put($backupFile, $sql);
    }

    /**
     * Get table count
     */
    protected function getTableCount(): int
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver === 'mysql') {
                $tables = DB::select('SHOW TABLES');
                return count($tables);
            } elseif ($driver === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                return count($tables);
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }

    /**
     * Clean old backups
     */
    protected function cleanOldBackups(): void
    {
        $retentionDays = $this->config['retention_days'];
        $cutoffDate = now()->subDays($retentionDays);

        $backupDirs = File::directories($this->backupPath);

        foreach ($backupDirs as $dir) {
            $dirName = basename($dir);

            // Parse timestamp from directory name
            if (preg_match('/\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}/', $dirName, $matches)) {
                $backupDate = \Carbon\Carbon::createFromFormat('Y-m-d_H-i-s', $matches[0]);

                if ($backupDate->lt($cutoffDate)) {
                    File::deleteDirectory($dir);
                }
            }
        }
    }

    /**
     * Restore from backup
     */
    public function restore(string $backupTimestamp): array
    {
        $backupDir = $this->backupPath . '/' . $backupTimestamp;

        if (!File::exists($backupDir)) {
            return [
                'status' => 'failed',
                'error' => 'Backup not found',
            ];
        }

        $report = [
            'timestamp' => $backupTimestamp,
            'restored_files' => [],
            'database_restored' => false,
            'status' => 'in_progress',
        ];

        try {
            // Restore files
            $files = File::allFiles($backupDir);
            foreach ($files as $file) {
                if (str_contains($file->getFilename(), 'database_')) {
                    continue; // Skip database files
                }

                $relativePath = str_replace($backupDir . '/', '', $file->getPathname());
                $targetPath = base_path($relativePath);

                File::copy($file->getPathname(), $targetPath);
                $report['restored_files'][] = $relativePath;
            }

            // Restore database
            $dbBackup = glob($backupDir . '/database_*.sql');
            if (!empty($dbBackup)) {
                $this->restoreDatabase($dbBackup[0]);
                $report['database_restored'] = true;
            }

            $report['status'] = 'completed';

        } catch (\Exception $e) {
            $report['status'] = 'failed';
            $report['error'] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase(string $sqlFile): void
    {
        $sql = File::get($sqlFile);
        DB::unprepared($sql);
    }

    /**
     * Get backup list
     */
    public function getBackupList(): array
    {
        $backups = [];
        $backupDirs = File::directories($this->backupPath);

        foreach ($backupDirs as $dir) {
            $dirName = basename($dir);

            if (preg_match('/\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}/', $dirName, $matches)) {
                $backups[] = [
                    'timestamp' => $matches[0],
                    'path' => $dir,
                    'size' => $this->getDirectorySize($dir),
                    'files_count' => count(File::allFiles($dir)),
                ];
            }
        }

        return $backups;
    }

    /**
     * Get directory size
     */
    protected function getDirectorySize(string $directory): int
    {
        $size = 0;
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Get backup report
     */
    public function getReport(): array
    {
        return $this->backupReport;
    }
}
