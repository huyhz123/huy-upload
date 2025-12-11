<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\Process;

class GitService
{
    protected array $config;
    protected array $report = [];

    public function __construct()
    {
        $this->config = config('cicd.git');
    }

    /**
     * Auto commit and push changes
     */
    public function commitAndPush(array $modifiedFiles, string $message = null): array
    {
        $this->report = [
            'timestamp' => now()->toDateTimeString(),
            'committed' => false,
            'pushed' => false,
            'files_count' => count($modifiedFiles),
            'commit_hash' => null,
            'branch' => $this->config['branch'],
        ];

        if (!$this->config['enabled']) {
            $this->report['status'] = 'disabled';
            return $this->report;
        }

        try {
            // Add files
            foreach ($modifiedFiles as $file) {
                $this->exec("git add " . escapeshellarg($file));
            }

            // Commit
            $commitMessage = $message ?? $this->generateCommitMessage($modifiedFiles);
            $fullMessage = $this->config['commit_message_prefix'] . ' ' . $commitMessage;

            $this->exec("git commit -m " . escapeshellarg($fullMessage));
            $this->report['committed'] = true;

            // Get commit hash
            $this->report['commit_hash'] = trim($this->exec("git rev-parse HEAD"));

            // Push if enabled
            if ($this->config['auto_push']) {
                $remote = $this->config['remote'];
                $branch = $this->config['branch'];

                $this->exec("git push {$remote} {$branch}", 300);
                $this->report['pushed'] = true;
            }

            $this->report['status'] = 'success';

        } catch (\Exception $e) {
            $this->report['status'] = 'failed';
            $this->report['error'] = $e->getMessage();
        }

        return $this->report;
    }

    /**
     * Generate commit message
     */
    protected function generateCommitMessage(array $files): string
    {
        $types = [];

        foreach ($files as $file) {
            if (str_contains($file, 'views')) $types['views'] = true;
            if (str_contains($file, 'Controllers')) $types['controllers'] = true;
            if (str_contains($file, 'routes')) $types['routes'] = true;
            if (str_contains($file, 'Models')) $types['models'] = true;
            if (str_contains($file, '.css') || str_contains($file, '.js')) $types['assets'] = true;
        }

        $message = 'Updated ' . implode(', ', array_keys($types));
        $message .= ' (' . count($files) . ' files)';

        return $message;
    }

    /**
     * Execute git command
     */
    protected function exec(string $command, int $timeout = 60): string
    {
        $result = Process::timeout($timeout)->run($command);

        if (!$result->successful()) {
            throw new \Exception($result->errorOutput() ?: $result->output());
        }

        return $result->output();
    }

    /**
     * Get report
     */
    public function getReport(): array
    {
        return $this->report;
    }
}
