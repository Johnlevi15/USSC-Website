@extends('layouts.ussc')
@section('title', 'Report Item | CLSU USSC Portal')
@section('content')
	<div class="max-w-lg w-full mx-auto p-4 md:p-6">
		<div class="bg-white rounded-xl shadow-sm border p-6">
			<h2 class="text-lg font-bold">Report Item</h2>
			<p class="text-xs text-gray-500 mb-4 pb-2 border-b">Submit a report for a lost or found item.</p>

			<form onsubmit="handleReportSubmit(event)" class="space-y-4">
				<label class="block text-xs font-bold text-gray-600 uppercase">
					<span id="name-label">Name</span>
					<input required id="name" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
				</label>

                <label class="block text-xs font-bold text-gray-600 uppercase">
					Item Title / Name
					<input required class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
				</label>

				<label class="block text-xs font-bold text-gray-600 uppercase">
					Category
					<select required class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
						<option value="">Select Item Category...</option>
						<option>Cellphone</option>
                        <option>Gadgets</option>
                        <option>Earphones</option>
						<option>ID</option>
                        <option>Wallet</option>
						<option>Bags</option>
                        <option>Tumblers</option>
                        <option>Others</option>
					</select>
				</label>

                <label class="block text-xs font-bold text-gray-600 uppercase">
					Description
					<textarea rows="3" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg"></textarea>
				</label>

                <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-lg">
					<button type="button" onclick="setType('found')" id="found" class="py-2.5 rounded-md bg-red-900 text-white text-xs font-bold">REPORT FOUND</button>
					<button type="button" onclick="setType('lost')" id="lost" class="py-2.5 rounded-md text-gray-600 text-xs font-bold">REPORT LOST</button>
				</div>

				<label class="block text-xs font-bold text-gray-600 uppercase">
					<span id="place-label">Place Found</span>
					<input required id="place" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
				</label>

				<label class="block text-xs font-bold text-gray-600 uppercase">
					Contact Number
					<input required type="tel" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
				</label>

				<button id="submit" class="w-full py-2.5 bg-red-900 text-white font-bold rounded-lg text-sm">SUBMIT FOUND ITEM REPORT</button>
			</form>
		</div>
	</div>
@endsection
@push('scripts')
	<script>
		function handleReportSubmit(event) {
			event.preventDefault();
			showPortalModal(
				'Report Submitted',
				'Your lost-and-found item report was submitted successfully.',
				'View Lost & Found',
				@json(route('lost-found'))
			);
		}

		function setType(type) {
			const lost = type === 'lost';
			document.getElementById('found').className = lost
				? 'py-2.5 rounded-md text-gray-600 text-xs font-bold'
				: 'py-2.5 rounded-md bg-red-900 text-white text-xs font-bold';
			document.getElementById('lost').className = lost
				? 'py-2.5 rounded-md bg-yellow-600 text-white text-xs font-bold'
				: 'py-2.5 rounded-md text-gray-600 text-xs font-bold';
			document.getElementById('place-label').textContent = lost
				? 'Location (Where Lost)'
				: 'Location (Place Found)';
			document.getElementById('submit').textContent = lost
				? 'SUBMIT LOST ITEM REPORT'
				: 'SUBMIT FOUND ITEM REPORT';
		}

		if (new URLSearchParams(location.search).get('type') === 'lost') {
			setType('lost');
		}
	</script>
@endpush