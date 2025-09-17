<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            "total_clients" => Client::count(),
            "active_clients" => Client::whereHas("status", function ($query) {
                $query->where("name", "active");
            })->count(),
            "inactive_clients" => Client::whereHas("status", function ($query) {
                $query->where("name", "inactive");
            })->count(),
            "total_users" => User::count(),
        ];

        // Email validation statistics
        $email_stats = [
            "total_with_email" => Client::whereNotNull("email")
                ->where("email", "!=", "")
                ->count(),
            "valid_emails" => Client::where("is_email_valid", true)->count(),
            "invalid_emails" => Client::where("is_email_valid", false)->count(),
            "never_validated" => Client::whereNull("email_last_validated_at")
                ->whereNotNull("email")
                ->where("email", "!=", "")
                ->count(),
            "recently_validated" => Client::where(
                "email_last_validated_at",
                ">=",
                now()->subHours(24),
            )->count(),
        ];

        $recent_clients = Client::latest()->limit(5)->get();

        return view(
            "admin.dashboard",
            compact("stats", "email_stats", "recent_clients"),
        );
    }

    /**
     * Show email validation statistics page
     */
    public function emailValidationStats()
    {
        $stats = [
            "total_clients" => Client::count(),
            "total_with_email" => Client::whereNotNull("email")
                ->where("email", "!=", "")
                ->count(),
            "valid_emails" => Client::where("is_email_valid", true)->count(),
            "invalid_emails" => Client::where("is_email_valid", false)->count(),
            "never_validated" => Client::whereNull("email_last_validated_at")
                ->whereNotNull("email")
                ->where("email", "!=", "")
                ->count(),
            "recently_validated" => Client::where(
                "email_last_validated_at",
                ">=",
                now()->subHours(24),
            )->count(),
        ];

        // Get validation reason breakdown
        $validation_reasons = Client::where("is_email_valid", false)
            ->whereNotNull("email_validation_reason")
            ->selectRaw("email_validation_reason, COUNT(*) as count")
            ->groupBy("email_validation_reason")
            ->orderBy("count", "desc")
            ->get()
            ->pluck("count", "email_validation_reason")
            ->toArray();

        // Get recent validation activity
        $recent_validations = Client::whereNotNull("email_last_validated_at")
            ->orderBy("email_last_validated_at", "desc")
            ->limit(20)
            ->get([
                "id",
                "name",
                "email",
                "is_email_valid",
                "email_validation_reason",
                "email_last_validated_at",
            ]);

        return view(
            "admin.email-validation-stats",
            compact("stats", "validation_reasons", "recent_validations"),
        );
    }
}
