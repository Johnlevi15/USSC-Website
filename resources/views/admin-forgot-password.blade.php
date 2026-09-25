@extends('layouts.admin-auth')
@section('title', 'Forgot Admin Password | CLSU USSC Portal')
@section('content')
    <div class="w-full rounded-2xl border bg-white p-6 shadow-sm">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-xl text-red-900">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="text-xl font-bold">Forgot Password</h2>
            <p class="mt-1 text-xs text-gray-500">Enter your administrator email address to receive a reset link.</p>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-center text-xs text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4">
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

            <button class="w-full rounded-lg bg-red-900 py-2.5 text-sm font-bold text-white transition hover:bg-red-800">
                <i class="fa-solid fa-paper-plane mr-1"></i> SEND RESET LINK
            </button>

            <p class="text-center text-xs">
                <a href="{{ route('admin-login') }}" class="font-semibold text-red-900 hover:text-red-700">Back to sign in</a>
            </p>

            @if($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-center text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>
@endsection
