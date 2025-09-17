<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $query = User::with("roles");

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")->orWhere(
                    "email",
                    "like",
                    "%{$search}%",
                );
            });
        }

        // Filter by role
        if ($request->filled("role")) {
            $query->whereHas("roles", function ($q) use ($request) {
                $q->where("name", $request->role);
            });
        }

        // Filter by status
        if ($request->filled("status")) {
            if ($request->status === "active") {
                $query->whereNotNull("email_verified_at");
            } elseif ($request->status === "inactive") {
                $query->whereNull("email_verified_at");
            }
        }

        $users = $query->latest()->paginate(20)->appends($request->query());

        // Get filter options
        $roles = Role::query()->orderBy("name")->get();

        return view("admin.users.index", compact("users", "roles"));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::query()->orderBy("name")->get();
        return view("admin.users.create", compact("roles"));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User([
            "name" => $validated["name"],
            "email" => $validated["email"],
            "password" => Hash::make($validated["password"]),
            "email_verified_at" => now(),
        ]);
        $user->save();

        // Assign roles
        if (!empty($validated["roles"])) {
            $user->assignRole($validated["roles"]);
        }

        return redirect()
            ->route("admin.users.index")
            ->with("success", "User created successfully.");
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load("roles", "permissions");
        return view("admin.users.show", compact("user"));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::query()->orderBy("name")->get();
        $user->load("roles");
        return view("admin.users.edit", compact("user", "roles"));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $updateData = [
            "name" => $validated["name"],
            "email" => $validated["email"],
        ];

        // Update password if provided
        if (!empty($validated["password"])) {
            $updateData["password"] = Hash::make($validated["password"]);
        }

        // Update email verification status
        if ($validated["status"] === "active") {
            $updateData["email_verified_at"] =
                $user->email_verified_at ?? now();
        } else {
            $updateData["email_verified_at"] = null;
        }

        $user->update($updateData);

        // Sync roles
        if (isset($validated["roles"])) {
            $user->syncRoles($validated["roles"]);
        } else {
            $user->syncRoles([]);
        }

        return redirect()
            ->route("admin.users.index")
            ->with("success", "User updated successfully.");
    }

    /**
     * Update the user's status.
     */
    public function updateStatus(
        Request $request,
        User $user,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            "status" => "required|in:active,inactive",
        ]);

        $updateData = [];
        if ($validated["status"] === "active") {
            $updateData["email_verified_at"] =
                $user->email_verified_at ?? now();
        } else {
            $updateData["email_verified_at"] = null;
        }

        $user->update($updateData);

        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "User status updated successfully.",
                "user" => [
                    "id" => $user->id,
                    "status" => $user->email_verified_at
                        ? "active"
                        : "inactive",
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "User status updated successfully.");
    }

    /**
     * Bulk update user statuses.
     */
    public function bulkUpdateStatus(
        Request $request,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            "user_ids" => "required|array",
            "user_ids.*" => "exists:users,id",
            "status" => "required|in:active,inactive",
        ]);

        $updateData = [];
        if ($validated["status"] === "active") {
            $updateData["email_verified_at"] = now();
        } else {
            $updateData["email_verified_at"] = null;
        }

        $updatedCount = User::query()
            ->whereIn("id", $validated["user_ids"])
            ->update($updateData);

        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Status updated for {$updatedCount} users.",
                "updated_count" => $updatedCount,
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "Status updated for {$updatedCount} users.");
    }

    /**
     * Assign roles to user.
     */
    public function assignRoles(
        Request $request,
        User $user,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            "roles" => "required|array",
            "roles.*" => "exists:roles,name",
        ]);

        $user->syncRoles($validated["roles"]);

        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Roles assigned successfully.",
                "user" => [
                    "id" => $user->id,
                    "roles" => $user->fresh()->roles->pluck("name"),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "Roles assigned successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deletion of own account
        if ($user->id === Auth::id()) {
            return redirect()
                ->route("admin.users.index")
                ->with("error", "You cannot delete your own account.");
        }

        // Check if user has any critical relationships that should prevent deletion
        // Add any additional checks here based on your business logic

        $user->delete();

        return redirect()
            ->route("admin.users.index")
            ->with("success", "User deleted successfully.");
    }

    /**
     * Get users for API/AJAX requests.
     */
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::with("roles");

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")->orWhere(
                    "email",
                    "like",
                    "%{$search}%",
                );
            });
        }

        $users = $query
            ->orderBy("name")
            ->get()
            ->map(function ($user) {
                return [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "status" => $user->email_verified_at
                        ? "active"
                        : "inactive",
                    "roles" => $user->roles->pluck("name"),
                ];
            });

        return response()->json($users);
    }
}
