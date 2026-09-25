<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_login_page_shows_forgot_password_link(): void
    {
        $this->get(route('admin-login'))
            ->assertSeeText('Forgot Password?')
            ->assertSee(route('admin.password.request'), false);
    }

    public function test_admin_can_request_a_password_reset_email(): void
    {
        Notification::fake();
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('old-password'),
        ]);

        $this->from(route('admin.password.request'))
            ->post(route('admin.password.email'), [
                'email' => 'Admin@Example.test',
            ])
            ->assertRedirect(route('admin.password.request'))
            ->assertSessionHas('status', 'If an administrator account exists for that email, a password reset link has been sent.');

        Notification::assertSentTo(
            $admin,
            AdminResetPasswordNotification::class,
            function (AdminResetPasswordNotification $notification) use ($admin): bool {
                $mail = $notification->toMail($admin);

                return $mail->subject === 'Reset your USSC admin password'
                    && $mail->actionText === 'Reset Admin Password'
                    && str_contains($mail->actionUrl, route('admin.password.reset', $notification->token, false))
                    && str_contains($mail->actionUrl, 'email=admin%40example.test');
            },
        );
    }

    public function test_unknown_admin_email_receives_the_same_reset_request_response(): void
    {
        Notification::fake();

        $this->from(route('admin.password.request'))
            ->post(route('admin.password.email'), [
                'email' => 'missing@example.test',
            ])
            ->assertRedirect(route('admin.password.request'))
            ->assertSessionHas('status', 'If an administrator account exists for that email, a password reset link has been sent.');

        Notification::assertNothingSent();
    }

    public function test_reset_form_opens_with_a_valid_token(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('old-password'),
        ]);
        $token = Password::broker('admins')->createToken($admin);

        $this->get(route('admin.password.reset', [
            'token' => $token,
            'email' => 'admin@example.test',
        ]))
            ->assertSeeText('Reset Password')
            ->assertSee('value="'.$token.'"', false)
            ->assertSee('value="admin@example.test"', false);
    }

    public function test_admin_can_reset_password_and_log_in_with_the_new_password(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('old-password'),
        ]);
        $token = Password::broker('admins')->createToken($admin);

        $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => 'Admin@Example.test',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
            ->assertRedirect(route('admin-login'))
            ->assertSessionHas('status', 'Your password has been reset. You may now sign in.');

        $this->assertTrue(Hash::check('new-secure-password', $admin->fresh()->password_hash));

        $this->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'new-secure-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin->fresh(), 'admin');
    }

    public function test_invalid_reset_token_does_not_update_the_admin_password(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('old-password'),
        ]);

        $this->from(route('admin.password.reset', [
            'token' => 'invalid-token',
            'email' => 'admin@example.test',
        ]))
            ->post(route('admin.password.update'), [
                'token' => 'invalid-token',
                'email' => 'admin@example.test',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('admin.password.reset', [
                'token' => 'invalid-token',
                'email' => 'admin@example.test',
            ]))
            ->assertSessionHasErrors([
                'email' => 'This password reset link is invalid or has expired.',
            ]);

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password_hash));
    }

    public function test_expired_reset_token_does_not_update_the_admin_password(): void
    {
        $this->travelTo('2026-09-25 09:00:00');
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('old-password'),
        ]);
        $token = Password::broker('admins')->createToken($admin);

        $this->travelTo('2026-09-25 10:01:00');

        $this->from(route('admin.password.reset', [
            'token' => $token,
            'email' => 'admin@example.test',
        ]))
            ->post(route('admin.password.update'), [
                'token' => $token,
                'email' => 'admin@example.test',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('admin.password.reset', [
                'token' => $token,
                'email' => 'admin@example.test',
            ]))
            ->assertSessionHasErrors([
                'email' => 'This password reset link is invalid or has expired.',
            ]);

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password_hash));
    }
}
