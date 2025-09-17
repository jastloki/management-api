<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample clients with different email validation statuses
        Client::create([
            "name" => "John Doe",
            "email" => "john.doe@validcompany.com",
            "phone" => "+1 (555) 123-4567",
            "company" => "Tech Solutions Inc.",
            "address" => "123 Main St, New York, NY 10001",
            "is_email_valid" => true,
        ]);

        Client::create([
            "name" => "Jane Smith",
            "email" => "jane.smith@example.com",
            "phone" => "+1 (555) 987-6543",
            "company" => "Design Studio LLC",
            "address" => "456 Oak Ave, Los Angeles, CA 90210",
            "is_email_valid" => true,
        ]);

        Client::create([
            "name" => "Bob Johnson",
            "email" => "bob.invalid@fakeemail.xyz",
            "phone" => "+1 (555) 555-0100",
            "company" => "Marketing Agency",
            "address" => "789 Pine St, Chicago, IL 60601",
            "is_email_valid" => false,
        ]);

        Client::create([
            "name" => "Alice Brown",
            "email" => "alice.brown@bounced.email",
            "phone" => "+1 (555) 444-0200",
            "company" => "Consulting Services",
            "address" => "321 Elm St, Houston, TX 77001",
            "is_email_valid" => false,
        ]);

        Client::create([
            "name" => "Mike Wilson",
            "email" => "mike.wilson@validcorp.net",
            "phone" => "+1 (555) 333-0300",
            "company" => "Software Development Co.",
            "address" => "654 Maple Ave, Phoenix, AZ 85001",
            "is_email_valid" => true,
        ]);

        Client::create([
            "name" => "Sarah Davis",
            "email" => "sarah@invaliddomain.fake",
            "phone" => null,
            "company" => null,
            "address" => null,
            "is_email_valid" => false,
        ]);
    }
}
