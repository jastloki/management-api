<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing clients and users
        $clients = Client::take(5)->get();
        $users = User::take(3)->get();

        if ($clients->isEmpty() || $users->isEmpty()) {
            $this->command->info(
                "No clients or users found. Please seed clients and users first.",
            );
            return;
        }

        // Create sample comments
        $sampleComments = [
            [
                "title" => "Initial Contact",
                "comment" =>
                    "First contact made via phone. Client expressed interest in our premium services.",
                "status" => "active",
                "type" => "comment",
            ],
            [
                "title" => "Follow-up Meeting",
                "comment" =>
                    "Had a productive meeting discussing their requirements. They need a custom solution.",
                "status" => "active",
                "type" => "comment",
            ],
            [
                "title" => "Payment Issue",
                "comment" =>
                    "Client reported issues with payment processing. Escalated to finance team.",
                "status" => "active",
                "type" => "comment",
            ],
            [
                "title" => null,
                "comment" =>
                    "Quick note: Client prefers email communication over phone calls.",
                "status" => "active",
                "type" => "comment",
            ],
            [
                "title" => "Service Feedback",
                "comment" =>
                    "Client provided positive feedback about our service quality. Very satisfied with the delivery timeline.",
                "status" => "active",
                "type" => "comment",
            ],
            [
                "title" => "Contract Renewal",
                "comment" =>
                    "Discussed contract renewal terms. Client is interested in extending for another year with additional features.",
                "status" => "active",
                "type" => "comment",
            ],
        ];

        foreach ($clients as $client) {
            // Add 2-4 random comments per client
            $commentCount = rand(2, 4);
            $randomComments = collect($sampleComments)->random($commentCount);

            foreach ($randomComments as $commentData) {
                ClientComment::create([
                    "client_id" => $client->id,
                    "user_id" => $users->random()->id,
                    "title" => $commentData["title"],
                    "comment" => $commentData["comment"],
                    "status" => $commentData["status"],
                    "type" => $commentData["type"],
                    "created_at" => now()->subDays(rand(1, 30)),
                    "updated_at" => now()->subDays(rand(0, 30)),
                ]);
            }
        }

        $this->command->info("Client comments seeded successfully!");
    }
}
