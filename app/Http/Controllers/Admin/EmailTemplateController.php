<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the email templates.
     */
    public function index()
    {
        $templates = EmailTemplate::orderBy("name")->paginate(15);

        return view("admin.email-templates.index", compact("templates"));
    }

    /**
     * Show the form for creating a new email template.
     */
    public function create()
    {
        return view("admin.email-templates.create");
    }

    /**
     * Store a newly created email template in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255|unique:email_templates,name",
            "subject" => "required|string|max:255",
            "content" => "required|string",
            "description" => "nullable|string|max:1000",
            "is_active" => "boolean",
        ]);

        $template = new EmailTemplate($validated);

        // Extract variables from content and subject
        $template->variables = $template->extractVariables();
        $template->save();

        return redirect()
            ->route("admin.email-templates.index")
            ->with("success", "Email template created successfully.");
    }

    /**
     * Display the specified email template.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return view("admin.email-templates.show", [
            "template" => $emailTemplate,
        ]);
    }

    /**
     * Show the form for editing the specified email template.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view("admin.email-templates.edit", [
            "template" => $emailTemplate,
        ]);
    }

    /**
     * Update the specified email template in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("email_templates", "name")->ignore(
                    $emailTemplate->id,
                ),
            ],
            "subject" => "required|string|max:255",
            "content" => "required|string",
            "description" => "nullable|string|max:1000",
            "is_active" => "boolean",
        ]);

        $emailTemplate->fill($validated);

        // Extract variables from content and subject
        $emailTemplate->variables = $emailTemplate->extractVariables();
        $emailTemplate->save();

        return redirect()
            ->route("admin.email-templates.index")
            ->with("success", "Email template updated successfully.");
    }

    /**
     * Remove the specified email template from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        try {
            $emailTemplate->delete();

            return redirect()
                ->route("admin.email-templates.index")
                ->with("success", "Email template deleted successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.email-templates.index")
                ->with(
                    "error",
                    "Unable to delete email template. It may be in use.",
                );
        }
    }

    /**
     * Toggle the active status of an email template.
     */
    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        $emailTemplate->is_active = !$emailTemplate->is_active;
        $emailTemplate->save();

        $status = $emailTemplate->is_active ? "activated" : "deactivated";

        return redirect()
            ->route("admin.email-templates.index")
            ->with("success", "Email template {$status} successfully.");
    }

    /**
     * Preview an email template with sample data.
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        // Sample data for preview
        $sampleData = [
            "client_name" => "John Doe",
            "client_email" => "john.doe@example.com",
            "app_name" => config("app.name"),
            "registration_date" => now()->format("F j, Y"),
            "new_status" => "Active",
            "old_status" => "Pending",
            "update_date" => now()->format("F j, Y g:i A"),
            "user_name" => "Jane Smith",
            "reset_link" => url("/password/reset/sample-token"),
            "expiry_time" => "24",
        ];

        $parsed = $emailTemplate->parse($sampleData);

        return response()->json([
            "success" => true,
            "subject" => $parsed["subject"],
            "content" => $parsed["content"],
        ]);
    }

    /**
     * Load default templates.
     */
    public function loadDefaults()
    {
        $defaults = EmailTemplate::getDefaultTemplates();
        $created = 0;

        foreach ($defaults as $key => $templateData) {
            $exists = EmailTemplate::where(
                "name",
                $templateData["name"],
            )->exists();

            if (!$exists) {
                EmailTemplate::create($templateData);
                $created++;
            }
        }

        if ($created > 0) {
            return redirect()
                ->route("admin.email-templates.index")
                ->with(
                    "success",
                    "{$created} default template(s) loaded successfully.",
                );
        }

        return redirect()
            ->route("admin.email-templates.index")
            ->with("info", "All default templates already exist.");
    }

    /**
     * Duplicate an email template.
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->name = $emailTemplate->name . " (Copy)";
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()
            ->route("admin.email-templates.edit", $newTemplate)
            ->with("success", "Email template duplicated successfully.");
    }
}
