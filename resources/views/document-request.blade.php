@extends('layouts.ussc')
@section('title', 'Request Document | CLSU USSC Portal')
@section('content')
	<div class="max-w-lg w-full mx-auto p-4 md:p-6">
		<div class="bg-white rounded-xl shadow-sm border p-6">
			<h2 class="text-lg font-bold mb-4 pb-2 border-b">Document Request Form</h2>

			<form method="POST" action="{{ route('document-request.store') }}" class="space-y-3">
				@csrf
				@foreach([
					['label' => 'Type of Document', 'name' => 'document_type', 'type' => 'text'],
					['label' => 'Full Name', 'name' => 'full_name', 'type' => 'text'],
					['label' => 'Student ID Number', 'name' => 'student_id', 'type' => 'text'],
					['label' => 'College / Department', 'name' => 'department', 'type' => 'text'],
					['label' => 'Year & Section', 'name' => 'year_section', 'type' => 'text'],
					['label' => 'Email Address', 'name' => 'email', 'type' => 'email'],
				] as $field)
					<label class="block text-xs font-bold text-gray-600 uppercase">
						{{ $field['label'] }}
						<input required type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
					</label>
				@endforeach

				<label class="block text-xs font-bold text-gray-600 uppercase">
					Purpose of Request
					<textarea required name="purpose" rows="3" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">{{ old('purpose') }}</textarea>
				</label>

				<button class="w-full py-2.5 bg-red-900 text-white font-bold rounded-lg text-sm">SUBMIT REQUEST</button>
			</form>
		</div>
	</div>
@endsection
