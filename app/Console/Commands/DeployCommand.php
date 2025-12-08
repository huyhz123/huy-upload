<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:full {--skip-migrate : Skip database migration} {--skip-seed : Skip database seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Full deployment command - setup everything automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   Full Deployment Started');
        $this->info('========================================');
        $this->newLine();

        // Step 1: Clear caches
        $this->info('Step 1: Clearing caches...');
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('route:clear');
        $this->success('Caches cleared!');
        $this->newLine();

        // Step 2: Storage link
        $this->info('Step 2: Creating storage link...');
        $this->call('storage:link');
        $this->success('Storage link created!');
        $this->newLine();

        // Step 3: Migrations
        if (!$this->option('skip-migrate')) {
            $this->info('Step 3: Running migrations...');
            if ($this->confirm('Run migrations fresh? (WARNING: This will delete all data)', false)) {
                $this->call('migrate:fresh', ['--force' => true]);
            } else {
                $this->call('migrate', ['--force' => true]);
            }
            $this->success('Migrations completed!');
            $this->newLine();
        }

        // Step 4: Seeding
        if (!$this->option('skip-seed') && !$this->option('skip-migrate')) {
            $this->info('Step 4: Seeding database...');
            if ($this->confirm('Seed demo data?', true)) {
                $this->call('db:seed', ['--force' => true]);
                $this->success('Database seeded!');
                $this->newLine();
                $this->info('Demo credentials:');
                $this->line('  Admin: admin@repair.com / admin123');
                $this->line('  Staff: staff@repair.com / staff123');
                $this->line('  Customer: customer1@example.com / password');
            }
            $this->newLine();
        }

        // Step 5: Optimize
        $this->info('Step 5: Optimizing application...');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->success('Application optimized!');
        $this->newLine();

        // Step 6: Publish assets
        $this->info('Step 6: Publishing vendor assets...');
        $this->call('vendor:publish', ['--all' => true, '--force' => true]);
        $this->success('Vendor assets published!');
        $this->newLine();

        $this->info('========================================');
        $this->success('   Deployment Completed Successfully!');
        $this->info('========================================');
        $this->newLine();

        $this->info('Next steps:');
        $this->line('  1. Update .env with your configuration');
        $this->line('  2. Run: npm run build');
        $this->line('  3. Start server: php artisan serve');

        return Command::SUCCESS;
    }

    private function success($message)
    {
        $this->info("✓ $message");
    }
}
