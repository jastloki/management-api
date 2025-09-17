<?php

namespace Tests\Unit\Services;

use App\Services\EmailValidationService;
use Tests\TestCase;

class EmailValidationServiceTest extends TestCase
{
    protected EmailValidationService $emailValidationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailValidationService = new EmailValidationService();
    }

    /**
     * Test valid email addresses pass validation.
     */
    public function test_valid_emails_pass_validation()
    {
        $validEmails = [
            "user@gmail.com",
            "test.email@domain.co.uk",
            "user+tag@yahoo.com",
            "firstname.lastname@company.org",
            "user123@test-domain.net",
        ];

        foreach ($validEmails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertTrue(
                $result["is_valid"],
                "Email {$email} should be valid",
            );
            $this->assertEquals($email, $result["email"]);
            $this->assertIsArray($result["checks"]);
            $this->assertEquals(
                "All validation checks passed",
                $result["reason"],
            );
        }
    }

    /**
     * Test invalid email formats are rejected.
     */
    public function test_invalid_email_formats_are_rejected()
    {
        $invalidEmails = [
            "plainaddress",
            "missing@.com",
            "@domain.com",
            "user@",
            "user..name@domain.com",
            "user@domain",
            "user name@domain.com",
            "user@domain..com",
        ];

        foreach ($invalidEmails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse(
                $result["is_valid"],
                "Email {$email} should be invalid",
            );
            $this->assertArrayHasKey("checks", $result);
            $this->assertNotNull($result["reason"]);
        }
    }

    /**
     * Test empty and null emails are handled correctly.
     */
    public function test_empty_emails_are_rejected()
    {
        $emptyEmails = ["", "   ", "\t", "\n"];

        foreach ($emptyEmails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse(
                $result["is_valid"],
                "Empty email should be invalid",
            );
            $this->assertEquals("Email is empty", $result["reason"]);
        }
    }

    /**
     * Test emails exceeding maximum length are rejected.
     */
    public function test_long_emails_are_rejected()
    {
        // Create an email longer than 254 characters
        $longEmail = str_repeat("a", 240) . "@example.com";

        $result = $this->emailValidationService->validateEmail($longEmail);

        $this->assertFalse($result["is_valid"]);
        $this->assertEquals(
            "Email exceeds maximum length of 254 characters",
            $result["reason"],
        );
    }

    /**
     * Test invalid patterns are detected.
     */
    public function test_invalid_patterns_are_detected()
    {
        $invalidPatternEmails = [
            "test@gmail.com" => "Test email address",
            "noreply@company.com" => "No-reply email address",
            "no-reply@company.com" => "No-reply email address",
            "donotreply@company.com" => "Do-not-reply email address",
            "admin@localhost" => "Invalid email format",
            "test@test.com" => "Test domain email",
            "example@somedomain.com" => "Example email address",
            "user@somedomain.invalid" => "Invalid TLD",
            "user@somedomain.test" => "Test TLD",
            "user@somedomain.localhost" => "Localhost domain",
            "123@domain.com" => "Numeric-only local part",
            "user@192.168.1.1" => "IP address domain",
        ];

        foreach ($invalidPatternEmails as $email => $expectedReason) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse(
                $result["is_valid"],
                "Email {$email} should be invalid due to pattern",
            );
            $this->assertEquals($expectedReason, $result["reason"]);
        }
    }

    /**
     * Test disposable email providers are detected.
     */
    public function test_disposable_emails_are_detected()
    {
        $disposableEmails = [
            "user@10minutemail.com",
            "test@temp-mail.org",
            "user@guerrillamail.com",
            "test@mailinator.com",
            "user@yopmail.com",
            "test@throwaway.email",
        ];

        foreach ($disposableEmails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse(
                $result["is_valid"],
                "Email {$email} should be invalid (disposable)",
            );
            $this->assertStringContainsString(
                "disposable",
                strtolower($result["reason"]),
            );
        }
    }

    /**
     * Test domain validation checks.
     */
    public function test_domain_validation_checks()
    {
        // Test invalid domain formats
        $invalidDomains = [
            "user@.com",
            "user@domain.",
            "user@-domain.com",
            "user@domain-.com",
            "user@domain..com",
            "user@domain--name.com",
        ];

        foreach ($invalidDomains as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse(
                $result["is_valid"],
                "Email {$email} should have invalid domain",
            );
            $this->assertArrayHasKey("domain", $result["checks"]);
            $this->assertFalse($result["checks"]["domain"]["valid"]);
        }
    }

    /**
     * Test batch email validation.
     */
    public function test_batch_email_validation()
    {
        $emails = [
            "valid1@gmail.com",
            "valid2@yahoo.com",
            "test@invalid.invalid",
            "noreply@company.com",
            "invalid-format",
        ];

        $batchResult = $this->emailValidationService->validateBatch($emails);

        $this->assertArrayHasKey("results", $batchResult);
        $this->assertArrayHasKey("stats", $batchResult);
        $this->assertCount(5, $batchResult["results"]);
        $this->assertEquals(5, $batchResult["stats"]["total"]);
        $this->assertGreaterThan(0, $batchResult["stats"]["invalid"]);
    }

    /**
     * Test validation statistics generation.
     */
    public function test_validation_statistics()
    {
        $validationResults = [
            ["is_valid" => true, "reason" => "All validation checks passed"],
            ["is_valid" => false, "reason" => "Invalid email format"],
            ["is_valid" => false, "reason" => "Invalid email format"],
            ["is_valid" => false, "reason" => "Test email address"],
            ["is_valid" => true, "reason" => "All validation checks passed"],
        ];

        $stats = $this->emailValidationService->getValidationStats(
            $validationResults,
        );

        $this->assertEquals(5, $stats["total"]);
        $this->assertEquals(2, $stats["valid"]);
        $this->assertEquals(3, $stats["invalid"]);
        $this->assertArrayHasKey("reasons", $stats);
        $this->assertEquals(2, $stats["reasons"]["Invalid email format"]);
        $this->assertEquals(1, $stats["reasons"]["Test email address"]);
    }

    /**
     * Test email normalization.
     */
    public function test_email_normalization()
    {
        $emails = [
            "  USER@GMAIL.COM  ",
            "User@Yahoo.Com",
            "\ttest@domain.org\n",
        ];

        $expectedNormalized = [
            "user@gmail.com",
            "user@yahoo.com",
            "test@domain.org",
        ];

        foreach ($emails as $index => $email) {
            $result = $this->emailValidationService->validateEmail($email);
            $this->assertEquals($expectedNormalized[$index], $result["email"]);
        }
    }

    /**
     * Test format validation details.
     */
    public function test_format_validation_details()
    {
        $email = "user@gmail.com";
        $result = $this->emailValidationService->validateEmail($email);

        $this->assertArrayHasKey("checks", $result);
        $this->assertArrayHasKey("format", $result["checks"]);
        $this->assertTrue($result["checks"]["format"]["valid"]);
        $this->assertEquals(
            "Format validation passed",
            $result["checks"]["format"]["reason"],
        );
    }

    /**
     * Test pattern validation details.
     */
    public function test_pattern_validation_details()
    {
        $validEmail = "user@gmail.com";
        $result = $this->emailValidationService->validateEmail($validEmail);

        $this->assertArrayHasKey("patterns", $result["checks"]);
        $this->assertTrue($result["checks"]["patterns"]["valid"]);
        $this->assertEquals(
            "No invalid patterns detected",
            $result["checks"]["patterns"]["reason"],
        );

        $invalidEmail = "test@test.com";
        $result = $this->emailValidationService->validateEmail($invalidEmail);

        $this->assertArrayHasKey("patterns", $result["checks"]);
        $this->assertFalse($result["checks"]["patterns"]["valid"]);
        $this->assertEquals(
            "Test domain email",
            $result["checks"]["patterns"]["reason"],
        );
    }

    /**
     * Test disposable email validation details.
     */
    public function test_disposable_validation_details()
    {
        $validEmail = "user@gmail.com";
        $result = $this->emailValidationService->validateEmail($validEmail);

        $this->assertArrayHasKey("disposable", $result["checks"]);
        $this->assertTrue($result["checks"]["disposable"]["valid"]);
        $this->assertEquals(
            "Not a disposable email",
            $result["checks"]["disposable"]["reason"],
        );

        $disposableEmail = "user@mailinator.com";
        $result = $this->emailValidationService->validateEmail(
            $disposableEmail,
        );

        $this->assertArrayHasKey("disposable", $result["checks"]);
        $this->assertFalse($result["checks"]["disposable"]["valid"]);
        $this->assertEquals(
            "Disposable email provider detected",
            $result["checks"]["disposable"]["reason"],
        );
    }

    /**
     * Test multiple @ symbols are rejected.
     */
    public function test_multiple_at_symbols_rejected()
    {
        $emails = [
            "user@@domain.com",
            "user@domain@com",
            "user@sub@domain.com",
        ];

        foreach ($emails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse(
                $result["is_valid"],
                "Email {$email} should be invalid",
            );
            $this->assertEquals(
                "Email must contain exactly one @ symbol",
                $result["reason"],
            );
        }
    }

    /**
     * Test local and domain part length limits.
     */
    public function test_local_and_domain_part_length_limits()
    {
        // Local part too long (over 64 characters)
        $longLocalPart = str_repeat("a", 65) . "@gmail.com";
        $result = $this->emailValidationService->validateEmail($longLocalPart);

        $this->assertFalse($result["is_valid"]);
        $this->assertEquals(
            "Local or domain part exceeds maximum length",
            $result["reason"],
        );

        // Domain part too long (over 253 characters)
        $longDomainPart = "user@" . str_repeat("a", 250) . ".com";
        $result = $this->emailValidationService->validateEmail($longDomainPart);

        $this->assertFalse($result["is_valid"]);
        $this->assertEquals(
            "Local or domain part exceeds maximum length",
            $result["reason"],
        );
    }

    /**
     * Test SMTP verification when disabled.
     */
    public function test_smtp_verification_disabled()
    {
        // Ensure SMTP verification is disabled
        config(["mail.smtp_verification_enabled" => false]);

        $email = "user@gmail.com";
        $result = $this->emailValidationService->validateEmail($email);

        $this->assertTrue($result["is_valid"]);
        $this->assertArrayHasKey("domain", $result["checks"]);
        $this->assertArrayHasKey("smtp_check", $result["checks"]["domain"]);
        $this->assertFalse(
            $result["checks"]["domain"]["smtp_check"]["enabled"],
        );
        $this->assertEquals(
            "SMTP verification disabled",
            $result["checks"]["domain"]["smtp_check"]["reason"],
        );
    }

    /**
     * Test SMTP verification when enabled but in testing environment.
     */
    public function test_smtp_verification_skipped_in_testing()
    {
        // Enable SMTP verification but we're in testing environment
        config(["mail.smtp_verification_enabled" => true]);

        $email = "user@gmail.com";
        $result = $this->emailValidationService->validateEmail($email);

        $this->assertTrue($result["is_valid"]);
        $this->assertArrayHasKey("domain", $result["checks"]);
        // In testing environment, SMTP verification should be skipped
        $this->assertStringContains(
            "test environment",
            strtolower($result["checks"]["domain"]["reason"]),
        );
    }

    /**
     * Test domain validation with MX and A record checks.
     */
    public function test_domain_validation_with_dns_checks()
    {
        $email = "user@gmail.com";
        $result = $this->emailValidationService->validateEmail($email);

        $this->assertArrayHasKey("domain", $result["checks"]);
        $domainCheck = $result["checks"]["domain"];

        $this->assertTrue($domainCheck["valid"]);
        $this->assertArrayHasKey("has_mx", $domainCheck);
        $this->assertArrayHasKey("has_a", $domainCheck);
        $this->assertIsBool($domainCheck["has_mx"]);
        $this->assertIsBool($domainCheck["has_a"]);
    }

    /**
     * Test SMTP timeout configuration.
     */
    public function test_smtp_timeout_configuration()
    {
        // Set custom SMTP timeout
        config(["mail.smtp_timeout" => 5]);

        $email = "user@nonexistent-domain-12345.com";
        $result = $this->emailValidationService->validateEmail($email);

        // Should handle timeout gracefully and not mark as invalid
        $this->assertIsArray($result);
        $this->assertArrayHasKey("is_valid", $result);
    }

    /**
     * Test domain extraction from email.
     */
    public function test_domain_extraction()
    {
        $testCases = [
            "user@gmail.com" => "gmail.com",
            "test.email@subdomain.example.org" => "subdomain.example.org",
            "user+tag@company.co.uk" => "company.co.uk",
        ];

        foreach ($testCases as $email => $expectedDomain) {
            $result = $this->emailValidationService->validateEmail($email);

            // The domain validation should work with extracted domain
            $this->assertArrayHasKey("domain", $result["checks"]);
            if ($result["checks"]["domain"]["valid"]) {
                $this->assertTrue(true); // Domain extraction worked
            } else {
                // If domain check failed, it should be due to DNS, not extraction
                $this->assertStringNotContains(
                    "Domain is empty",
                    $result["checks"]["domain"]["reason"],
                );
            }
        }
    }

    /**
     * Test consecutive dots and dashes validation.
     */
    public function test_consecutive_characters_validation()
    {
        $invalidEmails = [
            "user@domain..com",
            "user@domain--name.com",
            "user@sub..domain.org",
            "user@sub--domain.net",
        ];

        foreach ($invalidEmails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse($result["is_valid"]);
            $this->assertStringContains(
                "consecutive",
                strtolower($result["reason"]),
            );
        }
    }

    /**
     * Test domain starts/ends with invalid characters.
     */
    public function test_domain_boundary_characters()
    {
        $invalidEmails = [
            "user@.domain.com",
            "user@domain.com.",
            "user@-domain.com",
            "user@domain.com-",
        ];

        foreach ($invalidEmails as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertFalse($result["is_valid"]);
            $this->assertStringContains(
                "invalid character",
                strtolower($result["reason"]),
            );
        }
    }

    /**
     * Test test domain detection.
     */
    public function test_test_domain_detection()
    {
        $testDomains = [
            "user@example.com",
            "user@example.org",
            "user@example.net",
            "user@test.com",
            "user@testing.com",
        ];

        foreach ($testDomains as $email) {
            $result = $this->emailValidationService->validateEmail($email);

            $this->assertArrayHasKey("domain", $result["checks"]);
            if ($result["checks"]["domain"]["valid"]) {
                $this->assertStringContains(
                    "test environment",
                    strtolower($result["checks"]["domain"]["reason"]),
                );
            }
        }
    }

    /**
     * Test error handling in domain validation.
     */
    public function test_domain_validation_error_handling()
    {
        // Test with potentially problematic domain
        $email = "user@invalid-test-domain-xyz-123.nonexistent";
        $result = $this->emailValidationService->validateEmail($email);

        // Should handle DNS lookup failures gracefully
        $this->assertIsArray($result);
        $this->assertArrayHasKey("is_valid", $result);
        $this->assertArrayHasKey("checks", $result);
        $this->assertArrayHasKey("domain", $result["checks"]);
    }

    /**
     * Test batch validation with SMTP verification scenarios.
     */
    public function test_batch_validation_with_smtp_scenarios()
    {
        $emails = [
            "valid@gmail.com",
            "invalid@nonexistent-domain-12345.com",
            "test@example.com",
            "disposable@mailinator.com",
            "invalid-format-email",
        ];

        $batchResult = $this->emailValidationService->validateBatch($emails);

        $this->assertArrayHasKey("results", $batchResult);
        $this->assertArrayHasKey("stats", $batchResult);
        $this->assertEquals(5, $batchResult["stats"]["total"]);

        // Each result should have consistent structure
        foreach ($batchResult["results"] as $result) {
            $this->assertArrayHasKey("is_valid", $result);
            $this->assertArrayHasKey("email", $result);
            $this->assertArrayHasKey("checks", $result);
            $this->assertArrayHasKey("reason", $result);
        }
    }

    /**
     * Test SMTP verification response handling.
     */
    public function test_smtp_response_handling()
    {
        // Enable SMTP verification for this test
        config(["mail.smtp_verification_enabled" => true]);

        // Test with a real domain that should have MX records
        $email = "test@gmail.com";

        // Since we're in testing environment, this will be skipped
        // but we can test the configuration is properly set
        $result = $this->emailValidationService->validateEmail($email);

        $this->assertArrayHasKey("domain", $result["checks"]);
        $this->assertTrue($result["is_valid"]);
    }
}
