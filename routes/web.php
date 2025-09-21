<?php

use App\Models\Client;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\ProxyController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\UserController;
use App\Mail\HtmlMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

Route::middleware("guest")->group(function () {
    Route::get("/login", [AuthController::class, "showLogin"])->name("login");
    Route::post("/login", [AuthController::class, "login"]);
});

// Admin Authentication Routes
Route::name("admin.")->group(function () {
    // Guest routes (not authenticated)

    // Authenticated admin routes
    Route::middleware(["auth", "admin"])->group(function () {
        Route::get("/", [AdminController::class, "dashboard"])->name(
            "dashboard",
        );
        Route::post("/logout", [AuthController::class, "logout"])->name(
            "logout",
        );

        // Client Management Routes
        Route::resource("clients", ClientController::class);

        // Leads Management Route (alias for clients with converted=false)
        Route::get("leads", function () {
            return redirect()->route("admin.clients.index", [
                "converted" => "false",
            ]);
        })->name("leads.index");

        // Client Status Update Route
        Route::patch("/clients/{client}/status", [
            ClientController::class,
            "updateStatus",
        ])->name("clients.update.status");

        // Client Bulk Status Update Route
        Route::patch("/clients/bulk/bulk-status", [
            ClientController::class,
            "bulkUpdateStatus",
        ])->name("clients.bulk.update.status");

        // Client Bulk Assign Route
        Route::post("/clients/bulk/bulk-assign", [
            ClientController::class,
            "bulkAssign",
        ])->name("clients.bulk-assign");

        // Client Bulk Delete Route
        Route::delete("/clients/bulk/bulk-delete", [
            ClientController::class,
            "bulkDelete",
        ])->name("clients.bulk-delete");

        // Client Bulk Make Client Route
        Route::post("/clients/bulk/bulk-make-client", [
            ClientController::class,
            "bulkMakeClient",
        ])->name("clients.bulk-make-client");

        // Status Management Routes
        Route::resource("statuses", StatusController::class);

        // Email Template Management Routes
        Route::resource("email-templates", EmailTemplateController::class);
        Route::post("/email-templates/{email_template}/toggle-status", [
            EmailTemplateController::class,
            "toggleStatus",
        ])->name("email-templates.toggle-status");
        Route::get("/email-templates/{email_template}/preview", [
            EmailTemplateController::class,
            "preview",
        ])->name("email-templates.preview");
        Route::post("/email-templates/load-defaults", [
            EmailTemplateController::class,
            "loadDefaults",
        ])->name("email-templates.load-defaults");
        Route::post("/email-templates/{email_template}/duplicate", [
            EmailTemplateController::class,
            "duplicate",
        ])->name("email-templates.duplicate");

        // Proxy Management Routes
        Route::get("/proxies/export", [ProxyController::class, "export"])->name(
            "proxies.export",
        );
        Route::post("/proxies/bulk-test", [
            ProxyController::class,
            "bulkTest",
        ])->name("proxies.bulk-test");
        Route::resource("proxies", ProxyController::class);
        Route::post("/proxies/{proxy}/toggle-status", [
            ProxyController::class,
            "toggleStatus",
        ])->name("proxies.toggle-status");
        Route::post("/proxies/{proxy}/test", [
            ProxyController::class,
            "test",
        ])->name("proxies.test");
        Route::post("/proxies/{proxy}/duplicate", [
            ProxyController::class,
            "duplicate",
        ])->name("proxies.duplicate");

        // Status API Routes
        Route::get("/api/statuses", [
            StatusController::class,
            "getStatuses",
        ])->name("api.statuses");

        // Client Import Routes
        Route::get("/clients-import", [
            ClientController::class,
            "showImport",
        ])->name("clients.import.show");
        Route::post("/clients-import", [
            ClientController::class,
            "import",
        ])->name("clients.import");
        Route::get("/clients-template", [
            ClientController::class,
            "downloadTemplate",
        ])->name("clients.template");

        // Client Email Validation Routes
        Route::post("/clients/check-email-validity", [
            ClientController::class,
            "checkEmailValidity",
        ])->name("clients.check.email.validity");

        // Email Validation Statistics Route
        Route::get("/email-validation-stats", [
            AdminController::class,
            "emailValidationStats",
        ])->name("admin.email.validation.stats");

        // Email Queue Management Routes
        Route::get("/emails", [EmailController::class, "index"])->name(
            "emails.index",
        );
        Route::get("/emails/analytics", [
            EmailController::class,
            "analytics",
        ])->name("emails.analytics");
        Route::post("/emails/queue/{client}", [
            EmailController::class,
            "queueSingle",
        ])->name("emails.queue.single");
        Route::post("/emails/queue-batch", [
            EmailController::class,
            "queueBatch",
        ])->name("emails.queue.batch");
        Route::post("/emails/queue-all", [
            EmailController::class,
            "queueAll",
        ])->name("emails.queue.all");
        Route::post("/emails/reset/{client}", [
            EmailController::class,
            "resetStatus",
        ])->name("emails.reset.single");
        Route::post("/emails/reset-batch", [
            EmailController::class,
            "resetBatch",
        ])->name("emails.reset.batch");

        // Email Provider Management Routes
        Route::get("/emails/providers/status", [
            EmailController::class,
            "providerStatus",
        ])->name("emails.providers.status");
        Route::post("/emails/providers/test", [
            EmailController::class,
            "testProvider",
        ])->name("emails.providers.test");
        Route::get("/emails/providers", [
            EmailController::class,
            "providers",
        ])->name("emails.providers");

        // Roles and Permissions Management Routes
        Route::get("/roles", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "index",
        ])->name("roles.index");
        Route::post("/roles", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "storeRole",
        ])->name("roles.store");
        Route::put("/roles/{role}", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "updateRole",
        ])->name("roles.update");
        Route::delete("/roles/{role}", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "destroyRole",
        ])->name("roles.destroy");
        Route::post("/permissions", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "storePermission",
        ])->name("permissions.store");
        Route::delete("/permissions/{permission}", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "destroyPermission",
        ])->name("permissions.destroy");
        Route::post("/users/attach/roles", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "assignUserRoles",
        ])->name("users.attach.roles");
        Route::get("/roles/create-defaults", [
            \App\Http\Controllers\Admin\RolePermissionController::class,
            "createDefaultPermissions",
        ])->name("roles.create-defaults");

        // User Management Routes
        Route::resource("users", UserController::class)->middleware([
            "index" => "permission:users.view",
            "show" => "permission:users.view",
            "create" => "permission:users.create",
            "store" => "permission:users.create",
            "edit" => "permission:users.edit",
            "update" => "permission:users.edit",
            "destroy" => "permission:users.delete",
        ]);

        // User Status Update Route
        Route::patch("/users/{user}/status", [
            UserController::class,
            "updateStatus",
        ])
            ->name("users.update.status")
            ->middleware("permission:users.edit");

        // User Bulk Status Update Route
        Route::patch("/users/bulk-status", [
            UserController::class,
            "bulkUpdateStatus",
        ])
            ->name("users.bulk.status")
            ->middleware("permission:users.edit");

        // User Role Assignment Route
        Route::patch("/users/{user}/roles", [
            UserController::class,
            "assignRoles",
        ])
            ->name("users.roles.assign")
            ->middleware("permission:users.edit");

        // Users API Routes
        Route::get("/api/users", [UserController::class, "getUsers"])->name(
            "api.users",
        );
    });
});

Route::post("/send-html-email", function (Request $request) {
    $request->validate([
        "ids" => "required|array",
        "template_id" => "required|exists:email_templates,id",
        "proxy_id" => "nullable|exists:proxies,id",
    ]);

    try {
        $template = \App\Models\EmailTemplate::findOrFail(
            $request->template_id,
        );

        if (!$template->is_active) {
            return back()->with(
                "error",
                "The selected email template is not active.",
            );
        }

        // Get proxy if selected
        $proxy = null;
        if ($request->proxy_id) {
            $proxy = \App\Models\Proxy::active()->find($request->proxy_id);
            if (!$proxy) {
                return back()->with(
                    "error",
                    "Selected proxy is not available or inactive.",
                );
            }
        }

        $clientCount = 0;
        Client::whereIn("id", $request->ids)->chunk(100, function (
            $clients,
        ) use ($request, $template, $proxy, &$clientCount) {
            foreach ($clients as $client) {
                // Create the mail instance
                $mail = new HtmlMail($client, $template);

                // If proxy is selected, store it for use in mail sending
                if ($proxy) {
                    // You can add proxy information to the mail class or handle it in the queue job
                    $mail->with(["proxy" => $proxy]);
                }

                Mail::to($client->email)->queue($mail);
                $clientCount++;
            }
        });

        $proxyMessage = $proxy ? " using proxy: {$proxy->name}" : "";
        return back()->with(
            "success",
            "Email queued successfully for {$clientCount} recipient(s) using template: {$template->name}{$proxyMessage}",
        );
    } catch (\Exception $e) {
        return back()->withErrors([
            "email_send_error" => "Failed to send email: " . $e->getMessage(),
        ]);
    }
})->name("send.html.email");
