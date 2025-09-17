<?php

namespace App\Console\Commands;

use App\Jobs\CheckClientEmailValidityJob;
use App\Models\Client;
use Illuminate\Console\Command;

class CheckEmailValidityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:check-email-validity
                            {--chunk-size=100 : Number of clients to process per chunk}
                            {--force : Force processing even if no invalid emails found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check email validity for all clients with invalid email addresses';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $chunkSize = $this->option('chunk-size');
        $force = $this->option('force');

        // Validate chunk size
        if ($chunkSize < 1 || $chunkSize > 1000) {
            $this->error('Chunk size must be between 1 and 1000');
            return 1;
        }

        $this->info('Starting email validity check...');

        // Count total clients with invalid emails
        $totalInvalidClients = Client::where('is_email_valid', false)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        $totalClients = Client::whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        $this->info("Total clients with emails: {$totalClients}");
        $this->info("Clients with invalid emails: {$totalInvalidClients}");

        if ($totalInvalidClients === 0 && !$force) {
            $this->info('No clients with invalid emails found.');

            if ($this->confirm('Do you want to recheck all client emails?')) {
                // Reset all email validity flags
                Client::whereNotNull('email')
                    ->where('email', '!=', '')
                    ->update(['is_email_valid' => false]);

                $totalInvalidClients = $totalClients;
                $this->info("Reset email validity for {$totalClients} clients");
            } else {
                return 0;
            }
        }

        if ($totalInvalidClients === 0) {
            $this->info('No clients to process.');
            return 0;
        }

        $estimatedChunks = ceil($totalInvalidClients / $chunkSize);

        $this->info("Processing {$totalInvalidClients} clients in chunks of {$chunkSize}");
        $this->info("Estimated number of job chunks: {$estimatedChunks}");

        if ($this->confirm('Do you want to proceed?')) {
            // Dispatch the first job to start the chain
            CheckClientEmailValidityJob::dispatch(1, $chunkSize);

            $this->info('Email validation jobs dispatched successfully!');
            $this->info('Monitor the queue with: php artisan queue:work');
            $this->info('Check logs for detailed progress information.');

            return 0;
        }

        $this->info('Operation cancelled.');
        return 0;
    }
}
