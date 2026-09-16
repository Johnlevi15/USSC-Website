<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_admin_dashboard_rejects_a_user_authenticated_with_the_web_guard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect();
    }
}
