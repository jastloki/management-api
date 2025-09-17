<?php

namespace App\Console\Commands;

use App\Services\EmailValidationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class TestSmtpVerificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-smtp {email} {--enable-smtp} {--timeout=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMTP email verification for a specific email address';

    /**
     * The email validation service instance.
     *
     * @var EmailValidationService
     */
    protected EmailValidationService $emailValidationService;

    /**
     * Create a new command instance.
     */
    public function __construct(EmailValidationService $emailValidationService)
    {
        parent::__construct();
        $this->emailValidationService = $emailValidationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $enableSmtp = $this->option('enable-smtp');
        $timeout = $this->option('timeout');

        $this->info("Testing email validation for: {$email}");
        $this->newLine();

        // Configure SMTP verification if requested
        if ($enableSmtp) {
            Config::set('mail.smtp_verification_enabled', true);
            Config::set('mail.smtp_timeout', (int) $timeout);
            $this->warn("SMTP verification ENABLED (timeout: {$timeout}s)");
        } else {
            Config::set('mail.smtp_verification_enabled', false);
            $this->info("SMTP verification DISABLED");
        }

        $this->newLine();

        // Perform validation
        $startTime = microtime(true);
        $result = $this->emailValidationService->validateEmail($email);
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        // Display results
        $this->displayResults($result, $duration);

        return $result['is_valid'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Display the validation results in a formatted way.
     *
     * @param array $result
     * @param float $duration
     * @return void
     */
    protected function displayResults(array $result, float $duration): void
    {
        // Overall result
        $this->newLine();
        if ($result['is_valid']) {
            $this->info("✅ EMAIL IS VALID");
        } else {
            $this->error("❌ EMAIL IS INVALID");
        }

        $this->info("Duration: {$duration}ms");
        $this->newLine();

        // Basic info
        $this->line("📧 Normalized Email: " . $result['email']);
        $this->line("💭 Reason: " . $result['reason']);
        $this->newLine();

        // Detailed checks
        $this->line("🔍 DETAILED CHECKS:");
        $this->line("─────────────────────");

        foreach ($result['checks'] as $checkName => $checkResult) {
            $status = $checkResult['valid'] ? '✅' : '❌';
            $this->line("{$status} " . ucfirst($checkName) . ": " . $checkResult['reason']);

            // Show additional details for domain check
            if ($checkName === 'domain' && is_array($checkResult)) {
                if (isset($checkResult['has_mx'])) {
                    $mxStatus = $checkResult['has_mx'] ? '✅' : '❌';
                    $this->line("   {$mxStatus} MX Record: " . ($checkResult['has_mx'] ? 'Found' : 'Not found'));
                }

                if (isset($checkResult['has_a'])) {
                    $aStatus = $checkResult['has_a'] ? '✅' : '❌';
                    $this->line("   {$aStatus} A Record: " . ($checkResult['has_a'] ? 'Found' : 'Not found'));
                }

                if (isset($checkResult['smtp_check'])) {
                    $smtpCheck = $checkResult['smtp_check'];
                    $this->newLine();
                    $this->line("📬 SMTP VERIFICATION:");
                    $this->line("─────────────────────");

                    if (isset($smtpCheck['enabled']) && !$smtpCheck['enabled']) {
                        $this->line("⚠️  SMTP Check: Disabled");
                        $this->line("   Reason: " . $smtpCheck['reason']);
                    } else {
                        $smtpStatus = $smtpCheck['valid'] ? '✅' : '❌';
                        $this->line("{$smtpStatus} SMTP Check: " . $smtpCheck['reason']);

                        if (isset($smtpCheck['mx_host'])) {
                            $this->line("   📡 MX Host: " . $smtpCheck['mx_host']);
                        }

                        if (isset($smtpCheck['smtp_response'])) {
                            $this->line("   📝 SMTP Response: " . $smtpCheck['smtp_response']);
                        }
                    }
                }
            }
        }

        $this->newLine();
    }

    /**
     * Display help information about SMTP verification.
     */
    protected function displayHelp(): void
    {
        $this->newLine();
        $this->line("🔧 SMTP VERIFICATION INFO:");
        $this->line("─────────────────────────");
        $this->line("SMTP verification connects to the recipient's mail server");
        $this->line("to check if an email address actually exists.");
        $this->newLine();
        $this->line("⚠️  Warning: Some mail servers may:");
        $this->line("   • Block or throttle verification attempts");
        $this->line("   • Return false positives/negatives");
        $this->line("   • Consider frequent checks as spam");
        $this->newLine();
        $this->line("💡 Usage Examples:");
        $this->line("   php artisan email:test-smtp user@gmail.com");
        $this->line("   php artisan email:test-smtp user@domain.com --enable-smtp");
        $this->line("   php artisan email:test-smtp user@domain.com --enable-smtp --timeout=15");
        $this->newLine();
    }
}
