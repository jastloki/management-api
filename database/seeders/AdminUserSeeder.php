<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            "name" => "Admin User",
            "email" => "admin@example.com",
            "password" => Hash::make("password"),
            "role" => "admin",
        ]);

        echo "Admin user created:\n";
        echo "Email: admin@example.com\n";
        echo "Password: password\n\n";

        // Create some sample clients
    }
}
