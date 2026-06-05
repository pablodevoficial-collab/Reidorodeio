<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class StartQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:start-worker {--tries=3} {--timeout=90} {--sleep=3} {--max-jobs=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the queue worker for processing realtime jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting queue worker for realtime processing...');

        $tries = $this->option('tries');
        $timeout = $this->option('timeout');
        $sleep = $this->option('sleep');
        $maxJobs = $this->option('max-jobs');

        $this->info("Configuration:");
        $this->info("- Tries: {$tries}");
        $this->info("- Timeout: {$timeout} seconds");
        $this->info("- Sleep: {$sleep} seconds");
        $this->info("- Max jobs: {$maxJobs}");

        // Execute the queue work command
        Artisan::call('queue:work', [
            'connection' => 'database',
            '--tries' => $tries,
            '--timeout' => $timeout,
            '--sleep' => $sleep,
            '--max-jobs' => $maxJobs,
            '--verbose' => true,
            '--no-interaction' => true
        ]);

        return Command::SUCCESS;
    }
}
