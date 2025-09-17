<?php

namespace App\Services\Mail\Providers;

use App\Models\Client;
use App\Services\Mail\AbstractMailProvider;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SendGridProvider extends AbstractMailProvider
{
    /**
     * SendGrid API endpoint.
     */
    private const API_ENDPOINT = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'sendgrid';
    }

    /**
     * Validate provider configuration.
     *
     * @return bool
     * @throws Exception
     */
    public function validateConfig(): bool
    {
        $requiredKeys = ['api_key'];
        return $this->validateRequiredConfig($requiredKeys);
    }

    /**
     * Perform the actual email sending via SendGrid API.
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

            // Prepare SendGrid payload
            $payload = [
                'personalizations' => [
                    [
                        'to' => [
                            [
                                'email' => $client->email,
                                'name' => $client->name
                            ]
                        ],
                        'subject' => $subject
                    ]
                ],
                'from' => [
                    'email' => $this->getConfigValue('from_email', config('mail.from.address')),
                    'name' => $this->getConfigValue('from_name', config('mail.from.name'))
                ],
                'content' => []
            ];

            // Add HTML content if available
            if ($htmlContent) {
                $payload['content'][] = [
                    'type' => 'text/html',
                    'value' => $htmlContent
                ];
            }

            // Add plain text content if available
            if ($textContent) {
                $payload['content'][] = [
                    'type' => 'text/plain',
                    'value' => $textContent
                ];
            }

            // If no content, add a default plain text
            if (empty($payload['content'])) {
                $payload['content'][] = [
                    'type' => 'text/plain',
                    'value' => 'This email was sent via SendGrid.'
                ];
            }

            // Add custom headers if specified
            if ($this->hasConfigValue('custom_headers')) {
                $payload['headers'] = $this->getConfigValue('custom_headers');
            }

            // Add tracking settings if specified
            if ($this->hasConfigValue('tracking_settings')) {
                $payload['tracking_settings'] = $this->getConfigValue('tracking_settings');
            }

            // Send the email via SendGrid API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getConfigValue('api_key'),
                'Content-Type' => 'application/json'
            ])->post(self::API_ENDPOINT, $payload);

            if ($response->successful()) {
                Log::info('SendGrid email sent successfully', [
                    'client_id' => $client->id,
                    'client_email' => $client->email,
                    'response_status' => $response->status(),
                    'message_id' => $response->header('X-Message-Id')
                ]);
                return true;
            } else {
                $errorBody = $response->json();
                throw new Exception(
                    "SendGrid API error: " . ($errorBody['errors'][0]['message'] ?? 'Unknown error') .
                    " (Status: {$response->status()})"
                );
            }

        } catch (Exception $e) {
            throw new Exception("SendGrid sending failed: " . $e->getMessage(), 0, $e);
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
        return 'SendGrid';
    }

    /**
     * Get default configuration for SendGrid provider.
     *
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'api_key' => env('SENDGRID_API_KEY'),
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
            'tracking_settings' => [
                'click_tracking' => [
                    'enable' => true
                ],
                'open_tracking' => [
                    'enable' => true
                ]
            ]
        ];
    }

    /**
     * Test the SendGrid API connection.
     *
     * @return bool
     * @throws Exception
     */
    public function testConnection(): bool
    {
        try {
            $this->validateConfig();

            // Test API key by making a simple API call
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getConfigValue('api_key'),
            ])->get('https://api.sendgrid.com/v3/user/account');

            if ($response->successful()) {
                return true;
            } else {
                throw new Exception("SendGrid API test failed with status: {$response->status()}");
            }

        } catch (Exception $e) {
            throw new Exception("SendGrid connection test failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get SendGrid account information.
     *
     * @return array
     * @throws Exception
     */
    public function getAccountInfo(): array
    {
        try {
            $this->validateConfig();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getConfigValue('api_key'),
            ])->get('https://api.sendgrid.com/v3/user/account');

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception("Failed to get SendGrid account info: {$response->status()}");
            }

        } catch (Exception $e) {
            throw new Exception("SendGrid account info retrieval failed: " . $e->getMessage(), 0, $e);
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
                $accountInfo = $this->getAccountInfo();
                $status['account_type'] = $accountInfo['type'] ?? 'unknown';
                $status['reputation'] = $accountInfo['reputation'] ?? 'unknown';
            }
        } catch (Exception $e) {
            $status['account_error'] = $e->getMessage();
        }

        return $status;
    }
}
