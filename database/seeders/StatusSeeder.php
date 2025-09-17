<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                "name" => "Active",
                "description" => "Client is active and engaged",
            ],
            [
                "name" => "Inactive",
                "description" => "Client is temporarily inactive",
            ],
            [
                "name" => "Prospect",
                "description" => "Potential client being evaluated",
            ],
            [
                "name" => "Lead",
                "description" => "Qualified lead ready for engagement",
            ],
            [
                "name" => "Contract Signed",
                "description" => "Client has signed a contract",
            ],
            [
                "name" => "On Hold",
                "description" => "Client engagement is on hold",
            ],
            [
                "name" => "Completed",
                "description" => "Project or engagement completed",
            ],
            [
                "name" => "Cancelled",
                "description" => "Client relationship cancelled",
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(["name" => $status["name"]], $status);
        }
    }
}
