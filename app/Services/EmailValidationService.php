<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class EmailValidationService
{
    /**
     * Validate email address using multiple validation methods.
     *
     * @param string $email
     * @return array
     */
    public function validateEmail(string $email): array
    {
        $result = [
            "is_valid" => false,
            "email" => $email,
            "checks" => [],
            "reason" => null,
        ];

        try {
            // Trim and normalize email
            $email = trim(strtolower($email));
            $result["email"] = $email;

            // Check 1: Basic format validation
            $formatCheck = $this->validateFormat($email);
            $result["checks"]["format"] = $formatCheck;

            if (!$formatCheck["valid"]) {
                $result["reason"] = $formatCheck["reason"];
                return $result;
            }

            // Check 2: Invalid patterns
            $patternCheck = $this->checkInvalidPatterns($email);
            $result["checks"]["patterns"] = $patternCheck;

            if (!$patternCheck["valid"]) {
                $result["reason"] = $patternCheck["reason"];
                return $result;
            }

            // Check 3: Domain validation
            $domainCheck = $this->validateDomain($email);
            $result["checks"]["domain"] = $domainCheck;

            if (!$domainCheck["valid"]) {
                $result["reason"] = $domainCheck["reason"];
                return $result;
            }

            // Check 4: Disposable email check
            $disposableCheck = $this->checkDisposableEmail($email);
            $result["checks"]["disposable"] = $disposableCheck;

            if (!$disposableCheck["valid"]) {
                $result["reason"] = $disposableCheck["reason"];
                return $result;
            }

            // All checks passed
            $result["is_valid"] = true;
            $result["reason"] = "All validation checks passed";
        } catch (Exception $e) {
            Log::warning("Email validation service error", [
                "email" => $email,
                "error" => $e->getMessage(),
            ]);

            $result["reason"] = "Validation error: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * Validate email format using PHP's built-in filter.
     *
     * @param string $email
     * @return array
     */
    protected function validateFormat(string $email): array
    {
        if (empty($email)) {
            return [
                "valid" => false,
                "reason" => "Email is empty",
            ];
        }

        if (strlen($email) > 254) {
            return [
                "valid" => false,
                "reason" => "Email exceeds maximum length of 254 characters",
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "valid" => false,
                "reason" => "Invalid email format",
            ];
        }

        // Additional format checks
        if (substr_count($email, "@") !== 1) {
            return [
                "valid" => false,
                "reason" => "Email must contain exactly one @ symbol",
            ];
        }

        $parts = explode("@", $email);
        if (strlen($parts[0]) > 64 || strlen($parts[1]) > 253) {
            return [
                "valid" => false,
                "reason" => "Local or domain part exceeds maximum length",
            ];
        }

        return [
            "valid" => true,
            "reason" => "Format validation passed",
        ];
    }

    /**
     * Check for common invalid email patterns.
     *
     * @param string $email
     * @return array
     */
    protected function checkInvalidPatterns(string $email): array
    {
        $invalidPatterns = [
            "/^test@/" => "Test email address",
            "/^noreply@/" => "No-reply email address",
            "/^no-reply@/" => "No-reply email address",
            "/^donotreply@/" => "Do-not-reply email address",
            "/^admin@localhost/" => "Localhost admin email",
            "/^test@test\./" => "Test domain email",
            "/^example@/" => "Example email address",
            "/^user@example\./" => "Example domain email",
            '/\.local$/' => "Local domain",
            '/^(.+)@(.+)\.invalid$/' => "Invalid TLD",
            '/^(.+)@(.+)\.test$/' => "Test TLD",
            '/^(.+)@(.+)\.localhost$/' => "Localhost domain",
            "/^\d+@/" => "Numeric-only local part",
            '/^(.+)@\d+\.\d+\.\d+\.\d+$/' => "IP address domain",
        ];

        foreach ($invalidPatterns as $pattern => $reason) {
            if (preg_match($pattern, $email)) {
                return [
                    "valid" => false,
                    "reason" => $reason,
                ];
            }
        }

        return [
            "valid" => true,
            "reason" => "No invalid patterns detected",
        ];
    }

    /**
     * Validate email domain by performing DNS lookup and SMTP verification.
     *
     * @param string $email
     * @return array
     */
    protected function validateDomain(string $email): array
    {
        try {
            $domain = substr(strrchr($email, "@"), 1);

            if (empty($domain)) {
                return [
                    "valid" => false,
                    "reason" => "Domain is empty",
                ];
            }

            // Check for basic domain format
            if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
                return [
                    "valid" => false,
                    "reason" => "Invalid domain format",
                ];
            }

            // Check for consecutive dots or dashes
            if (
                strpos($domain, "..") !== false ||
                strpos($domain, "--") !== false
            ) {
                return [
                    "valid" => false,
                    "reason" => "Domain contains consecutive dots or dashes",
                ];
            }

            // Check if domain starts or ends with dot or dash
            if (
                str_starts_with($domain, ".") ||
                str_ends_with($domain, ".") ||
                str_starts_with($domain, "-") ||
                str_ends_with($domain, "-")
            ) {
                return [
                    "valid" => false,
                    "reason" => "Domain starts or ends with invalid character",
                ];
            }

            // // Skip DNS checks in testing environment or for known test domains
            // if (app()->environment("testing") || $this->isTestDomain($domain)) {
            //     return [
            //         "valid" => true,
            //         "reason" => "Domain validation passed (test environment)",
            //     ];
            // }

            // Perform DNS lookup (MX record preferred, A record as fallback)
            $hasMX = checkdnsrr($domain, "MX");
            $hasA = checkdnsrr($domain, "A");

            if (!$hasMX && !$hasA) {
                return [
                    "valid" => false,
                    "reason" => "Domain has no MX or A record",
                ];
            }
            // Check if SMTP verification is enabled

            return [
                "valid" => true,
                "reason" => "Domain validation passed",
                "has_mx" => $hasMX,
                "has_a" => $hasA,
                "smtp_check" => [
                    "enabled" => false,
                    "reason" => "SMTP verification disabled",
                ],
            ];
        } catch (Exception $e) {
            Log::warning("Domain validation failed", [
                "email" => $email,
                "error" => $e->getMessage(),
            ]);

            // If DNS check fails, don't mark as invalid (could be network issue)
            return [
                "valid" => true,
                "reason" =>
                    "Domain check skipped due to error: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if email is from a known disposable email provider.
     *
     * @param string $email
     * @return array
     */
    protected function checkDisposableEmail(string $email): array
    {
        $domain = substr(strrchr($email, "@"), 1);

        // Common disposable email domains
        $disposableDomains = [
            "10minutemail.com",
            "temp-mail.org",
            "guerrillamail.com",
            "mailinator.com",
            "yopmail.com",
            "throwaway.email",
            "tempmail.plus",
            "getnada.com",
            "maildrop.cc",
            "sharklasers.com",
            "grr.la",
            "guerrillamailblock.com",
            "pokemail.net",
            "spam4.me",
            "tempail.com",
            "tempr.email",
            "dispostable.com",
            "fakeinbox.com",
            "mytrashmail.com",
            "trbvm.com",
        ];

        if (in_array($domain, $disposableDomains)) {
            return [
                "valid" => false,
                "reason" => "Disposable email provider detected",
            ];
        }

        // Check for common disposable email patterns
        $disposablePatterns = [
            "/^\d+min/",
            "/temp/",
            "/trash/",
            "/throw/",
            "/disposable/",
            "/fake/",
        ];

        foreach ($disposablePatterns as $pattern) {
            if (preg_match($pattern, $domain)) {
                return [
                    "valid" => false,
                    "reason" => "Potentially disposable email domain pattern",
                ];
            }
        }

        return [
            "valid" => true,
            "reason" => "Not a disposable email",
        ];
    }

    /**
     * Batch validate multiple emails.
     *
     * @param array $emails
     * @return array
     */
    public function validateBatch(array $emails): array
    {
        $results = [];
        $stats = [
            "total" => count($emails),
            "valid" => 0,
            "invalid" => 0,
        ];

        foreach ($emails as $email) {
            $result = $this->validateEmail($email);
            $results[] = $result;

            if ($result["is_valid"]) {
                $stats["valid"]++;
            } else {
                $stats["invalid"]++;
            }
        }

        return [
            "results" => $results,
            "stats" => $stats,
        ];
    }

    /**
     * Get validation statistics for a set of emails.
     *
     * @param array $validationResults
     * @return array
     */
    public function getValidationStats(array $validationResults): array
    {
        $stats = [
            "total" => count($validationResults),
            "valid" => 0,
            "invalid" => 0,
            "reasons" => [],
        ];

        foreach ($validationResults as $result) {
            if ($result["is_valid"]) {
                $stats["valid"]++;
            } else {
                $stats["invalid"]++;
                $reason = $result["reason"] ?? "Unknown error";
                $stats["reasons"][$reason] =
                    ($stats["reasons"][$reason] ?? 0) + 1;
            }
        }

        return $stats;
    }

    /**
     * Check if domain is a test domain that should be allowed.
     *
     * @param string $domain
     * @return bool
     */
    protected function isTestDomain(string $domain): bool
    {
        $testDomains = [
            "example.com",
            "example.org",
            "example.net",
            "test.com",
            "testing.com",
            "domain.co.uk",
            "company.org",
            "test-domain.net",
        ];

        return in_array(strtolower($domain), $testDomains);
    }
}
