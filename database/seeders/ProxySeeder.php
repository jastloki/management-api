<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Proxy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProxySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $proxies = [
            [
                "name" => "US East Coast HTTP",
                "url" => "proxy-east.example.com",
                "type" => "http",
                "port" => 8080,
                "username" => "user1",
                "password" => "pass123",
                "country" => "United States",
                "city" => "New York",
                "description" =>
                    "Fast HTTP proxy located in New York data center",
                "is_active" => true,
                "extra_fields" => [
                    "provider" => "ProxyService Inc",
                    "bandwidth" => "1Gbps",
                    "concurrent_connections" => "100",
                ],
            ],
            [
                "name" => "Europe HTTPS Proxy",
                "url" => "secure-proxy.eu",
                "type" => "https",
                "port" => 443,
                "username" => "eurouser",
                "password" => "securepass",
                "country" => "Germany",
                "city" => "Frankfurt",
                "description" => "Secure HTTPS proxy with SSL encryption",
                "is_active" => true,
                "extra_fields" => [
                    "provider" => "EuroProxy GmbH",
                    "ssl_grade" => "A+",
                    "uptime" => "99.9%",
                ],
            ],
            [
                "name" => "Asia Pacific SOCKS5",
                "url" => "192.168.50.100",
                "type" => "socks5",
                "port" => 1080,
                "country" => "Singapore",
                "city" => "Singapore",
                "description" =>
                    "High-speed SOCKS5 proxy for Asia-Pacific region",
                "is_active" => true,
                "extra_fields" => [
                    "provider" => "AsiaTech Solutions",
                    "location_tier" => "Tier 1",
                    "support_ipv6" => "yes",
                ],
            ],
            [
                "name" => "UK London HTTP",
                "url" => "london-proxy.uk.net",
                "type" => "http",
                "port" => 3128,
                "username" => "ukuser",
                "password" => "londonpass",
                "country" => "United Kingdom",
                "city" => "London",
                "description" => "UK-based HTTP proxy for local content access",
                "is_active" => false,
                "extra_fields" => [
                    "provider" => "BritProxy Ltd",
                    "data_retention" => "7 days",
                    "gdpr_compliant" => "yes",
                ],
            ],
            [
                "name" => "Canada Toronto SOCKS4",
                "url" => "tor-proxy.ca",
                "type" => "socks4",
                "port" => 1080,
                "country" => "Canada",
                "city" => "Toronto",
                "description" => "Canadian SOCKS4 proxy with fast speeds",
                "is_active" => true,
                "extra_fields" => [
                    "provider" => "MapleProxy Inc",
                    "speed_rating" => "Fast",
                    "logs_policy" => "No logs",
                ],
            ],
            [
                "name" => "Australia Sydney HTTP",
                "url" => "203.45.67.89",
                "type" => "http",
                "port" => 8080,
                "country" => "Australia",
                "city" => "Sydney",
                "description" => "Australian proxy server for Oceania region",
                "is_active" => true,
                "extra_fields" => [
                    "provider" => "OzProxy Solutions",
                    "ping_time" => "< 50ms",
                    "traffic_limit" => "Unlimited",
                ],
            ],
            [
                "name" => "Japan Tokyo HTTPS",
                "url" => "tokyo-secure.jp",
                "type" => "https",
                "port" => 8443,
                "username" => "jpuser",
                "password" => "tokyopass123",
                "country" => "Japan",
                "city" => "Tokyo",
                "description" =>
                    "Japanese HTTPS proxy with enterprise-grade security",
                "is_active" => true,
                "extra_fields" => [
                    "provider" => "NipponProxy Corp",
                    "encryption" => "AES-256",
                    "compliance" => "ISO 27001",
                ],
            ],
            [
                "name" => "Brazil Sao Paulo SOCKS5",
                "url" => "br-proxy.com.br",
                "type" => "socks5",
                "port" => 9050,
                "country" => "Brazil",
                "city" => "São Paulo",
                "description" =>
                    "South American SOCKS5 proxy for regional access",
                "is_active" => false,
                "extra_fields" => [
                    "provider" => "BrasilProxy Ltda",
                    "language_support" => "Portuguese",
                    "local_content" => "Available",
                ],
            ],
        ];

        foreach ($proxies as $proxyData) {
            Proxy::create($proxyData);
        }

        $this->command->info("Created " . count($proxies) . " sample proxies.");
    }
}
