<?php

namespace App\Jobs;

use App\Models\Client;
use App\Mail\ClientWelcome;
use App\Services\Mail\MailProviderFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendClientEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The client instance.
     *
     * @var \App\Models\Client
     */
    protected $client;

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
     * Create a new job instance.
     *
     * @param  \App\Models\Client  $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;

        // Ensure the client has a provider set, fallback to default if not
        if (empty($this->client->email_provider)) {
            $this->client->email_provider = config(
                "mail.default_provider",
                "smtp",
            );
            $this->client->save();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Update client status to sending
            $this->client->update([
                "email_status" => "sending",
            ]);

            // Create the mailable
            $mailable = new ClientWelcome($this->client);

            // Get the selected provider or fallback
            $provider = $this->getEmailProvider();

            Log::info("Attempting to send email via {$provider->getName()}", [
                "client_id" => $this->client->id,
                "client_email" => $this->client->email,
                "provider" => $provider->getName(),
                "attempt" => $this->attempts(),
            ]);

            // Send the email using the selected provider
            $success = $provider->send($this->client, $mailable);

            if ($success) {
                // Update client with successful email status
                $this->client->update([
                    "email_status" => "sent",
                    "email_sent_at" => now(),
                ]);

                Log::info(
                    "Email sent successfully to client: {$this->client->email}",
                    [
                        "client_id" => $this->client->id,
                        "client_name" => $this->client->name,
                        "provider" => $provider->getName(),
                    ],
                );
            } else {
                throw new Exception(
                    "Email provider returned false, indicating failure",
                );
            }
        } catch (Exception $e) {
            // Log the error with provider context
            Log::error("Email sending failed", [
                "client_id" => $this->client->id,
                "client_email" => $this->client->email,
                "provider" => $this->client->email_provider,
                "attempt" => $this->attempts(),
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            // Try fallback provider if this is not the last attempt
            if ($this->attempts() < $this->tries) {
                $this->tryFallbackProvider($e);
            }

            // Mark as failed if this was the last attempt
            if ($this->attempts() >= $this->tries) {
                $this->client->update([
                    "email_status" => "failed",
                ]);

                Log::error(
                    "Failed to send email to client after {$this->tries} attempts: {$this->client->email}",
                    [
                        "error" => $e->getMessage(),
                        "client_id" => $this->client->id,
                        "final_provider" => $this->client->email_provider,
                    ],
                );
            } else {
                $this->client->update([
                    "email_status" => "queued",
                ]);
            }

            // Re-throw the exception to trigger the retry mechanism
            throw $e;
        }
    }

    /**
     * Get the email provider for this client.
     *
     * @return \App\Services\Mail\MailProviderInterface
     * @throws Exception
     */
    private function getEmailProvider()
    {
        try {
            // Try to create provider from client's selected provider
            $provider = MailProviderFactory::makeFromConfig(
                $this->client->email_provider,
            );

            // Validate that the provider is available
            if (!$provider->isAvailable()) {
                throw new Exception(
                    "Provider {$this->client->email_provider} is not available",
                );
            }

            return $provider;
        } catch (Exception $e) {
            Log::warning("Failed to get configured provider, trying fallback", [
                "client_id" => $this->client->id,
                "configured_provider" => $this->client->email_provider,
                "error" => $e->getMessage(),
            ]);

            // Try to get the best available provider as fallback
            $fallbackProvider = MailProviderFactory::getBestAvailableProvider(
                config("mail.provider_priority", [
                    "sendgrid",
                    "mailgun",
                    "smtp",
                ]),
            );

            if (!$fallbackProvider) {
                throw new Exception("No email providers are available");
            }

            // Update client with the fallback provider
            $this->client->update([
                "email_provider" => $fallbackProvider->getName(),
            ]);

            return $fallbackProvider;
        }
    }

    /**
     * Try fallback provider on failure.
     *
     * @param Exception $originalException
     * @return void
     */
    private function tryFallbackProvider(Exception $originalException)
    {
        try {
            $priorityOrder = config("mail.provider_priority", [
                "sendgrid",
                "mailgun",
                "smtp",
            ]);
            $currentProvider = $this->client->email_provider;

            // Find next provider in priority order
            $currentIndex = array_search($currentProvider, $priorityOrder);
            if (
                $currentIndex !== false &&
                $currentIndex < count($priorityOrder) - 1
            ) {
                $nextProvider = $priorityOrder[$currentIndex + 1];

                Log::info("Trying fallback provider", [
                    "client_id" => $this->client->id,
                    "from_provider" => $currentProvider,
                    "to_provider" => $nextProvider,
                    "original_error" => $originalException->getMessage(),
                ]);

                // Update client with fallback provider
                $this->client->update([
                    "email_provider" => $nextProvider,
                ]);
            }
        } catch (Exception $e) {
            Log::warning("Failed to set fallback provider", [
                "client_id" => $this->client->id,
                "error" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // This is called when the job has failed all of its retry attempts
        $this->client->update([
            "email_status" => "failed",
        ]);

        Log::error(
            "Email job failed permanently for client: {$this->client->email}",
            [
                "error" => $exception->getMessage(),
                "client_id" => $this->client->id,
            ],
        );
    }
}
