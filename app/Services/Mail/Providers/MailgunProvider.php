<?php

namespace App\Services\Mail\Providers;

use App\Models\Client;
use App\Services\Mail\AbstractMailProvider;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MailgunProvider extends AbstractMailProvider
{
    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'mailgun';
    }

    /**
     * Validate provider configuration.
     *
     * @return bool
     * @throws Exception
     */
    public function validateConfig(): bool
    {
        $requiredKeys = ['api_key', 'domain'];
        return $this->validateRequiredConfig($requiredKeys);
    }

    /**
     * Perform the actual email sending via Mailgun API.
     *
     * @param Client $client
     * @param Mailable $mailable
     * @return bool
     * @throws Exception
     */
    protected function doSend(Client $client, Mailable $mailable): bool
    {
        try {
            // Build the mailable to get rendered content
            $renderedMailable = $mailable->build();

            // Get email content
            $subject = $renderedMailable->subject ?? 'No Subject';
            $htmlContent = $this->getHtmlContent($renderedMailable);
            $textContent = $this->getTextContent($renderedMailable);

            // Get Mailgun API endpoint
            $domain = $this->getConfigValue('domain');
            $region = $this->getConfigValue('region', 'us');
            $baseUrl = $region === 'eu' ? 'https://api.eu.mailgun.net' : 'https://api.mailgun.net';
            $endpoint = "{$baseUrl}/v3/{$domain}/messages";

            // Prepare Mailgun payload
            $payload = [
                'from' => $this->getConfigValue('from_email', config('mail.from.address')),
                'to' => $client->email,
                'subject' => $subject,
            ];

            // Add recipient name if available
            if (!empty($client->name)) {
                $payload['to'] = "{$client->name} <{$client->email}>";
            }

            // Add HTML content if available
            if ($htmlContent) {
                $payload['html'] = $htmlContent;
            }

            // Add plain text content if available
            if ($textContent) {
                $payload['text'] = $textContent;
            }

            // If no content, add a default plain text
            if (!$htmlContent && !$textContent) {
                $payload['text'] = 'This email was sent via Mailgun.';
            }

            // Add tracking options
            if ($this->getConfigValue('track_clicks', true)) {
                $payload['o:tracking-clicks'] = 'yes';
            }

            if ($this->getConfigValue('track_opens', true)) {
                $payload['o:tracking-opens'] = 'yes';
            }

            // Add custom headers if specified
            if ($this->hasConfigValue('custom_headers')) {
                foreach ($this->getConfigValue('custom_headers') as $key => $value) {
                    $payload["h:{$key}"] = $value;
                }
            }

            // Add tags if specified
            if ($this->hasConfigValue('tags')) {
                $tags = $this->getConfigValue('tags');
                if (is_array($tags)) {
                    foreach ($tags as $tag) {
                        $payload['o:tag'][] = $tag;
                    }
                } else {
                    $payload['o:tag'] = $tags;
                }
            }

            // Send the email via Mailgun API
            $response = Http::withBasicAuth('api', $this->getConfigValue('api_key'))
                ->asForm()
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Mailgun email sent successfully', [
                    'client_id' => $client->id,
                    'client_email' => $client->email,
                    'response_status' => $response->status(),
                    'message_id' => $responseData['id'] ?? null,
                    'message' => $responseData['message'] ?? null
                ]);
                return true;
            } else {
                $errorBody = $response->json();
                throw new Exception(
                    "Mailgun API error: " . ($errorBody['message'] ?? 'Unknown error') .
                    " (Status: {$response->status()})"
                );
            }

        } catch (Exception $e) {
            throw new Exception("Mailgun sending failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get HTML content from the mailable.
     *
     * @param Mailable $mailable
     * @return string|null
     */
    private function getHtmlContent(Mailable $mailable): ?string
    {
        try {
            if (method_exists($mailable, 'render')) {
                return $mailable->render();
            }
            return null;
        } catch (Exception $e) {
            Log::warning('Failed to get HTML content from mailable', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get plain text content from the mailable.
     *
     * @param Mailable $mailable
     * @return string|null
     */
    private function getTextContent(Mailable $mailable): ?string
    {
        try {
            // Try to get text view if it exists
            if (isset($mailable->textView)) {
                return view($mailable->textView, $mailable->buildViewData())->render();
            }

            // If no text view, strip HTML tags from HTML content
            $htmlContent = $this->getHtmlContent($mailable);
            if ($htmlContent) {
                return strip_tags($htmlContent);
            }

            return null;
        } catch (Exception $e) {
            Log::warning('Failed to get text content from mailable', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get provider display name.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Mailgun';
    }

    /**
     * Get default configuration for Mailgun provider.
     *
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'api_key' => env('MAILGUN_SECRET'),
            'domain' => env('MAILGUN_DOMAIN'),
            'region' => env('MAILGUN_REGION', 'us'),
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
            'track_clicks' => true,
            'track_opens' => true,
            'tags' => ['laravel-sender']
        ];
    }

    /**
     * Test the Mailgun API connection.
     *
     * @return bool
     * @throws Exception
     */
    public function testConnection(): bool
    {
        try {
            $this->validateConfig();

            $domain = $this->getConfigValue('domain');
            $region = $this->getConfigValue('region', 'us');
            $baseUrl = $region === 'eu' ? 'https://api.eu.mailgun.net' : 'https://api.mailgun.net';
            $endpoint = "{$baseUrl}/v3/{$domain}";

            // Test API key by getting domain information
            $response = Http::withBasicAuth('api', $this->getConfigValue('api_key'))
                ->get($endpoint);

            if ($response->successful()) {
                return true;
            } else {
                throw new Exception("Mailgun API test failed with status: {$response->status()}");
            }

        } catch (Exception $e) {
            throw new Exception("Mailgun connection test failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get Mailgun domain information.
     *
     * @return array
     * @throws Exception
     */
    public function getDomainInfo(): array
    {
        try {
            $this->validateConfig();

            $domain = $this->getConfigValue('domain');
            $region = $this->getConfigValue('region', 'us');
            $baseUrl = $region === 'eu' ? 'https://api.eu.mailgun.net' : 'https://api.mailgun.net';
            $endpoint = "{$baseUrl}/v3/{$domain}";

            $response = Http::withBasicAuth('api', $this->getConfigValue('api_key'))
                ->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception("Failed to get Mailgun domain info: {$response->status()}");
            }

        } catch (Exception $e) {
            throw new Exception("Mailgun domain info retrieval failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get provider-specific status information.
     *
     * @return array
     */
    public function getStatus(): array
    {
        $status = parent::getStatus();

        try {
            if ($this->isAvailable()) {
                $domainInfo = $this->getDomainInfo();
                $status['domain_state'] = $domainInfo['domain']['state'] ?? 'unknown';
                $status['domain_type'] = $domainInfo['domain']['type'] ?? 'unknown';
            }
        } catch (Exception $e) {
            $status['domain_error'] = $e->getMessage();
        }

        return $status;
    }
}
