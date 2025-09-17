<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CheckClientEmailValidityJob;
use App\Models\Client;
use App\Services\EmailValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckClientEmailValidityJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job processes clients with invalid emails correctly.
     */
    public function test_job_processes_invalid_email_clients()
    {
        // Create test clients with various email statuses
        $validClient = Client::factory()->create([
            'email' => 'valid@example.com',
            'is_email_valid' => false, // Will be validated
        ]);

        $invalidClient = Client::factory()->create([
            'email' => 'invalid@invalid.invalid',
            'is_email_valid' => false, // Will be validated
        ]);

        $alreadyValidClient = Client::factory()->create([
            'email' => 'already@valid.com',
            'is_email_valid' => true, // Should be skipped
        ]);

        // Mock the email validation service
        $mockValidationService = $this->mock(EmailValidationService::class);

        $mockValidationService->shouldReceive('validateEmail')
            ->with('valid@example.com')
            ->once()
            ->andReturn([
                'is_valid' => true,
                'reason' => 'All validation checks passed',
                'checks' => [
                    'format' => ['valid' => true, 'reason' => 'Format validation passed'],
                    'patterns' => ['valid' => true, 'reason' => 'No invalid patterns detected'],
                    'domain' => ['valid' => true, 'reason' => 'Domain validation passed'],
                    'disposable' => ['valid' => true, 'reason' => 'Not a disposable email'],
                ],
            ]);

        $mockValidationService->shouldReceive('validateEmail')
            ->with('invalid@invalid.invalid')
            ->once()
            ->andReturn([
                'is_valid' => false,
                'reason' => 'Domain has no MX or A record',
                'checks' => [
                    'format' => ['valid' => true, 'reason' => 'Format validation passed'],
                    'patterns' => ['valid' => true, 'reason' => 'No invalid patterns detected'],
                    'domain' => ['valid' => false, 'reason' => 'Domain has no MX or A record'],
                ],
            ]);

        // Create job with chunk size of 10 (should process both invalid clients)
        $job = new CheckClientEmailValidityJob(1, 10);

        // Replace the service in the job
        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('emailValidator');
        $property->setAccessible(true);
        $property->setValue($job, $mockValidationService);

        // Execute the job
        $job->handle();

        // Assert clients were updated correctly
        $validClient->refresh();
        $this->assertTrue($validClient->is_email_valid);
        $this->assertEquals('valid', $validClient->email_status);
        $this->assertEquals('All validation checks passed', $validClient->email_validation_reason);
        $this->assertNotNull($validClient->email_last_validated_at);
        $this->assertEquals(1, $validClient->email_validation_attempts);

        $invalidClient->refresh();
        $this->assertFalse($invalidClient->is_email_valid);
        $this->assertEquals('invalid', $invalidClient->email_status);
        $this->assertEquals('Domain has no MX or A record', $invalidClient->email_validation_reason);
        $this->assertNotNull($invalidClient->email_last_validated_at);
        $this->assertEquals(1, $invalidClient->email_validation_attempts);

        // Already valid client should not be processed
        $alreadyValidClient->refresh();
        $this->assertTrue($alreadyValidClient->is_email_valid);
        $this->assertNull($alreadyValidClient->email_last_validated_at);
        $this->assertEquals(0, $alreadyValidClient->email_validation_attempts);
    }

    /**
     * Test job handles pagination correctly.
     */
    public function test_job_handles_pagination_correctly()
    {
        Queue::fake();

        // Create 5 clients with invalid emails
        Client::factory()->count(5)->create([
            'is_email_valid' => false,
        ]);

        // Create job with chunk size of 2
        $job = new CheckClientEmailValidityJob(1, 2);

        // Mock the email validation service to return valid results
        $mockValidationService = $this->mock(EmailValidationService::class);
        $mockValidationService->shouldReceive('validateEmail')
            ->andReturn([
                'is_valid' => true,
                'reason' => 'All validation checks passed',
                'checks' => [],
            ]);

        // Replace the service in the job
        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('emailValidator');
        $property->setAccessible(true);
        $property->setValue($job, $mockValidationService);

        // Execute the job
        $job->handle();

        // Should dispatch next job for remaining clients
        Queue::assertPushed(CheckClientEmailValidityJob::class, function ($job) {
            return $job->page === 2 && $job->chunkSize === 2;
        });
    }

    /**
     * Test job stops when no more clients to process.
     */
    public function test_job_stops_when_no_more_clients()
    {
        Queue::fake();

        // Create only 1 client with invalid email
        Client::factory()->create([
            'is_email_valid' => false,
        ]);

        // Create job with chunk size of 10 (more than available)
        $job = new CheckClientEmailValidityJob(1, 10);

        // Mock the email validation service
        $mockValidationService = $this->mock(EmailValidationService::class);
        $mockValidationService->shouldReceive('validateEmail')
            ->once()
            ->andReturn([
                'is_valid' => true,
                'reason' => 'All validation checks passed',
                'checks' => [],
            ]);

        // Replace the service in the job
        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('emailValidator');
        $property->setAccessible(true);
        $property->setValue($job, $mockValidationService);

        // Execute the job
        $job->handle();

        // Should not dispatch any more jobs
        Queue::assertNotPushed(CheckClientEmailValidityJob::class);
    }

    /**
     * Test job skips clients without email addresses.
     */
    public function test_job_skips_clients_without_email()
    {
        // Create clients with various email states
        Client::factory()->create([
            'email' => null,
            'is_email_valid' => false,
        ]);

        Client::factory()->create([
            'email' => '',
            'is_email_valid' => false,
        ]);

        $validEmailClient = Client::factory()->create([
            'email' => 'test@example.com',
            'is_email_valid' => false,
        ]);

        // Mock the email validation service (should only be called once)
        $mockValidationService = $this->mock(EmailValidationService::class);
        $mockValidationService->shouldReceive('validateEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn([
                'is_valid' => true,
                'reason' => 'All validation checks passed',
                'checks' => [],
            ]);

        // Create and execute job
        $job = new CheckClientEmailValidityJob(1, 10);

        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('emailValidator');
        $property->setAccessible(true);
        $property->setValue($job, $mockValidationService);

        $job->handle();

        // Only the client with valid email should be processed
        $validEmailClient->refresh();
        $this->assertTrue($validEmailClient->is_email_valid);
        $this->assertEquals(1, $validEmailClient->email_validation_attempts);
    }

    /**
     * Test job handles validation service exceptions gracefully.
     */
    public function test_job_handles_validation_exceptions()
    {
        $client = Client::factory()->create([
            'email' => 'test@example.com',
            'is_email_valid' => false,
        ]);

        // Mock the email validation service to throw exception
        $mockValidationService = $this->mock(EmailValidationService::class);
        $mockValidationService->shouldReceive('validateEmail')
            ->once()
            ->andThrow(new \Exception('Validation service error'));

        // Create and execute job
        $job = new CheckClientEmailValidityJob(1, 10);

        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('emailValidator');
        $property->setAccessible(true);
        $property->setValue($job, $mockValidationService);

        // Should not throw exception
        $job->handle();

        // Client should remain unchanged
        $client->refresh();
        $this->assertFalse($client->is_email_valid);
        $this->assertEquals(0, $client->email_validation_attempts);
        $this->assertNull($client->email_last_validated_at);
    }

    /**
     * Test job returns early when no clients found.
     */
    public function test_job_returns_early_when_no_clients_found()
    {
        Queue::fake();

        // Create job but no clients with invalid emails
        $job = new CheckClientEmailValidityJob(1, 10);

        // Mock validation service should not be called
        $mockValidationService = $this->mock(EmailValidationService::class);
        $mockValidationService->shouldNotReceive('validateEmail');

        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('emailValidator');
        $property->setAccessible(true);
        $property->setValue($job, $mockValidationService);

        $job->handle();

        // Should not dispatch any jobs
        Queue::assertNotPushed(CheckClientEmailValidityJob::class);
    }
}
