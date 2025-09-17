<?php

namespace App\Services\Mail\Providers;

use App\Models\Client;
use App\Services\Mail\AbstractMailProvider;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Exception;

class SmtpProvider extends AbstractMailProvider
{
    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string
    {
        return "smtp";
    }

    /**
     * Validate provider configuration.
     *
     * @return bool
     * @throws Exception
     */
    public function validateConfig(): bool
    {
        $requiredKeys = ["host", "port", "username", "password"];
        return $this->validateRequiredConfig($requiredKeys);
    }

    /**
     * Perform the actual email sending via SMTP.
     *
     * @param Client $client
     * @param Mailable $mailable
     * @return bool
     * @throws Exception
     */
    protected function doSend(Client $client, Mailable $mailable): bool
    {
        try {
            // Configure SMTP mailer on the fly
            $mailerConfig = [
                "transport" => "smtp",
                "host" => $this->getConfigValue("host"),
                "port" => $this->getConfigValue("port"),
                "username" => $this->getConfigValue("username"),
                "password" => $this->getConfigValue("password"),
                "encryption" => $this->getConfigValue("encryption", "tls"),
                "timeout" => $this->getConfigValue("timeout", 60),
                "local_domain" => $this->getConfigValue("local_domain"),
            ];

            // Create a custom mailer configuration
            config(["mail.mailers.smtp_custom" => $mailerConfig]);

            // Send email using the custom SMTP configuration
            Mail::mailer("smtp_custom")->to($client->email)->send($mailable);

            return true;
        } catch (Exception $e) {
            throw new Exception(
                "SMTP sending failed: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * Get provider display name.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return "SMTP";
    }

    /**
     * Get default configuration for SMTP provider.
     *
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            "host" => env("MAIL_HOST", "127.0.0.1"),
            "port" => env("MAIL_PORT", 587),
            "username" => env("MAIL_USERNAME"),
            "password" => env("MAIL_PASSWORD"),
            "encryption" => env("MAIL_ENCRYPTION", "tls"),
            "timeout" => 60,
            "local_domain" => env("MAIL_EHLO_DOMAIN"),
        ];
    }

    /**
     * Test the SMTP connection.
     *
     * @return bool
     * @throws Exception
     */
    public function testConnection(): bool
    {
        try {
            $this->validateConfig();

            // Create a test mailer configuration
            $mailerConfig = [
                "transport" => "smtp",
                "host" => $this->getConfigValue("host"),
                "port" => $this->getConfigValue("port"),
                "username" => $this->getConfigValue("username"),
                "password" => $this->getConfigValue("password"),
                "encryption" => $this->getConfigValue("encryption", "tls"),
                "timeout" => $this->getConfigValue("timeout", 60),
            ];

            config(["mail.mailers.smtp_test" => $mailerConfig]);

            // Try to create a test connection by attempting to get the mailer
            $mailer = Mail::mailer("smtp_test");

            // If we can create the mailer without exception, consider it successful
            return $mailer !== null;
        } catch (Exception $e) {
            throw new Exception(
                "SMTP connection test failed: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }
}
