<?php

namespace App\Services\Mail;

use App\Models\Client;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class AbstractMailProvider implements MailProviderInterface
{
    /**
     * The provider configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * Create a new mail provider instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Send an email using the provider.
     *
     * @param Client $client
     * @param Mailable $mailable
     * @return bool
     * @throws Exception
     */
    public function send(Client $client, Mailable $mailable): bool
    {
        try {
            $this->validateConfig();

            Log::info("Attempting to send email via {$this->getName()}", [
                "provider" => $this->getName(),
                "client_id" => $client->id,
                "client_email" => $client->email,
            ]);

            $result = $this->doSend($client, $mailable);

            if ($result) {
                Log::info("Email sent successfully via {$this->getName()}", [
                    "provider" => $this->getName(),
                    "client_id" => $client->id,
                    "client_email" => $client->email,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Failed to send email via {$this->getName()}", [
                "provider" => $this->getName(),
                "client_id" => $client->id,
                "client_email" => $client->email,
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Perform the actual email sending.
     * This method should be implemented by concrete providers.
     *
     * @param Client $client
     * @param Mailable $mailable
     * @return bool
     * @throws Exception
     */
    abstract protected function doSend(
        Client $client,
        Mailable $mailable,
    ): bool;

    /**
     * Get provider-specific configuration.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check if the provider is configured and available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            return $this->validateConfig();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get a configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set a configuration value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function setConfigValue(string $key, $value): void
    {
        data_set($this->config, $key, $value);
    }

    /**
     * Check if a configuration key exists and is not empty.
     *
     * @param string $key
     * @return bool
     */
    protected function hasConfigValue(string $key): bool
    {
        $value = $this->getConfigValue($key);
        return !empty($value);
    }

    /**
     * Validate required configuration keys.
     *
     * @param array $requiredKeys
     * @return bool
     * @throws Exception
     */
    protected function validateRequiredConfig(array $requiredKeys): bool
    {
        foreach ($requiredKeys as $key) {
            if (!$this->hasConfigValue($key)) {
                throw new Exception(
                    "Missing required configuration key: {$key} for provider {$this->getName()}",
                );
            }
        }

        return true;
    }

    /**
     * Get the provider display name for logging and UI.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return ucfirst($this->getName());
    }

    /**
     * Get provider statistics or health information.
     *
     * @return array
     */
    public function getStatus(): array
    {
        return [
            "name" => $this->getName(),
            "display_name" => $this->getDisplayName(),
            "available" => $this->isAvailable(),
            "config_valid" => $this->validateConfig(),
        ];
    }

    /**
     * Test the provider connection.
     * Default implementation just checks if the provider is available.
     * Concrete providers can override this for more specific testing.
     *
     * @return bool
     * @throws Exception
     */
    public function testConnection(): bool
    {
        return $this->isAvailable();
    }
}
