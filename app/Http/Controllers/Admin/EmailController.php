<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendClientEmail;
use App\Models\Client;
use App\Services\Mail\MailProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailController extends Controller
{
    /**
     * Display the email queue page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $query = Client::query();

        // Filter by email status if provided
        if ($request->filled("email_status")) {
            $query->where("email_status", $request->email_status);
        }

        // Filter by email validity if provided
        if ($request->filled("is_email_valid")) {
            $query->where("is_email_valid", $request->is_email_valid == "1");
        }

        // Filter for queue-eligible clients if provided
        if (
            $request->filled("queue_eligible") &&
            $request->queue_eligible == "1"
        ) {
            $query
                ->where("is_email_valid", true)
                ->whereIn("email_status", ["pending", "failed"]);
        }

        // Filter by search term if provided
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%")
                    ->orWhere("company", "like", "%{$search}%");
            });
        }

        // Get clients with pagination
        $clients = $query->orderBy("created_at", "desc")->paginate(15);

        // Get statistics for email statuses
        $statistics = [
            "total" => Client::query()->count(),
            "pending" => Client::query()
                ->where("email_status", "pending")
                ->count(),
            "queued" => Client::query()
                ->where("email_status", "queued")
                ->count(),
            "sending" => Client::query()
                ->where("email_status", "sending")
                ->count(),
            "sent" => Client::query()->where("email_status", "sent")->count(),
            "failed" => Client::query()
                ->where("email_status", "failed")
                ->count(),
            "valid_emails" => Client::query()
                ->where("is_email_valid", true)
                ->count(),
            "invalid_emails" => Client::query()
                ->where("is_email_valid", false)
                ->count(),
            "queue_eligible" => Client::query()
                ->where("is_email_valid", true)
                ->whereIn("email_status", ["pending", "failed"])
                ->count(),
        ];

        // Get available mail providers for the UI
        $providers = MailProviderFactory::getProvidersInfo();

        return view(
            "admin.emails.index",
            compact("clients", "statistics", "providers"),
        );
    }

    /**
     * Queue an email for a single client.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function queueSingle(
        Client $client,
        Request $request,
    ): \Illuminate\Http\RedirectResponse {
        // Check if the client is eligible for sending an email
        if (!in_array($client->email_status, ["pending", "failed"])) {
            return redirect()
                ->route("admin.emails.index")
                ->with(
                    "error",
                    "Client's email is already in {$client->email_status} status and cannot be queued.",
                );
        }

        // Check if the client has a valid email
        if (!$client->is_email_valid) {
            return redirect()
                ->route("admin.emails.index")
                ->with(
                    "error",
                    "Cannot queue email for {$client->name} - email address is marked as invalid.",
                );
        }

        // Get provider from request or use client's current provider or default
        $provider =
            $request->input("email_provider") ?:
            $client->email_provider ?:
            config("mail.default_provider", "smtp");

        // Validate provider
        try {
            MailProviderFactory::makeFromConfig($provider)->validateConfig();
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.emails.index")
                ->with(
                    "error",
                    "Invalid email provider '{$provider}': " . $e->getMessage(),
                );
        }

        // Update client status and provider
        $client->update([
            "email_status" => "queued",
            "email_provider" => $provider,
        ]);

        // Dispatch the job to the queue
        SendClientEmail::dispatch($client);

        return redirect()
            ->route("admin.emails.index")
            ->with(
                "success",
                "Email for {$client->name} has been queued using {$provider} provider.",
            );
    }

    /**
     * Queue emails for multiple clients.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function queueBatch(
        Request $request,
    ): \Illuminate\Http\RedirectResponse {
        $validated = $request->validate([
            "client_ids" => "required|array",
            "client_ids.*" => "exists:clients,id",
            "email_provider" =>
                "nullable|string|in:" .
                implode(",", MailProviderFactory::getAvailableProviders()),
        ]);

        // Get provider or use default
        $provider =
            $validated["email_provider"] ??
            config("mail.default_provider", "smtp");

        // Validate provider
        try {
            MailProviderFactory::makeFromConfig($provider)->validateConfig();
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.emails.index")
                ->with(
                    "error",
                    "Invalid email provider '{$provider}': " . $e->getMessage(),
                );
        }

        $count = 0;
        foreach ($validated["client_ids"] as $clientId) {
            $client = Client::query()->find($clientId);

            // Only queue emails for clients with pending or failed status and valid emails
            if (
                $client &&
                in_array($client->email_status, ["pending", "failed"]) &&
                $client->is_email_valid
            ) {
                $client->update([
                    "email_status" => "queued",
                    "email_provider" => $provider,
                ]);
                SendClientEmail::dispatch($client);
                $count++;
            }
        }

        return redirect()
            ->route("admin.emails.index")
            ->with(
                "success",
                "{$count} emails have been queued for sending using {$provider} provider.",
            );
    }

    /**
     * Queue emails for all clients with a specific status.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function queueAll(
        Request $request,
    ): \Illuminate\Http\RedirectResponse {
        $validated = $request->validate([
            "status" => "required|in:pending,failed,all",
            "email_provider" =>
                "nullable|string|in:" .
                implode(",", MailProviderFactory::getAvailableProviders()),
        ]);

        // Get provider or use default
        $provider =
            $validated["email_provider"] ??
            config("mail.default_provider", "smtp");

        // Validate provider
        try {
            MailProviderFactory::makeFromConfig($provider)->validateConfig();
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.emails.index")
                ->with(
                    "error",
                    "Invalid email provider '{$provider}': " . $e->getMessage(),
                );
        }

        $query = Client::query();

        if ($validated["status"] !== "all") {
            $query->where("email_status", $validated["status"]);
        } else {
            $query->whereIn("email_status", ["pending", "failed"]);
        }

        // Only queue emails for clients with valid email addresses
        $query->where("is_email_valid", true);

        $clients = $query->get();
        $count = 0;

        foreach ($clients as $client) {
            $client->update([
                "email_status" => "queued",
                "email_provider" => $provider,
            ]);
            SendClientEmail::dispatch($client);
            $count++;
        }

        return redirect()
            ->route("admin.emails.index")
            ->with(
                "success",
                "{$count} emails have been queued for sending using {$provider} provider.",
            );
    }

    /**
     * Reset the email status for a client.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetStatus(
        Client $client,
    ): \Illuminate\Http\RedirectResponse {
        $client->update([
            "email_status" => "pending",
            "email_sent_at" => null,
        ]);

        return redirect()
            ->route("admin.emails.index")
            ->with(
                "success",
                "Email status for {$client->name} has been reset to pending.",
            );
    }

    /**
     * Reset the email status for multiple clients.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetBatch(
        Request $request,
    ): \Illuminate\Http\RedirectResponse {
        $validated = $request->validate([
            "client_ids" => "required|array",
            "client_ids.*" => "exists:clients,id",
        ]);

        Client::query()
            ->whereIn("id", $validated["client_ids"])
            ->update([
                "email_status" => "pending",
                "email_sent_at" => null,
            ]);

        $count = count($validated["client_ids"]);
        return redirect()
            ->route("admin.emails.index")
            ->with(
                "success",
                "Email status for {$count} clients has been reset to pending.",
            );
    }

    /**
     * Show email analytics and reports.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function analytics(): \Illuminate\Contracts\View\View
    {
        // Get daily email sending statistics for the last 30 days
        $dailyStats = DB::table("clients")
            ->select(
                DB::raw("DATE(email_sent_at) as date"),
                DB::raw("count(*) as total"),
            )
            ->whereNotNull("email_sent_at")
            ->where("email_status", "sent")
            ->where("email_sent_at", ">=", now()->subDays(30))
            ->groupBy("date")
            ->orderBy("date")
            ->get();

        // Get status distribution
        $statusDistribution = DB::table("clients")
            ->select("email_status", DB::raw("count(*) as count"))
            ->groupBy("email_status")
            ->get();

        return view(
            "admin.emails.analytics",
            compact("dailyStats", "statusDistribution"),
        );
    }

    /**
     * Get mail provider status information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function providerStatus(): \Illuminate\Http\JsonResponse
    {
        try {
            $providers = MailProviderFactory::getProvidersInfo();
            return response()->json([
                "success" => true,
                "providers" => $providers,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Test a mail provider connection.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testProvider(
        Request $request,
    ): \Illuminate\Http\JsonResponse {
        $validated = $request->validate([
            "provider" =>
                "required|string|in:" .
                implode(",", MailProviderFactory::getAvailableProviders()),
        ]);

        try {
            $provider = MailProviderFactory::makeFromConfig(
                $validated["provider"],
            );

            // Try to test connection, fallback to availability check
            try {
                if (method_exists($provider, "testConnection")) {
                    $result = $provider->testConnection();
                    $status = $result ? "connected" : "failed";
                } else {
                    $available = $provider->isAvailable();
                    $status = $available ? "available" : "unavailable";
                }
            } catch (\Exception $testException) {
                $status = "failed";
            }

            return response()->json([
                "success" => true,
                "provider" => $validated["provider"],
                "status" => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "provider" => $validated["provider"],
                    "error" => $e->getMessage(),
                ],
                400,
            );
        }
    }

    /**
     * Show email provider management page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function providers(): \Illuminate\Contracts\View\View
    {
        // Get available mail providers for the UI
        $providers = MailProviderFactory::getProvidersInfo();

        return view("admin.emails.providers", compact("providers"));
    }
}
