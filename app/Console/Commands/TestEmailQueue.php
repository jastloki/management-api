<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Jobs\SendClientEmail;

class TestEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "email:test-queue {--count=5 : Number of clients to queue}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Test the email queue by queuing emails for pending clients";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option("count");

        $this->info("Testing email queue with {$count} clients...");

        // Get pending clients
        $clients = Client::where("email_status", "pending")
            ->limit($count)
            ->get();

        if ($clients->isEmpty()) {
            $this->warn("No pending clients found. Creating test clients...");

            // Create some test clients
            for ($i = 1; $i <= $count; $i++) {
                $client = Client::create([
                    "name" => "Test Client {$i}",
                    "email" => "test{$i}@example.com",
                    "company" => "Test Company {$i}",
                    "status" => "active",
                    "email_status" => "pending",
                ]);
                $clients->push($client);
            }
        }

        $queuedCount = 0;
        foreach ($clients as $client) {
            if ($client->email_status === "pending") {
                $client->update(["email_status" => "queued"]);
                SendClientEmail::dispatch($client);
                $queuedCount++;

                $this->line(
                    "Queued email for: {$client->name} ({$client->email})",
                );
            }
        }

        $this->info("Successfully queued {$queuedCount} emails!");
        $this->info("Run 'php artisan queue:work' to process the queue.");

        return Command::SUCCESS;
    }
}
