@extends('layouts.admin-auth')
@section('title', 'Admin Login | CLSU USSC Portal')
@section('content')
    <div class="w-full rounded-2xl border bg-white p-6 shadow-sm">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-xl text-red-900">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2 class="text-xl font-bold">Administrator Sign In</h2>
            <p class="mt-1 text-xs text-gray-500">Sign in to access the protected management dashboard.</p>
        </div>

        <form method="POST" action="{{ route('admin-login.store') }}" class="space-y-4">
            @csrf

            <label class="block text-xs font-bold uppercase text-gray-600">
                Email Address
                <input
                    required
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    placeholder="administrator@clsu2.edu.ph"
                    class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm normal-case focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900"
                >
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                Password
                <input
                    required
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm normal-case focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900"
                >
            </label>

            <label class="flex items-center gap-2 text-xs text-gray-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-red-900 focus:ring-red-900">
                Keep me signed in on this device
            </label>

            <button class="w-full rounded-lg bg-red-900 py-2.5 text-sm font-bold text-white transition hover:bg-red-800">
                <i class="fa-solid fa-right-to-bracket mr-1"></i> LOG IN
            </button>

            @if($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-center text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>
@endsection
