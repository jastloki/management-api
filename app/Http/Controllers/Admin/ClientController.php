<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\HtmlMail;
use App\Models\Client;
use App\Models\Status;
use App\Models\User;
use App\Imports\ClientsImport;
use App\Jobs\CheckClientEmailValidityJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware("permission:clients.view")->only("index");
        $this->middleware("permission:clients.view")->only("show");
        $this->middleware("permission:clients.create")->only("create");
        $this->middleware("permission:clients.create")->only("store");
        $this->middleware("permission:clients.edit")->only("edit");
        $this->middleware("permission:clients.edit")->only("update");
        $this->middleware("permission:clients.delete")->only("destroy");
        $this->middleware("permission:clients.status.update")->only(
            "updateStatus",
            "bulkUpdateStatus",
        );
        $this->middleware("permission:clients.edit")->only("bulkAssign");
        $this->middleware("permission:clients.delete")->only("bulkDelete");
        $this->middleware("permission:clients.edit")->only("bulkMakeClient");
    }

    /**
     * Display a listing of the clients.
     */
    public function index(Request $request)
    {
        $query = Client::with("status", "user", "comments.user");

        // Filter by status
        if ($request->filled("status_id")) {
            $query->where("status_id", $request->status_id);
        }

        if ($request->has("converted")) {
            // Check permission for viewing leads
            if (
                $request->converted === "false" &&
                !auth()->user()->can("clients.view.leads")
            ) {
                abort(403, "You don't have permission to view leads.");
            }
            $query->where("converted", $request->converted === "true");
        } else {
            $query->where("converted", true);
        }

        // Filter by user
        if ($request->filled("user_id")) {
            $query->where("user_id", $request->user_id);
        }

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%")
                    ->orWhere("company", "like", "%{$search}%");
            });
        }

        if (
            !auth()->user()->hasRole("admin") &&
            !auth()->user()->hasAnyPermission("clients.view.all")
        ) {
            $query->where("user_id", "=", auth()->user()->id);
        }

        $clients = $query
            ->latest()
            ->orderBy("updated_at", "desc")
            ->paginate($request->limit ?? 50)
            ->appends($request->query());

        // Get filter options
        $statuses = Status::orderBy("name")->get();
        $users = User::orderBy("name")->get();

        return view(
            "admin.clients.index",
            compact("clients", "statuses", "users"),
        );
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        // Check permission for creating leads
        if (
            request("converted") === "false" &&
            !auth()->user()->can("clients.view.leads")
        ) {
            abort(403, "You don't have permission to create leads.");
        }

        $statuses = Status::orderBy("name")->get();
        $users = User::orderBy("name")->get();
        return view("admin.clients.create", compact("statuses", "users"));
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:clients,email",
            "phone" => "nullable|string|max:20",
            "company" => "nullable|string|max:255",
            "address" => "nullable|string",
            "status_id" => "required|exists:statuses,id",
            "user_id" => "required|exists:users,id",
            "is_email_valid" => "required|boolean",
            "converted" => "nullable|string|in:true,false",
        ]);

        $validated["converted"] = $validated["converted"] === "true";

        Client::create($validated);

        $type = $validated["converted"] ? "Client" : "Lead";

        return redirect()
            ->route("admin.clients.index", [
                "converted" => $request->input("converted", "true"),
            ])
            ->with("success", "{$type} created successfully.");
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        $client->load("status");
        return view("admin.clients.show", compact("client"));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        $statuses = Status::orderBy("name")->paginate(100);
        $users = User::orderBy("name")->get();
        return view(
            "admin.clients.edit",
            compact("client", "statuses", "users"),
        );
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:clients,email," . $client->id,
            "phone" => "nullable|string|max:20",
            "company" => "nullable|string|max:255",
            "address" => "nullable|string",
            "status_id" => "required|exists:statuses,id",
            "user_id" => "required|exists:users,id",
            "is_email_valid" => "required|boolean",
            "converted" => "nullable|string|in:true,false",
        ]);

        if (isset($validated["converted"])) {
            $validated["converted"] = $validated["converted"] === "true";
        }

        $client->update($validated);

        $type = $client->converted ? "Client" : "Lead";

        return redirect()
            ->route("admin.clients.index", [
                "converted" => $client->converted ? "true" : "false",
            ])
            ->with("success", "{$type} updated successfully.");
    }

    /**
     * Update the client's status.
     */
    public function updateStatus(Request $request, Client $client)
    {
        $validated = $request->validate([
            "status_id" => "required|exists:statuses,id",
        ]);

        $client->update(["status_id" => $validated["status_id"]]);

        // Load the status relationship for the response
        $client->load("status");

        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Status updated successfully.",
                "client" => [
                    "id" => $client->id,
                    "status_id" => $client->status_id,
                    "status" => $client->status
                        ? [
                            "id" => $client->status->id,
                            "name" => $client->status->name,
                        ]
                        : null,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "Client status updated successfully.");
    }

    /**
     * Bulk update client statuses.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            "client_ids" => "required|array",
            "client_ids.*" => "exists:clients,id",
            "status_id" => "required|exists:statuses,id",
        ]);

        $updatedCount = Client::whereIn("id", $validated["client_ids"])->update(
            ["status_id" => $validated["status_id"]],
        );

        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Status updated for {$updatedCount} clients.",
                "updated_count" => $updatedCount,
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "Status updated for {$updatedCount} clients.");
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        $converted = $client->converted;
        $type = $converted ? "Client" : "Lead";

        $client->delete();

        return redirect()
            ->route("admin.clients.index", [
                "converted" => $converted ? "true" : "false",
            ])
            ->with("success", "{$type} deleted successfully.");
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        // Check permission for importing leads
        if (
            request("converted") === "false" &&
            !auth()->user()->can("clients.view.leads")
        ) {
            abort(403, "You don't have permission to import leads.");
        }

        return view("admin.clients.import");
    }

    /**
     * Process the Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            "file" => "required|mimes:xlsx,xls,csv|max:2048",
            "converted" => "nullable|string|in:true,false",
        ]);

        try {
            $clientCountBefore = Client::count();
            $file = $request->file("file");
            $import = new ClientsImport(
                $file->getClientOriginalName(),
                $request->input("converted", "true") === "true",
            );
            Excel::queueImport($import, $request->file("file"));

            $clientCountAfter = Client::count();
            $successCount = $clientCountAfter - $clientCountBefore;

            $errorCount = 0;
            $errors = [];

            // Process any failures
            foreach ($import->failures() as $failure) {
                $errorCount++;
                $errors[] = [
                    "row" => $failure->row(),
                    "attribute" => $failure->attribute(),
                    "errors" => $failure->errors(),
                    "values" => $failure->values(),
                ];
            }

            if ($errorCount > 0) {
                return redirect()
                    ->route("admin.clients.index", [
                        "converted" => $request->input("converted", "true"),
                    ])
                    ->with(
                        "warning",
                        "Import completed with some issues. Successfully imported: {$successCount}, Failed: {$errorCount}",
                    )
                    ->with("import_errors", $errors);
            }

            if ($successCount === 0) {
                return redirect()
                    ->route("admin.clients.index", [
                        "converted" => $request->input("converted", "true"),
                    ])
                    ->with("success", "Import started");
            }

            $type =
                $request->input("converted") === "false" ? "leads" : "clients";
            return redirect()
                ->route("admin.clients.index", [
                    "converted" => $request->input("converted", "true"),
                ])
                ->with(
                    "success",
                    "Successfully imported {$successCount} {$type} from {$file->getClientOriginalName()}",
                );
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.clients.index", [
                    "converted" => $request->input("converted", "true"),
                ])
                ->with("error", "Import failed: " . $e->getMessage());
        }
    }

    /**
     * Download sample Excel template
     */
    public function downloadTemplate()
    {
        $headers = ["name", "email", "phone", "company", "address", "status"];

        $sampleData = [
            [
                "name" => "John Doe",
                "email" => "john.doe@example.com",
                "phone" => "+1 (555) 123-4567",
                "company" => "Tech Solutions Inc.",
                "address" => "123 Main St, New York, NY 10001",
                "status" => "active",
            ],
            [
                "name" => "Jane Smith",
                "email" => "jane.smith@example.com",
                "phone" => "+1 (555) 987-6543",
                "company" => "Design Studio LLC",
                "address" => "456 Oak Ave, Los Angeles, CA 90210",
                "status" => "active",
            ],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $column = "A";
        foreach ($headers as $header) {
            $sheet->setCellValue($column . "1", ucfirst($header));
            $sheet
                ->getStyle($column . "1")
                ->getFont()
                ->setBold(true);
            $column++;
        }

        // Add sample data
        $row = 2;
        foreach ($sampleData as $data) {
            $column = "A";
            foreach ($headers as $header) {
                $sheet->setCellValue($column . $row, $data[$header]);
                $column++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range("A", "F") as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = "clients_import_template.xlsx";
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()
            ->download($tempFile, $fileName)
            ->deleteFileAfterSend(true);
    }

    /**
     * Check email validity for all clients
     */
    public function checkEmailValidity(Request $request)
    {
        try {
            // Get the chunk size from request or use default
            $chunkSize = $request->get("chunk_size", 100);

            // Validate chunk size
            if ($chunkSize < 1 || $chunkSize > 1000) {
                $chunkSize = 100;
            }

            // Count total clients with invalid emails
            $totalInvalidClients = Client::where("is_email_valid", false)
                ->whereNotNull("email")
                ->where("email", "!=", "")
                ->count();

            if ($totalInvalidClients === 0) {
                return redirect()
                    ->route("admin.clients.index")
                    ->with("info", "No clients with invalid emails found.");
            }

            // Dispatch the first job to start the chain
            CheckClientEmailValidityJob::dispatch(1, $chunkSize);

            return redirect()
                ->route("admin.leads.index")
                ->with(
                    "success",
                    "Email validation job started. Processing {$totalInvalidClients} clients in chunks of {$chunkSize}. Check logs for progress.",
                );
        } catch (\Exception $e) {
            Log::error("Failed to start email validation job", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route("admin.clients.index")
                ->with(
                    "error",
                    "Failed to start email validation. Please try again.",
                );
        }
    }

    /**
     * Bulk assign clients to a user.
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            "client_ids" => "required|string",
            "user_id" => "required|exists:users,id",
        ]);

        try {
            $clientIds = json_decode($validated["client_ids"], true);

            if (!is_array($clientIds)) {
                return redirect()
                    ->back()
                    ->with("error", "Invalid client selection.");
            }

            $updatedCount = Client::whereIn("id", $clientIds)->update([
                "user_id" => $validated["user_id"],
            ]);

            return redirect()
                ->back()
                ->with(
                    "success",
                    "Assigned {$updatedCount} clients to user successfully.",
                );
        } catch (\Exception $e) {
            Log::error("Failed to bulk assign clients", [
                "error" => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with("error", "Failed to assign clients. Please try again.");
        }
    }

    /**
     * Bulk delete clients.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            "client_ids" => "required|string",
        ]);

        try {
            $clientIds = json_decode($validated["client_ids"], true);

            if (!is_array($clientIds)) {
                return redirect()
                    ->back()
                    ->with("error", "Invalid client selection.");
            }

            $deletedCount = Client::whereIn("id", $clientIds)->delete();

            return redirect()
                ->back()
                ->with(
                    "success",
                    "Deleted {$deletedCount} clients successfully.",
                );
        } catch (\Exception $e) {
            Log::error("Failed to bulk delete clients", [
                "error" => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with("error", "Failed to delete clients. Please try again.");
        }
    }

    /**
     * Bulk make client - sets converted to true for selected clients.
     */
    public function bulkMakeClient(Request $request)
    {
        $validated = $request->validate([
            "client_ids" => "required|string",
        ]);

        try {
            $clientIds = json_decode($validated["client_ids"], true);

            if (!is_array($clientIds)) {
                return redirect()
                    ->back()
                    ->with("error", "Invalid client selection.");
            }

            $updatedCount = Client::whereIn("id", $clientIds)->update([
                "converted" => true,
            ]);

            return redirect()
                ->back()
                ->with(
                    "success",
                    "Successfully marked {$updatedCount} clients as converted.",
                );
        } catch (\Exception $e) {
            Log::error("Failed to bulk update clients", [
                "error" => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with("error", "Failed to update clients. Please try again.");
        }
    }

    public function sendHtmlEmail(Request $request)
    {
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

            $query = Client::with("status", "user", "comments.user")->where(
                "is_email_valid",
                "=",
                true,
            );

            // Filter by status
            if ($request->filled("status_id")) {
                $query = $query->where("status_id", $request->status_id);
            }

            if ($request->has("converted")) {
                // Check permission for viewing leads
                $query = $query->where(
                    "converted",
                    $request->converted === "true",
                );
            } else {
                $query = $query->where("converted", true);
            }

            // Filter by user
            if ($request->filled("user_id")) {
                $query = $query->where("user_id", $request->user_id);
            }

            // Search functionality
            if ($request->filled("search")) {
                $search = $request->search;
                $query = $query->where(function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%")
                        ->orWhere("company", "like", "%{$search}%");
                });
            }

            $query->chunk(100, function ($clients) use (
                $request,
                $template,
                $proxy,
                &$clientCount,
            ) {
                foreach ($clients as $client) {
                    // Create the mail instance
                    $mail = new HtmlMail($client, $template);

                    // If proxy is selected, store it for use in mail sending
                    if ($proxy) {
                        // You can add proxy information to the mail class or handle it in the queue job
                        $mail->with(["proxy" => $proxy]);
                    }
                    $sended = Mail::to($client->email)->send($mail);
                    $clientCount++;
                }
            });

            $proxyMessage = $proxy ? " using proxy: {$proxy->name}" : "";
            return back()->with("success", "Email queued successfully");
        } catch (\Exception $e) {
            dd($e);
            return back()->withErrors([
                "email_send_error" =>
                    "Failed to send email: " . $e->getMessage(),
            ]);
        }
    }
}
