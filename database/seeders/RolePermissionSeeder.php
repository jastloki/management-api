<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Client permissions
            "clients.view",
            "clients.create",
            "clients.edit",
            "clients.delete",
            "clients.import",
            "clients.export",
            "clients.call",
            "clients.mail",
            "clients.status.update",
            "clients.comment.create",
            "clients.view.all",
            "clients.view.leads",

            // Status permissions
            "statuses.view",
            "statuses.create",
            "statuses.edit",
            "statuses.delete",

            // Email permissions
            "emails.view",
            "emails.send",
            "emails.analytics",
            "emails.providers",
            "emails.templates",

            // Proxy permissions
            "proxies.view",
            "proxies.create",
            "proxies.edit",
            "proxies.delete",
            "proxies.test",
            "proxies.export",

            // Role permissions
            "roles.view",
            "roles.create",
            "roles.edit",
            "roles.delete",

            // User permissions
            "users.view",
            "users.create",
            "users.edit",
            "users.delete",

            // Admin permissions
            "admin.dashboard",
            "admin.settings",

            // Email analytics permissions
            "emails.analytics",
            "emails.providers",

            // Email queue permissions
            "queue.view",
            "queue.send",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(["name" => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(["name" => "admin"]);
        $managerRole = Role::firstOrCreate(["name" => "manager"]);
        $userRole = Role::firstOrCreate(["name" => "user"]);

        // Assign permissions to admin role (all permissions)
        $adminRole->syncPermissions(Permission::all());

        // Assign permissions to manager role
        $managerRole->syncPermissions([
            "clients.view",
            "clients.view.leads",
            "clients.create",
            "clients.edit",
            "clients.import",
            "clients.export",
            "statuses.view",
            "statuses.create",
            "statuses.edit",
            "emails.view",
            "emails.send",
            "emails.analytics",
            "proxies.view",
            "proxies.create",
            "proxies.edit",
            "proxies.test",
            "admin.dashboard",
        ]);

        // Assign permissions to user role
        $userRole->syncPermissions([
            "clients.view",
            "emails.view",
            "proxies.view",
            "admin.dashboard",
        ]);

        // Assign admin role to existing admin users
        $adminUsers = User::where("role", "admin")->get();
        foreach ($adminUsers as $user) {
            $user->assignRole("admin");
        }
    }
}
