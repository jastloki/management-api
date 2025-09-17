<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatusController extends Controller
{
    /**
     * Display a listing of the statuses.
     */
    public function index()
    {
        $statuses = Status::orderBy("name")->paginate(15);

        return view("admin.statuses.index", compact("statuses"));
    }

    /**
     * Show the form for creating a new status.
     */
    public function create()
    {
        return view("admin.statuses.create");
    }

    /**
     * Store a newly created status in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255|unique:statuses,name",
            "description" => "nullable|string|max:1000",
        ]);

        $status = Status::create($validated);

        return redirect()
            ->route("admin.statuses.index")
            ->with("success", "Status created successfully.");
    }

    /**
     * Display the specified status.
     */
    public function show(Status $status)
    {
        return view("admin.statuses.show", compact("status"));
    }

    /**
     * Show the form for editing the specified status.
     */
    public function edit(Status $status)
    {
        return view("admin.statuses.edit", compact("status"));
    }

    /**
     * Update the specified status in storage.
     */
    public function update(Request $request, Status $status)
    {
        $validated = $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("statuses", "name")->ignore($status->id),
            ],
            "description" => "nullable|string|max:1000",
        ]);

        $status->update($validated);

        return redirect()
            ->route("admin.statuses.index")
            ->with("success", "Status updated successfully.");
    }

    /**
     * Remove the specified status from storage.
     */
    public function destroy(Status $status)
    {
        try {
            $status->delete();

            return redirect()
                ->route("admin.statuses.index")
                ->with("success", "Status deleted successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.statuses.index")
                ->with(
                    "error",
                    "Unable to delete status. It may be in use by other records.",
                );
        }
    }

    /**
     * Get all statuses for API use.
     */
    public function getStatuses()
    {
        $statuses = Status::orderBy("name")->get();

        return response()->json([
            "success" => true,
            "data" => $statuses,
        ]);
    }
}
