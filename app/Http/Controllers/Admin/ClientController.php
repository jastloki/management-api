<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Status;
use App\Models\User;
use App\Imports\ClientsImport;
use App\Jobs\CheckClientEmailValidityJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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

        $clients = $query->latest()->paginate(20)->appends($request->query());

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
        ]);

        Client::create($validated);

        return redirect()
            ->route("admin.clients.index")
            ->with("success", "Client created successfully.");
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
        ]);

        $client->update($validated);

        return redirect()
            ->route("admin.clients.index")
            ->with("success", "Client updated successfully.");
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
        $client->delete();

        return redirect()
            ->route("admin.clients.index")
            ->with("success", "Client deleted successfully.");
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view("admin.clients.import");
    }

    /**
     * Process the Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            "file" => "required|mimes:xlsx,xls,csv|max:2048",
        ]);

        try {
            $clientCountBefore = Client::count();
            $file = $request->file("file");
            $import = new ClientsImport($file->getClientOriginalName());
            Excel::import($import, $request->file("file"));

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
                    ->route("admin.clients.index")
                    ->with(
                        "warning",
                        "Import completed with some issues. Successfully imported: {$successCount}, Failed: {$errorCount}",
                    )
                    ->with("import_errors", $errors);
            }

            if ($successCount === 0) {
                return redirect()
                    ->route("admin.clients.index")
                    ->with(
                        "warning",
                        "No new clients were imported. Please check your file format.",
                    );
            }

            return redirect()
                ->route("admin.clients.index")
                ->with(
                    "success",
                    "Successfully imported {$successCount} clients.",
                );
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.clients.index")
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
                ->route("admin.clients.index")
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
}
