<?php

namespace App\Services\Mail;

use App\Services\Mail\Providers\SmtpProvider;
use App\Services\Mail\Providers\SendGridProvider;
use App\Services\Mail\Providers\MailgunProvider;
use InvalidArgumentException;
use Exception;

class MailProviderFactory
{
    /**
     * Available mail providers.
     *
     * @var array
     */
    private static array $providers = [
        'smtp' => SmtpProvider::class,
        'sendgrid' => SendGridProvider::class,
        'mailgun' => MailgunProvider::class,
    ];

    /**
     * Provider configurations cache.
     *
     * @var array
     */
    private static array $configCache = [];

    /**
     * Provider instances cache.
     *
     * @var array
     */
    private static array $instanceCache = [];

    /**
     * Create a mail provider instance by name.
     *
     * @param string $providerName
     * @param array $config
     * @return MailProviderInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $providerName, array $config = []): MailProviderInterface
    {
        if (!isset(self::$providers[$providerName])) {
            throw new InvalidArgumentException("Unknown mail provider: {$providerName}");
        }

        $cacheKey = self::getCacheKey($providerName, $config);

        // Return cached instance if available
        if (isset(self::$instanceCache[$cacheKey])) {
            return self::$instanceCache[$cacheKey];
        }

        $providerClass = self::$providers[$providerName];

        // Merge with default configuration
        $finalConfig = array_merge(
            self::getDefaultConfig($providerName),
            $config
        );

        $instance = new $providerClass($finalConfig);

        // Cache the instance
        self::$instanceCache[$cacheKey] = $instance;

        return $instance;
    }

    /**
     * Create a mail provider instance from configuration key.
     *
     * @param string $providerName
     * @param string|null $configKey
     * @return MailProviderInterface
     * @throws InvalidArgumentException
     */
    public static function makeFromConfig(string $providerName, ?string $configKey = null): MailProviderInterface
    {
        $configKey = $configKey ?? "mail.providers.{$providerName}";
        $config = config($configKey, []);

        return self::make($providerName, $config);
    }

    /**
     * Get all available provider names.
     *
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Get provider information for all available providers.
     *
     * @return array
     */
    public static function getProvidersInfo(): array
    {
        $info = [];

        foreach (self::$providers as $name => $class) {
            try {
                $provider = self::make($name);
                $info[$name] = [
                    'name' => $name,
                    'display_name' => $provider->getDisplayName(),
                    'class' => $class,
                    'available' => $provider->isAvailable(),
                    'status' => $provider->getStatus(),
                ];
            } catch (Exception $e) {
                $info[$name] = [
                    'name' => $name,
                    'display_name' => ucfirst($name),
                    'class' => $class,
                    'available' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $info;
    }

    /**
     * Get available (configured) providers only.
     *
     * @return array
     */
    public static function getAvailableProvidersInfo(): array
    {
        return array_filter(
            self::getProvidersInfo(),
            fn($provider) => $provider['available']
        );
    }

    /**
     * Register a new mail provider.
     *
     * @param string $name
     * @param string $class
     * @return void
     * @throws InvalidArgumentException
     */
    public static function register(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Provider class does not exist: {$class}");
        }

        if (!is_subclass_of($class, MailProviderInterface::class)) {
            throw new InvalidArgumentException("Provider class must implement MailProviderInterface: {$class}");
        }

        self::$providers[$name] = $class;
    }

    /**
     * Check if a provider is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function isRegistered(string $name): bool
    {
        return isset(self::$providers[$name]);
    }

    /**
     * Get default configuration for a provider.
     *
     * @param string $providerName
     * @return array
     */
    public static function getDefaultConfig(string $providerName): array
    {
        if (isset(self::$configCache[$providerName])) {
            return self::$configCache[$providerName];
        }

        if (!isset(self::$providers[$providerName])) {
            return [];
        }

        $providerClass = self::$providers[$providerName];

        // Try to get default config from provider class
        if (method_exists($providerClass, 'getDefaultConfig')) {
            $config = $providerClass::getDefaultConfig();
        } else {
            $config = [];
        }

        // Cache the config
        self::$configCache[$providerName] = $config;

        return $config;
    }

    /**
     * Test a provider connection.
     *
     * @param string $providerName
     * @param array $config
     * @return bool
     * @throws Exception
     */
    public static function testProvider(string $providerName, array $config = []): bool
    {
        $provider = self::make($providerName, $config);

        if (method_exists($provider, 'testConnection')) {
            return $provider->testConnection();
        }

        // Fallback to basic availability check
        return $provider->isAvailable();
    }

    /**
     * Get the best available provider.
     *
     * @param array $preferredOrder
     * @return MailProviderInterface|null
     */
    public static function getBestAvailableProvider(array $preferredOrder = []): ?MailProviderInterface
    {
        // Use preferred order if provided
        if (!empty($preferredOrder)) {
            foreach ($preferredOrder as $providerName) {
                if (self::isRegistered($providerName)) {
                    try {
                        $provider = self::makeFromConfig($providerName);
                        if ($provider->isAvailable()) {
                            return $provider;
                        }
                    } catch (Exception $e) {
                        // Continue to next provider
                        continue;
                    }
                }
            }
        }

        // Fallback to any available provider
        foreach (self::$providers as $name => $class) {
            try {
                $provider = self::makeFromConfig($name);
                if ($provider->isAvailable()) {
                    return $provider;
                }
            } catch (Exception $e) {
                // Continue to next provider
                continue;
            }
        }

        return null;
    }

    /**
     * Clear provider instances cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$instanceCache = [];
        self::$configCache = [];
    }

    /**
     * Generate cache key for provider instance.
     *
     * @param string $providerName
     * @param array $config
     * @return string
     */
    private static function getCacheKey(string $providerName, array $config): string
    {
        return $providerName . '_' . md5(serialize($config));
    }

    /**
     * Get provider class name.
     *
     * @param string $providerName
     * @return string|null
     */
    public static function getProviderClass(string $providerName): ?string
    {
        return self::$providers[$providerName] ?? null;
    }

    /**
     * Get recommended provider order based on reliability.
     *
     * @return array
     */
    public static function getRecommendedOrder(): array
    {
        return ['sendgrid', 'mailgun', 'smtp'];
    }

    /**
     * Validate provider configuration without creating instance.
     *
     * @param string $providerName
     * @param array $config
     * @return bool
     * @throws Exception
     */
    public static function validateProviderConfig(string $providerName, array $config): bool
    {
        $provider = self::make($providerName, $config);
        return $provider->validateConfig();
    }
}
