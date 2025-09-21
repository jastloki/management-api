<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RolePermissionController extends Controller
{
    /**
     * Display roles and permissions management page
     */
    public function index(): View
    {
        $this->authorize("roles.view");

        $roles = Role::with("permissions")->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(".", $permission->name)[0];
        });
        $users = User::with("roles")->get();

        return view(
            "admin.roles.index",
            compact("roles", "permissions", "users"),
        );
    }

    /**
     * Store a new role
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $this->authorize("roles.create");

        $request->validate([
            "name" => "required|string|max:255|unique:roles",
            "permissions" => "array",
            "permissions.*" => "exists:permissions,id",
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create(["name" => $request->name]);

            if ($request->permissions) {
                $role->permissions()->attach($request->permissions);
            }

            DB::commit();

            return redirect()
                ->route("admin.roles.index")
                ->with("success", "Role created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with("error", "Failed to create role: " . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update an existing role
     */
    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $this->authorize("roles.edit");

        $request->validate([
            "name" => "required|string|max:255|unique:roles,name," . $role->id,
            "permissions" => "array",
            "permissions.*" => "exists:permissions,id",
        ]);

        try {
            DB::beginTransaction();

            $role->update(["name" => $request->name]);
            $role->permissions()->sync($request->permissions ?? []);

            DB::commit();

            return redirect()
                ->route("admin.roles.index")
                ->with("success", "Role updated successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with("error", "Failed to update role: " . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a role
     */
    public function destroyRole(Role $role): RedirectResponse
    {
        $this->authorize("roles.delete");

        try {
            // Check if role is assigned to any users
            if ($role->users()->count() > 0) {
                return redirect()
                    ->back()
                    ->with(
                        "error",
                        "Cannot delete role that is assigned to users.",
                    );
            }

            $role->delete();

            return redirect()
                ->route("admin.roles.index")
                ->with("success", "Role deleted successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with("error", "Failed to delete role: " . $e->getMessage());
        }
    }

    /**
     * Store a new permission
     */
    public function storePermission(Request $request): RedirectResponse
    {
        $this->authorize("roles.create");

        $request->validate([
            "name" => "required|string|max:255|unique:permissions",
        ]);

        try {
            Permission::create(["name" => $request->name]);

            return redirect()
                ->route("admin.roles.index")
                ->with("success", "Permission created successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to create permission: " . $e->getMessage(),
                )
                ->withInput();
        }
    }

    /**
     * Delete a permission
     */
    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $this->authorize("roles.delete");

        try {
            $permission->delete();

            return redirect()
                ->route("admin.roles.index")
                ->with("success", "Permission deleted successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to delete permission: " . $e->getMessage(),
                );
        }
    }

    /**
     * Assign roles to a user
     */
    public function assignUserRoles(Request $request): RedirectResponse
    {
        $this->authorize("users.edit");

        $request->validate([
            "roles" => "array",
            "user_id" => "required|exists:users,id",
            "roles.*" => "exists:roles,id",
        ]);

        $user = User::query()->findOrFail($request->user_id);

        try {
            $roles = Role::query()
                ->whereIn("id", $request->roles ?? [])
                ->pluck("name");
            $user->syncRoles($roles);

            return redirect()
                ->route("admin.roles.index")
                ->with("success", "User roles updated successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to update user roles: " . $e->getMessage(),
                );
        }
    }

    /**
     * Create default permissions
     */
    public function createDefaultPermissions(): RedirectResponse
    {
        $this->authorize("roles.create");

        $permissions = [
            // Client permissions
            "clients.view",
            "clients.view.leads",
            "clients.create",
            "clients.edit",
            "clients.delete",
            "clients.import",
            "clients.export",

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
        ];

        try {
            foreach ($permissions as $permission) {
                Permission::query()->firstOrCreate(["name" => $permission]);
            }

            // Create default admin role with all permissions
            $adminRole = Role::query()->firstOrCreate(["name" => "admin"]);
            $adminRole->syncPermissions(Permission::all());

            // Create default user role with limited permissions
            $userRole = Role::query()->firstOrCreate(["name" => "user"]);
            $userRole->syncPermissions([
                "clients.view",
                "emails.view",
                "admin.dashboard",
            ]);

            return redirect()
                ->route("admin.roles.index")
                ->with(
                    "success",
                    "Default permissions and roles created successfully!",
                );
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Failed to create default permissions: " . $e->getMessage(),
                );
        }
    }
}
