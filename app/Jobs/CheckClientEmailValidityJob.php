<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\EmailValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckClientEmailValidityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The chunk size for processing clients.
     *
     * @var int
     */
    protected $chunkSize;

    /**
     * The page number for pagination.
     *
     * @var int
     */
    protected $page;

    /**
     * The email validation service instance.
     *
     * @var EmailValidationService
     */
    protected $emailValidator;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param int $page The page number for pagination
     * @param int $chunkSize The number of clients to process per chunk
     * @return void
     */
    public function __construct(int $page = 1, int $chunkSize = 25)
    {
        $this->page = $page;
        $this->chunkSize = $chunkSize;
        $this->emailValidator = new EmailValidationService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info("Starting email validity job", [
                "page" => $this->page,
                "chunk_size" => $this->chunkSize,
                "attempt" => $this->attempts(),
            ]);

            // Calculate offset for pagination
            $offset = ($this->page - 1) * $this->chunkSize;
            $lastProcessedId =
                $this->page > 1 ? $this->getLastProcessedId() : 0;
            // Fetch clients with invalid emails in chunks
            $clients = Client::where("is_email_valid", false)
                ->whereNotNull("email")
                ->where("email", "!=", "")
                ->offset($offset)
                ->limit($this->chunkSize)
                ->get();

            if ($clients->isEmpty()) {
                Log::info("No more clients to process for email validity", [
                    "page" => $this->page,
                    "chunk_size" => $this->chunkSize,
                ]);
                return;
            }

            Log::info("Processing clients for email validity", [
                "page" => $this->page,
                "chunk_size" => $this->chunkSize,
                "clients_found" => $clients->count(),
            ]);

            $processedCount = 0;
            $validCount = 0;
            $invalidCount = 0;

            foreach ($clients as $client) {
                try {
                    $validationResult = $this->emailValidator->validateEmail(
                        $client->email,
                    );
                    $isValid = $validationResult["is_valid"];

                    // Update client email validity status with detailed information
                    $client->update([
                        "is_email_valid" => $isValid,
                        "email_status" => $isValid ? "valid" : "invalid",
                        "email_validation_reason" =>
                            $validationResult["reason"],
                        "email_validation_details" =>
                            $validationResult["checks"],
                        "email_last_validated_at" => now(),
                        "email_validation_attempts" =>
                            $client->email_validation_attempts + 1,
                    ]);

                    $processedCount++;
                    if ($isValid) {
                        $validCount++;
                    } else {
                        $invalidCount++;
                    }

                    // Log::debug("Email validation result", [
                    //     "client_id" => $client->id,
                    //     "email" => $client->email,
                    //     "is_valid" => $isValid,
                    //     "reason" => $validationResult["reason"],
                    //     "checks" => $validationResult["checks"],
                    // ]);
                } catch (Exception $e) {
                    Log::warning("Failed to validate email for client", [
                        "client_id" => $client->id,
                        "email" => $client->email,
                        "error" => $e->getMessage(),
                    ]);
                }
            }

            Log::info("Completed email validity check for chunk", [
                "page" => $this->page,
                "processed_count" => $processedCount,
                "valid_count" => $validCount,
                "invalid_count" => $invalidCount,
            ]);
            $lastId = $clients->last()?->id ?? $lastProcessedId;
            // Check if there are more clients to process
            $remainingCount = Client::where("is_email_valid", false)
                ->whereNotNull("email")
                ->where("email", "!=", "")
                ->where("id", ">", $lastId)
                ->limit(1)
                ->count();

            // Dispatch next chunk if there are more clients to process
            if ($remainingCount > 0) {
                Log::info("Dispatching next chunk for email validity check", [
                    "next_page" => $this->page + 1,
                    "chunk_size" => $this->chunkSize,
                ]);

                static::dispatch($this->page + 1, $this->chunkSize)->delay(
                    now()->addSeconds(5),
                ); // Small delay to prevent overwhelming the system
            } else {
                Log::info("Email validity check completed for all clients");
            }
        } catch (Exception $e) {
            Log::error("Email validity check job failed", [
                "page" => $this->page,
                "chunk_size" => $this->chunkSize,
                "attempt" => $this->attempts(),
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Email validity check job failed permanently", [
            "page" => $this->page,
            "chunk_size" => $this->chunkSize,
            "error" => $exception->getMessage(),
            "trace" => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Get the tags for the job.
     *
     * @return array
     */
    public function tags()
    {
        return ["email-validation", "clients", "page:" . $this->page];
    }

    protected function getLastProcessedId(): int
    {
        // For now, we'll use a simple calculation based on page and chunk size
        // In a more robust solution, you could cache the last processed ID
        return ($this->page - 1) * $this->chunkSize;
    }
}
