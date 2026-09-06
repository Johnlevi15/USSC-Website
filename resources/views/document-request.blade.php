@extends('layouts.ussc')
@section('title', 'Request Document | CLSU USSC Portal')
@section('content')
	<div class="max-w-lg w-full mx-auto p-4 md:p-6">
		<div class="bg-white rounded-xl shadow-sm border p-6">
			<h2 class="text-lg font-bold mb-4 pb-2 border-b">Document Request Form</h2>

			<form onsubmit="handleDocumentRequestSubmit(event)" class="space-y-3">
				@foreach(['Type of Document', 'Full Name', 'Student ID Number', 'College / Department', 'Year & Section', 'Email Address'] as $label)
					<label class="block text-xs font-bold text-gray-600 uppercase">
						{{ $label }}
						<input required class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
					</label>
				@endforeach

				<label class="block text-xs font-bold text-gray-600 uppercase">
					Purpose of Request
					<textarea rows="3" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg"></textarea>
				</label>

				<button class="w-full py-2.5 bg-red-900 text-white font-bold rounded-lg text-sm">SUBMIT REQUEST</button>
			</form>
		</div>
	</div>
@endsection
@push('scripts')
	<script>
		function handleDocumentRequestSubmit(event) {
			event.preventDefault();
			const code = 'USSC-2026-' + Math.floor(1000 + Math.random() * 9000);
			const trackingUrl = @json(route('track-request')) + '?code=' + encodeURIComponent(code);

			showPortalModal(
				'Request Submitted',
				'Your request was submitted successfully. Tracking number: ' + code,
				'Track Request',
				trackingUrl
			);
		}
	</script>
@endpush