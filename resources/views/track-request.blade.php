@extends('layouts.ussc')
@section('title', 'Track Request | CLSU USSC Portal')
@section('content')
	<div class="max-w-2xl w-full mx-auto p-4 md:p-6">
		<div class="bg-white rounded-xl shadow-sm border p-6">
			<div class="text-center mb-6">
				<h2 class="text-xl font-bold">Track Your Document Request</h2>
				<p class="text-xs text-gray-500 mt-1">
					Enter your tracking number to check the current status.
				</p>
			</div>

			<form
				class="flex gap-2 max-w-md mx-auto"
				onsubmit="event.preventDefault(); document.getElementById('display').textContent = document.getElementById('code').value.toUpperCase(); document.getElementById('result').classList.remove('hidden');"
			>
				<input
					id="code"
					required
					value="{{ request('code') }}"
					placeholder="e.g. USSC-2026-8942"
					class="flex-grow px-3 py-2 text-sm border rounded-lg uppercase"
				>
				<button class="px-5 py-2 bg-red-900 text-white font-bold rounded-lg text-sm">
					Track
				</button>
			</form>

			<div id="result" class="{{ request('code') ? '' : 'hidden' }} border-t mt-6 pt-6">
				<div class="bg-gray-50 p-4 rounded-lg border">
					<span class="text-[10px] font-bold text-red-900 bg-red-100 px-2 py-0.5 rounded">
						DOCUMENT STATUS
					</span>
					<p class="font-bold text-sm mt-2" id="display">
						{{ strtoupper(request('code')) }}
					</p>
					<span class="inline-block mt-2 text-xs font-semibold px-2.5 py-1 rounded bg-yellow-100 text-yellow-800 border border-yellow-200">
						Processing
					</span>
				</div>

				<div class="mt-5 bg-blue-50 border border-blue-200 text-blue-900 text-xs p-3 rounded-lg">
					<p class="font-semibold">Current Remark:</p>
					<p>Your document is currently under review by the USSC administration.</p>
				</div>
			</div>
		</div>
	</div>
@endsection