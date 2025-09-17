<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(AdminUserSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(RolePermissionSeeder::class);
    }
}
