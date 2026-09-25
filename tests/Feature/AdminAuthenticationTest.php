<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_log_in_without_a_user_account(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);

        $this->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_id' => $admin->admin_id,
            'action' => 'admin_login',
        ]);
    }

    public function test_admin_can_log_in_with_a_differently_cased_email(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);

        $this->post(route('admin-login.store'), [
            'email' => 'Admin@Example.test',
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_dashboard_rejects_a_user_authenticated_with_the_web_guard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect();
    }

    public function test_admin_pages_prevent_browser_caching(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');

        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
    }

    public function test_admin_layout_renders_burger_navigation_with_portal_and_logout_actions(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="admin-menu-toggle"', false)
            ->assertSee('aria-controls="admin-menu-panel"', false)
            ->assertSee('id="admin-menu-panel"', false)
            ->assertSeeText('Dashboard')
            ->assertSeeText('Document Requests')
            ->assertSeeText('Document Types')
            ->assertSeeText('Lost & Found')
            ->assertSeeText('Events')
            ->assertSeeText('Activity Logs')
            ->assertSeeText('View portal')
            ->assertSeeText('Logout')
            ->assertSee(route('admin-logout'), false);
    }

    public function test_invalid_admin_login_shows_a_generic_error_without_remaining_attempts(): void
    {
        Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);

        $this->from('/admin-login')->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])
            ->assertRedirect('/admin-login')
            ->assertSessionHasErrors([
                'email' => 'The administrator credentials are invalid.',
            ]);
    }

    public function test_failed_admin_login_is_audited_to_the_security_log(): void
    {
        Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);
        $securityLog = Mockery::spy(LoggerInterface::class);
        Log::shouldReceive('channel')
            ->once()
            ->with('security')
            ->andReturn($securityLog);

        $this->from('/admin-login')->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect('/admin-login');

        $securityLog->shouldHaveReceived('warning')
            ->once()
            ->with('Failed admin login attempt.', Mockery::on(
                fn (array $context): bool => $context['email'] === 'admin@example.test'
                    && $context['admin_exists'] === true
                    && $context['remaining_attempts'] === 4
                    && $context['route'] === 'admin-login.store'
                    && $context['method'] === 'POST'
                    && $context['path'] === 'admin-login'
                    && isset($context['ip'], $context['user_agent'])
                    && ! array_key_exists('password', $context)
            ));
    }

    public function test_successful_admin_login_clears_previous_failed_attempts(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secure-password'),
        ]);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->from('/admin-login')->post(route('admin-login.store'), [
                'email' => 'admin@example.test',
                'password' => 'wrong-password',
            ])->assertRedirect('/admin-login');
        }

        $this->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');

        Auth::guard('admin')->logout();
        $this->app['session']->invalidate();
        $this->app['session']->regenerateToken();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from('/admin-login')->post(route('admin-login.store'), [
                'email' => 'admin@example.test',
                'password' => 'wrong-password',
            ])->assertRedirect('/admin-login');
        }

        $this->from('/admin-login')->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }
}
