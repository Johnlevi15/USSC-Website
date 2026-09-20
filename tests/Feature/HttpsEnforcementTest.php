<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HttpsEnforcementTest extends TestCase
{
    public function test_production_http_requests_redirect_to_https(): void
    {
        $this->app['env'] = 'production';

        $this->get('http://example.test/admin-login?next=dashboard')
            ->assertRedirect('https://example.test/admin-login?next=dashboard');
    }

    public function test_non_production_http_requests_are_not_forced_to_https(): void
    {
        $this->app['env'] = 'local';

        $this->get('http://example.test/admin-login')
            ->assertOk();
    }

    public function test_production_request_trusts_forwarded_https_only_from_configured_proxy(): void
    {
        $this->app['env'] = 'production';
        config(['trustedproxy.proxies' => ['10.0.0.1']]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.25',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('http://example.test/admin-login')
            ->assertOk();
    }

    public function test_production_request_ignores_forwarded_https_from_untrusted_proxy(): void
    {
        $this->app['env'] = 'production';
        config(['trustedproxy.proxies' => ['10.0.0.1']]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.25',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('http://example.test/admin-login')
            ->assertRedirect('https://example.test/admin-login');
    }

    public function test_production_generated_urls_use_https(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://example.test']);

        try {
            (new AppServiceProvider($this->app))->boot();

            $logoUrl = asset('logo.png');

            $this->assertStringStartsWith('https://', $logoUrl);
            $this->assertStringEndsWith('/logo.png', $logoUrl);
        } finally {
            URL::forceScheme(null);
        }
    }
}
