@extends('layouts.admin-auth')
@section('title', 'Reset Admin Password | CLSU USSC Portal')
@section('content')
    <div class="w-full rounded-2xl border bg-white p-6 shadow-sm">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-xl text-red-900">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 class="text-xl font-bold">Reset Password</h2>
            <p class="mt-1 text-xs text-gray-500">Choose a new password for your administrator account.</p>
        </div>

        <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="block text-xs font-bold uppercase text-gray-600">
                Email Address
                <input
                    required
                    name="email"
                    type="email"
                    value="{{ old('email', $email) }}"
                    autocomplete="username"
                    placeholder="administrator@clsu2.edu.ph"
                    class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm normal-case focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900"
                >
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                New Password
                <input
                    required
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Enter a new password"
                    class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm normal-case focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900"
                >
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                Confirm Password
                <input
                    required
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Confirm your new password"
                    class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm normal-case focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900"
                >
            </label>

            <button class="w-full rounded-lg bg-red-900 py-2.5 text-sm font-bold text-white transition hover:bg-red-800">
                <i class="fa-solid fa-check mr-1"></i> RESET PASSWORD
            </button>

            @if($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-center text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>
@endsection
