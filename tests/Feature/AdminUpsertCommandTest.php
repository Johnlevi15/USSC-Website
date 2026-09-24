<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUpsertCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_an_admin_that_can_log_in(): void
    {
        $this->artisan('admin:create', [
            '--email' => 'Admin@Example.test',
            '--name' => 'Portal Admin',
            '--password' => 'secure-password',
        ])->assertSuccessful();

        $this->assertDatabaseHas('admins', [
            'email' => 'admin@example.test',
            'name' => 'Portal Admin',
        ]);

        $this->post(route('admin-login.store'), [
            'email' => 'admin@example.test',
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_it_resets_an_existing_admin_password(): void
    {
        $admin = Admin::create([
            'name' => 'Old Name',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('old-password'),
        ]);

        $this->artisan('admin:upsert', [
            '--email' => 'admin@example.test',
            '--name' => 'New Name',
            '--password' => 'new-secure-password',
        ])->assertSuccessful();

        $admin->refresh();

        $this->assertSame('New Name', $admin->name);
        $this->assertTrue(Hash::check('new-secure-password', $admin->password_hash));
        $this->assertFalse(Hash::check('old-password', $admin->password_hash));
    }

    public function test_the_legacy_upsert_alias_still_works(): void
    {
        $this->artisan('admin:upsert', [
            '--email' => 'admin@example.test',
            '--name' => 'Portal Admin',
            '--password' => 'secure-password',
        ])->assertSuccessful();

        $this->assertDatabaseHas('admins', [
            'email' => 'admin@example.test',
            'name' => 'Portal Admin',
        ]);
    }
}
