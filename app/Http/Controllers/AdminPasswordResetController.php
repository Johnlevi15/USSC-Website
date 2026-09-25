<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AdminPasswordResetController extends Controller
{
    public function create(): View
    {
        return view('admin-forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $validated['email'] = Str::lower($validated['email']);

        Password::broker('admins')->sendResetLink([
            'email' => $validated['email'],
        ]);

        return back()->with('status', $this->resetLinkStatusMessage());
    }

    public function edit(Request $request, string $token): View
    {
        return view('admin-reset-password', [
            'email' => $request->query('email'),
            'token' => $token,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $validated['email'] = Str::lower($validated['email']);

        $status = Password::broker('admins')->reset(
            $validated,
            function (Admin $admin, string $password): void {
                $admin->forceFill([
                    'password_hash' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($admin));
            },
        );

        return $status === Password::PasswordReset
            ? redirect()->route('admin-login')->with('status', 'Your password has been reset. You may now sign in.')
            : back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
    }

    private function resetLinkStatusMessage(): string
    {
        return 'If an administrator account exists for that email, a password reset link has been sent.';
    }
}
