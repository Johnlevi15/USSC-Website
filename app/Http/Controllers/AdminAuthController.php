<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View
    {
        return view('admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::with('user')
            ->whereHas('user', fn ($query) => $query->where('email', $credentials['email']))
            ->first();

        if ($admin === null || ! Hash::check($credentials['password'], $admin->password_hash)) {
            return back()->withErrors(['email' => 'The administrator credentials are invalid.'])->onlyInput('email');
        }

        Auth::login($admin->user, $request->boolean('remember'));
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
        if ($request->user()?->admin !== null) {
            AdminActivityLog::create([
                'admin_id' => $request->user()->admin->admin_id,
                'action' => 'admin_logout',
                'description' => 'Signed out of the admin portal.',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin-login');
    }
}
