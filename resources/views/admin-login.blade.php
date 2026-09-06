@extends('layouts.ussc')
@section('title', 'Admin Login | CLSU USSC Portal')
@section('content')
	<div class="flex-grow flex items-center justify-center p-4 min-h-[70vh]">
		<div class="bg-white rounded-xl shadow-sm border p-6 w-full max-w-md">
			<div class="text-center mb-6">
				<div class="w-14 h-14 rounded-full bg-red-100 text-red-900 flex items-center justify-center mx-auto mb-3 text-xl">
					<i class="fa-solid fa-user-shield"></i>
				</div>
				<h2 class="text-xl font-bold">Admin Login</h2>
				<p class="text-xs text-gray-500 mt-1">Authorized USSC administrators only.</p>
			</div>

			<form method="POST" action="{{ route('admin-login.store') }}" class="space-y-4">
				@csrf
				<label class="block text-xs font-bold text-gray-600 uppercase">
					Email Address
					<input required name="email" type="email" value="{{ old('email') }}" placeholder="admin@clsu.edu.ph" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
				</label>

				<label class="block text-xs font-bold text-gray-600 uppercase">
					Password
					<input required name="password" type="password" placeholder="Enter your password" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
				</label>

				<button class="w-full py-2.5 bg-red-900 text-white font-bold rounded-lg text-sm">LOG IN</button>
				@if($errors->any())
					<p class="text-center text-xs text-red-700">{{ $errors->first() }}</p>
				@endif
			</form>
		</div>
	</div>
@endsection