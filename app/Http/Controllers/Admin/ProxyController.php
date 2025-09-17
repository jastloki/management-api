<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proxy;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

class ProxyController extends Controller
{
    /**
     * Display a listing of the proxies.
     */
    public function index(): View
    {
        $proxies = Proxy::orderBy("name")->paginate(15);

        return view("admin.proxies.index", compact("proxies"));
    }

    /**
     * Show the form for creating a new proxy.
     */
    public function create(): View
    {
        return view("admin.proxies.create");
    }

    /**
     * Store a newly created proxy in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => "required|string|max:255|unique:proxies,name",
            "url" => "required|string|max:255",
            "type" => [
                "required",
                "string",
                Rule::in(array_keys(Proxy::getTypes())),
            ],
            "port" => "nullable|integer|min:1|max:65535",
            "username" => "nullable|string|max:255",
            "password" => "nullable|string|max:255",
            "country" => "nullable|string|max:100",
            "city" => "nullable|string|max:100",
            "description" => "nullable|string|max:1000",
            "is_active" => "boolean",
            "extra_fields" => "nullable|array",
            "extra_fields.*" => "nullable|string",
        ]);

        // Extra fields are already in the correct format from the form
        if (empty($validated["extra_fields"])) {
            $validated["extra_fields"] = null;
        }

        Proxy::create($validated);

        return redirect()
            ->route("admin.proxies.index")
            ->with("success", "Proxy created successfully.");
    }

    /**
     * Display the specified proxy.
     */
    public function show(Proxy $proxy): View
    {
        return view("admin.proxies.show", [
            "proxy" => $proxy,
        ]);
    }

    /**
     * Show the form for editing the specified proxy.
     */
    public function edit(Proxy $proxy): View
    {
        return view("admin.proxies.edit", [
            "proxy" => $proxy,
        ]);
    }

    /**
     * Update the specified proxy in storage.
     */
    public function update(Request $request, Proxy $proxy): RedirectResponse
    {
        $validated = $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("proxies", "name")->ignore($proxy->id),
            ],
            "url" => "required|string|max:255",
            "type" => [
                "required",
                "string",
                Rule::in(array_keys(Proxy::getTypes())),
            ],
            "port" => "nullable|integer|min:1|max:65535",
            "username" => "nullable|string|max:255",
            "password" => "nullable|string|max:255",
            "country" => "nullable|string|max:100",
            "city" => "nullable|string|max:100",
            "description" => "nullable|string|max:1000",
            "is_active" => "boolean",
            "extra_fields" => "nullable|array",
            "extra_fields.*" => "nullable|string",
        ]);

        // Extra fields are already in the correct format from the form
        if (empty($validated["extra_fields"])) {
            $validated["extra_fields"] = null;
        }

        $proxy->update($validated);

        return redirect()
            ->route("admin.proxies.index")
            ->with("success", "Proxy updated successfully.");
    }

    /**
     * Remove the specified proxy from storage.
     */
    public function destroy(Proxy $proxy): RedirectResponse
    {
        try {
            $proxy->delete();

            return redirect()
                ->route("admin.proxies.index")
                ->with("success", "Proxy deleted successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->route("admin.proxies.index")
                ->with("error", "Unable to delete proxy. It may be in use.");
        }
    }

    /**
     * Toggle the active status of a proxy.
     */
    public function toggleStatus(Proxy $proxy): RedirectResponse
    {
        $proxy->is_active = !$proxy->is_active;
        $proxy->save();

        $status = $proxy->is_active ? "activated" : "deactivated";

        return redirect()
            ->route("admin.proxies.index")
            ->with("success", "Proxy {$status} successfully.");
    }

    /**
     * Test a proxy connection.
     */
    public function test(Proxy $proxy): JsonResponse
    {
        // Simple test implementation - you can enhance this
        $startTime = microtime(true);

        try {
            // Basic test with a simple HTTP request
            $context = stream_context_create([
                "http" => [
                    "proxy" => $proxy->full_url,
                    "timeout" => 10,
                ],
            ]);

            $result = @file_get_contents(
                "http://httpbin.org/ip",
                false,
                $context,
            );
            $responseTime = round((microtime(true) - $startTime) * 1000);

            if ($result !== false) {
                $proxy->update([
                    "status" => "working",
                    "response_time" => $responseTime,
                    "last_tested_at" => now(),
                ]);

                return response()->json([
                    "success" => true,
                    "message" => "Proxy is working! Response time: {$responseTime}ms",
                    "response_time" => $responseTime,
                ]);
            }
        } catch (\Exception $e) {
            // Test failed
        }

        $proxy->update([
            "status" => "failed",
            "last_tested_at" => now(),
        ]);

        return response()->json([
            "success" => false,
            "message" => "Proxy test failed",
        ]);
    }

    /**
     * Duplicate a proxy.
     */
    public function duplicate(Proxy $proxy): RedirectResponse
    {
        $newProxy = $proxy->replicate();
        $newProxy->name = $proxy->name . " (Copy)";
        $newProxy->is_active = false;
        $newProxy->status = "untested";
        $newProxy->last_tested_at = null;
        $newProxy->response_time = null;
        $newProxy->save();

        return redirect()
            ->route("admin.proxies.edit", $newProxy)
            ->with("success", "Proxy duplicated successfully.");
    }

    /**
     * Export proxies in various formats.
     */
    public function export(Request $request): Response
    {
        $format = $request->get("format", "txt");
        $proxies = Proxy::where("is_active", true)
            ->where("status", "working")
            ->get();

        $content = "";
        $filename = "proxies_" . date("Y-m-d");

        switch ($format) {
            case "json":
                $content = json_encode(
                    $proxies->map(function ($proxy) {
                        return [
                            "name" => $proxy->name,
                            "url" => $proxy->url,
                            "port" => $proxy->port,
                            "type" => $proxy->type,
                            "username" => $proxy->username,
                            "password" => $proxy->password,
                        ];
                    }),
                    JSON_PRETTY_PRINT,
                );
                $filename .= ".json";
                $mimeType = "application/json";
                break;

            case "csv":
                $content =
                    "Name,URL,Port,Type,Username,Password,Country,City\n";
                foreach ($proxies as $proxy) {
                    $content .=
                        implode(",", [
                            $proxy->name,
                            $proxy->url,
                            $proxy->port ?? "",
                            $proxy->type,
                            $proxy->username ?? "",
                            $proxy->password ?? "",
                            $proxy->country ?? "",
                            $proxy->city ?? "",
                        ]) . "\n";
                }
                $filename .= ".csv";
                $mimeType = "text/csv";
                break;

            default:
                // txt
                foreach ($proxies as $proxy) {
                    $line = $proxy->url;
                    if ($proxy->port) {
                        $line .= ":" . $proxy->port;
                    }
                    if ($proxy->username && $proxy->password) {
                        $line =
                            $proxy->username .
                            ":" .
                            $proxy->password .
                            "@" .
                            $line;
                    }
                    $content .= $line . "\n";
                }
                $filename .= ".txt";
                $mimeType = "text/plain";
                break;
        }

        return response($content)
            ->header("Content-Type", $mimeType)
            ->header(
                "Content-Disposition",
                'attachment; filename="' . $filename . '"',
            );
    }

    /**
     * Bulk test selected proxies.
     */
    public function bulkTest(Request $request): RedirectResponse
    {
        $proxyIds = $request->input("proxy_ids", []);
        $tested = 0;
        $working = 0;

        foreach ($proxyIds as $id) {
            $proxy = Proxy::where("id", $id)->first();
            if ($proxy) {
                // Simple test logic (you can enhance this)
                $startTime = microtime(true);

                try {
                    $context = stream_context_create([
                        "http" => [
                            "proxy" => $proxy->full_url,
                            "timeout" => 5,
                        ],
                    ]);

                    $result = @file_get_contents(
                        "http://httpbin.org/ip",
                        false,
                        $context,
                    );
                    $responseTime = round(
                        (microtime(true) - $startTime) * 1000,
                    );

                    if ($result !== false) {
                        $proxy->update([
                            "status" => "working",
                            "response_time" => $responseTime,
                            "last_tested_at" => now(),
                        ]);
                        $working++;
                    } else {
                        $proxy->update([
                            "status" => "failed",
                            "last_tested_at" => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    $proxy->update([
                        "status" => "failed",
                        "last_tested_at" => now(),
                    ]);
                }

                $tested++;
            }
        }

        return redirect()
            ->route("admin.proxies.index")
            ->with(
                "success",
                "Tested {$tested} proxies. {$working} are working.",
            );
    }
}
