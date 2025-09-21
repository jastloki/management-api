<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the new permission
        $permission = Permission::firstOrCreate([
            "name" => "clients.view.leads",
            "guard_name" => "web",
        ]);

        // Assign the permission to admin role
        $adminRole = Role::where("name", "admin")->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        // Optionally assign to manager role
        $managerRole = Role::where("name", "manager")->first();
        if ($managerRole) {
            $managerRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permission
        $permission = Permission::where("name", "clients.view.leads")->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
