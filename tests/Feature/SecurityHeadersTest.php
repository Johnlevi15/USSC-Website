<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_responses_include_browser_security_headers(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $contentSecurityPolicy);
        $this->assertStringContainsString("frame-ancestors 'none'", $contentSecurityPolicy);
        $this->assertStringContainsString("object-src 'none'", $contentSecurityPolicy);
        $this->assertStringContainsString('https://cdn.tailwindcss.com', $contentSecurityPolicy);
        $this->assertStringContainsString('https://cdnjs.cloudflare.com', $contentSecurityPolicy);
        $this->assertStringContainsString('frame-src https://www.google.com', $contentSecurityPolicy);
    }

    public function test_production_https_responses_include_hsts(): void
    {
        $this->app['env'] = 'production';

        $this->get('https://example.test/')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_non_production_responses_do_not_include_hsts(): void
    {
        $this->app['env'] = 'local';

        $this->get('https://example.test/')
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }
}
