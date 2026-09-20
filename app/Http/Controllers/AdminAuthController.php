<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    private const MaxLoginAttempts = 5;

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user('admin') instanceof Admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin === null || ! Hash::check($credentials['password'], $admin->password_hash)) {
            $remainingAttempts = $this->remainingAdminLoginAttempts($request, $credentials['email']);

            $this->logFailedAdminLogin($request, $credentials['email'], $admin instanceof Admin, $remainingAttempts);

            return back()
                ->withErrors(['email' => $this->invalidCredentialsMessage()])
                ->onlyInput('email');
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        RateLimiter::clear($this->adminLoginThrottleKey($request, $credentials['email']));
        $request->session()->regenerate();
        AdminActivityLog::create([
            'admin_id' => $admin->admin_id,
            'action' => 'admin_login',
            'description' => 'Signed in to the admin portal.',
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');

        if ($admin instanceof Admin) {
            AdminActivityLog::create([
                'admin_id' => $admin->admin_id,
                'action' => 'admin_logout',
                'description' => 'Signed out of the admin portal.',
            ]);
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin-login');
    }

    private function invalidCredentialsMessage(): string
    {
        return 'The administrator credentials are invalid.';
    }

    private function remainingAdminLoginAttempts(Request $request, string $email): int
    {
        return RateLimiter::remaining(
            $this->adminLoginThrottleKey($request, $email),
            self::MaxLoginAttempts,
        );
    }

    private function logFailedAdminLogin(Request $request, string $email, bool $adminExists, int $remainingAttempts): void
    {
        Log::channel('security')->warning('Failed admin login attempt.', [
            'email' => Str::lower($email),
            'admin_exists' => $adminExists,
            'remaining_attempts' => $remainingAttempts,
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function adminLoginThrottleKey(Request $request, string $email): string
    {
        return md5('admin-login'.Str::lower($email).'|'.$request->ip());
    }
}
