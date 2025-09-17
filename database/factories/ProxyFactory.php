<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Proxy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proxy>
 */
class ProxyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Proxy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ["http", "https", "socks4", "socks5"];
        $countries = [
            "United States",
            "Canada",
            "United Kingdom",
            "Germany",
            "France",
            "Netherlands",
            "Singapore",
            "Japan",
            "Australia",
            "Brazil",
        ];
        $cities = [
            "New York",
            "Los Angeles",
            "Toronto",
            "London",
            "Berlin",
            "Amsterdam",
            "Singapore",
            "Tokyo",
            "Sydney",
            "São Paulo",
        ];

        $type = $this->faker->randomElement($types);
        $defaultPorts = [
            "http" => [8080, 3128, 8888],
            "https" => [443, 8443, 9443],
            "socks4" => [1080, 1085],
            "socks5" => [1080, 1085, 9050],
        ];

        $providers = [
            "ProxyService Inc",
            "GlobalProxy Ltd",
            "SecureNet Solutions",
            "FastProxy Corp",
            "EliteProxies",
            "ProxyHub",
            "NetShield",
        ];

        return [
            "name" =>
                $this->faker->company . " " . strtoupper($type) . " Proxy",
            "url" => $this->faker->boolean(70)
                ? $this->faker->domainName
                : $this->faker->ipv4,
            "type" => $type,
            "port" => $this->faker->randomElement($defaultPorts[$type]),
            "username" => $this->faker->boolean(60)
                ? $this->faker->userName
                : null,
            "password" => $this->faker->boolean(60)
                ? $this->faker->password(8, 16)
                : null,
            "country" => $this->faker->randomElement($countries),
            "city" => $this->faker->randomElement($cities),
            "description" => $this->faker->boolean(80)
                ? $this->faker->sentence(10, 20)
                : null,
            "is_active" => $this->faker->boolean(85),
            "status" => $this->faker->randomElement([
                "untested",
                "working",
                "failed",
            ]),
            "response_time" => $this->faker->boolean(60)
                ? $this->faker->numberBetween(50, 2000)
                : null,
            "last_tested_at" => $this->faker->boolean(70)
                ? $this->faker->dateTimeBetween("-1 week", "now")
                : null,
            "extra_fields" => $this->faker->boolean(50)
                ? [
                    "provider" => $this->faker->randomElement($providers),
                    "bandwidth" => $this->faker->randomElement([
                        "100Mbps",
                        "1Gbps",
                        "10Gbps",
                        "Unlimited",
                    ]),
                    "uptime" => $this->faker->randomElement([
                        "99.5%",
                        "99.9%",
                        "99.99%",
                    ]),
                    "concurrent_connections" => $this->faker->randomElement([
                        "50",
                        "100",
                        "500",
                        "1000",
                    ]),
                    "location_tier" => $this->faker->randomElement([
                        "Tier 1",
                        "Tier 2",
                        "Tier 3",
                    ]),
                ]
                : null,
        ];
    }

    /**
     * Indicate that the proxy is active.
     */
    public function active(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "is_active" => true,
            ],
        );
    }

    /**
     * Indicate that the proxy is inactive.
     */
    public function inactive(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "is_active" => false,
            ],
        );
    }

    /**
     * Indicate that the proxy is working.
     */
    public function working(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "working",
                "response_time" => $this->faker->numberBetween(50, 500),
                "last_tested_at" => $this->faker->dateTimeBetween(
                    "-1 day",
                    "now",
                ),
            ],
        );
    }

    /**
     * Indicate that the proxy has failed.
     */
    public function failed(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "failed",
                "response_time" => null,
                "last_tested_at" => $this->faker->dateTimeBetween(
                    "-1 week",
                    "now",
                ),
            ],
        );
    }

    /**
     * Indicate that the proxy is untested.
     */
    public function untested(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "status" => "untested",
                "response_time" => null,
                "last_tested_at" => null,
            ],
        );
    }

    /**
     * Indicate that the proxy has authentication.
     */
    public function withAuth(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "username" => $this->faker->userName,
                "password" => $this->faker->password(8, 16),
            ],
        );
    }

    /**
     * Indicate that the proxy has no authentication.
     */
    public function withoutAuth(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "username" => null,
                "password" => null,
            ],
        );
    }

    /**
     * Set the proxy type to HTTP.
     */
    public function http(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => "http",
                "port" => $this->faker->randomElement([8080, 3128, 8888]),
            ],
        );
    }

    /**
     * Set the proxy type to HTTPS.
     */
    public function https(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => "https",
                "port" => $this->faker->randomElement([443, 8443, 9443]),
            ],
        );
    }

    /**
     * Set the proxy type to SOCKS4.
     */
    public function socks4(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => "socks4",
                "port" => $this->faker->randomElement([1080, 1085]),
            ],
        );
    }

    /**
     * Set the proxy type to SOCKS5.
     */
    public function socks5(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "type" => "socks5",
                "port" => $this->faker->randomElement([1080, 1085, 9050]),
            ],
        );
    }
}
