<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    public function test_admin_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        $securityLog = $this->spySecurityLog();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from('/admin-login')
                ->post('/admin-login', [
                    'email' => 'admin@example.test',
                ])
                ->assertRedirect('/admin-login');
        }

        $this->from('/admin-login')
            ->post('/admin-login', [
                'email' => 'admin@example.test',
            ])
            ->assertTooManyRequests();

        $this->assertRateLimitWasLogged($securityLog, 'admin-login', 'admin-login.store');
    }

    public function test_document_request_upload_submissions_are_rate_limited_after_ten_attempts_per_ten_minutes(): void
    {
        $securityLog = $this->spySecurityLog();

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->from('/document-request')
                ->post('/document-request')
                ->assertRedirect('/document-request');
        }

        $this->from('/document-request')
            ->post('/document-request')
            ->assertTooManyRequests();

        $this->assertRateLimitWasLogged($securityLog, 'uploads', 'document-request.store');
    }

    public function test_item_report_upload_submissions_are_rate_limited_after_ten_attempts_per_ten_minutes(): void
    {
        $securityLog = $this->spySecurityLog();

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->from('/report-item')
                ->post('/report-item')
                ->assertRedirect('/report-item');
        }

        $this->from('/report-item')
            ->post('/report-item')
            ->assertTooManyRequests();

        $this->assertRateLimitWasLogged($securityLog, 'uploads', 'report-item.store');
    }

    public function test_chatbot_messages_are_rate_limited_after_thirty_attempts_per_minute(): void
    {
        $securityLog = $this->spySecurityLog();

        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $this->post('/chatbot/message')
                ->assertRedirect();
        }

        $this->post('/chatbot/message')
            ->assertTooManyRequests();

        $this->assertRateLimitWasLogged($securityLog, 'chatbot', 'chatbot.reply');
    }

    private function spySecurityLog(): MockInterface
    {
        $securityLog = Mockery::spy(LoggerInterface::class);

        Log::shouldReceive('channel')
            ->once()
            ->with('security')
            ->andReturn($securityLog);

        return $securityLog;
    }

    private function assertRateLimitWasLogged(MockInterface $securityLog, string $limiter, string $route): void
    {
        $securityLog->shouldHaveReceived('warning')
            ->once()
            ->with('Rate limit exceeded.', Mockery::on(
                fn (array $context): bool => $context['limiter'] === $limiter
                    && $context['route'] === $route
                    && $context['method'] === 'POST'
            ));
    }
}
