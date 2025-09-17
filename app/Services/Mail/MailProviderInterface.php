<?php

namespace App\Services\Mail;

use App\Models\Client;
use Illuminate\Mail\Mailable;

interface MailProviderInterface
{
    /**
     * Send an email using the provider.
     *
     * @param Client $client
     * @param Mailable $mailable
     * @return bool
     * @throws \Exception
     */
    public function send(Client $client, Mailable $mailable): bool;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the provider is configured and available.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get provider-specific configuration.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Validate provider configuration.
     *
     * @return bool
     */
    public function validateConfig(): bool;

    /**
     * Get the provider display name.
     *
     * @return string
     */
    public function getDisplayName(): string;

    /**
     * Get the provider status information.
     *
     * @return array
     */
    public function getStatus(): array;

    /**
     * Test the provider connection (optional).
     *
     * @return bool
     * @throws \Exception
     */
    public function testConnection(): bool;
}
