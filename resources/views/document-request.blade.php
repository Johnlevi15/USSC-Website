@extends('layouts.ussc')
@section('title', 'Request Document | CLSU USSC Portal')
@section('content')
	<div class="max-w-lg w-full mx-auto p-4 md:p-6">
		<div class="bg-white rounded-xl shadow-sm border p-6">
			<h2 class="text-lg font-bold mb-4 pb-2 border-b">Document Request Form</h2>
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
        <p class="text-sm font-bold text-red-700 mb-1">
            Please check the following:
        </p>

        <ul class="list-disc pl-5 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
			<form method="POST" action="{{ route('document-request.store') }}" enctype="multipart/form-data" class="space-y-3">
				@csrf
				<label class="block text-xs font-bold text-gray-600 uppercase">
					Type of Document
					<select required name="document_type_id" id="document_type" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
						<option value="">Select document type...</option>
						@foreach($documentTypes as $type)
							<option value="{{ $type->id }}" data-description="{{ $type->description }}" @selected(old('document_type_id') == $type->id)>{{ $type->name }}</option>
						@endforeach
					</select>
				</label>

				<div id="type-description" class="text-sm text-gray-600 mt-1 hidden"></div>

				{{-- Dynamic fields loaded via AJAX --}}
				<div id="dynamic-fields" class="space-y-3"></div>

				<button id="submit-btn" disabled class="w-full py-2.5 bg-red-900 text-white font-bold rounded-lg text-sm disabled:bg-gray-400">SUBMIT REQUEST</button>
			</form>
		</div>
	</div>

	{{-- Success Modal --}}
	@if(session('success') && request('code'))
	<div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
		<div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 space-y-4">
			<div class="text-center">
				<div class="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3">
					<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
					</svg>
				</div>
				<h3 class="text-lg font-bold text-gray-900">Request Submitted Successfully!</h3>
				<p class="text-sm text-gray-600 mt-2">Your document request has been received.</p>
			</div>

			<div class="bg-gray-50 rounded-lg p-4 border">
				<p class="text-xs font-bold text-gray-600 uppercase mb-1">Tracking Number</p>
				<p class="text-lg font-bold text-red-900 font-mono">{{ request('code') }}</p>
				<p class="text-xs text-gray-500 mt-2">Save this number to track your request status</p>
			</div>

			<div class="flex flex-col gap-2 sm:flex-row">
				<button onclick="copyTracking()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg text-sm hover:bg-gray-200">
					Copy Number
				</button>
				<a href="{{ route('track-request', ['code' => request('code')]) }}" class="flex-1 px-4 py-2 bg-red-900 text-white font-bold rounded-lg text-sm text-center hover:bg-red-800">
					Track Now
				</a>
			</div>

			<button onclick="closeModal()" class="w-full text-sm text-gray-500 hover:text-gray-700">
				Close
			</button>
		</div>
	</div>
	@endif

@push('scripts')
<script>
document.getElementById('document_type').addEventListener('change', async function() {
    const typeId = this.value;
    const description = this.options[this.selectedIndex]?.dataset.description;
    const descriptionDiv = document.getElementById('type-description');
    const fieldsContainer = document.getElementById('dynamic-fields');
    const submitBtn = document.getElementById('submit-btn');

    if (description) {
        descriptionDiv.textContent = description;
        descriptionDiv.classList.remove('hidden');
    } else {
        descriptionDiv.classList.add('hidden');
    }

    fieldsContainer.innerHTML = '';
    submitBtn.disabled = !typeId;

    if (!typeId) return;

    try {
        const response = await fetch(`/document-types/${typeId}/fields`);
        const fields = await response.json();

        fields.forEach(field => {
            const wrapper = document.createElement(field.field_type === 'checkbox' ? 'div' : 'label');
            wrapper.className = 'block text-xs font-bold text-gray-600 uppercase';

            const labelSpan = document.createElement('span');
            labelSpan.textContent = field.field_label + (field.is_required ? '' : ' (Optional)');
            wrapper.appendChild(labelSpan);

            if (field.field_type === 'checkbox') {
                const options = (field.field_options && field.field_options.length)
                    ? field.field_options
                    : [field.field_label];
                const hasOther = options.includes('Other');

                const optionsWrapper = document.createElement('div');
                optionsWrapper.className = 'mt-2 space-y-2 normal-case';

                options.forEach(opt => {
                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'flex items-center gap-2 text-sm font-medium text-gray-700';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = field.field_name + '[]';
                    checkbox.value = opt;
                    checkbox.className = 'h-4 w-4 rounded border-gray-300 text-red-900 focus:ring-red-900';

                    const optionText = document.createElement('span');
                    optionText.textContent = opt;

                    optionLabel.appendChild(checkbox);
                    optionLabel.appendChild(optionText);
                    optionsWrapper.appendChild(optionLabel);
                });

                wrapper.appendChild(optionsWrapper);

                if (hasOther) {
                    const otherInput = document.createElement('input');
                    otherInput.type = 'text';
                    otherInput.name = field.field_name + '_other';
                    otherInput.placeholder = 'Please specify';
                    otherInput.className = 'mt-2 hidden w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case';

                    optionsWrapper.addEventListener('change', () => {
                        const otherCheckbox = optionsWrapper.querySelector(`input[value="Other"]`);
                        const showOther = otherCheckbox?.checked;

                        otherInput.classList.toggle('hidden', ! showOther);
                        otherInput.required = Boolean(showOther);

                        if (! showOther) {
                            otherInput.value = '';
                        }
                    });

                    wrapper.appendChild(otherInput);
                }

                fieldsContainer.appendChild(wrapper);

                return;
            }

            let input;
            if (field.field_type === 'textarea') {
                input = document.createElement('textarea');
                input.rows = 3;
            } else if (field.field_type === 'select') {
                input = document.createElement('select');
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Select...';
                input.appendChild(emptyOption);
                (field.field_options || []).forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt;
                    option.textContent = opt;
                    input.appendChild(option);
                });
            } else {
                input = document.createElement('input');
                input.type = field.field_type;

                if (field.field_type === 'file') {
                    input.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
                }

                if (field.field_type === 'image') {
                    input.accept = 'image/jpeg,image/png,image/webp';
                }
            }

            input.name = field.field_name;
            input.required = field.is_required;
            input.className = 'mt-1 w-full px-3 py-2 text-sm border rounded-lg';

            wrapper.appendChild(input);

            if (field.field_type === 'select' && (field.field_options || []).includes('Other')) {
                const otherInput = document.createElement('input');
                otherInput.type = 'text';
                otherInput.name = field.field_name + '_other';
                otherInput.placeholder = 'Please specify';
                otherInput.className = 'mt-2 hidden w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case';

                input.addEventListener('change', () => {
                    const showOther = input.value === 'Other';

                    otherInput.classList.toggle('hidden', ! showOther);
                    otherInput.required = showOther;

                    if (! showOther) {
                        otherInput.value = '';
                    }
                });

                wrapper.appendChild(otherInput);
            }

            fieldsContainer.appendChild(wrapper);
        });
    } catch (err) {
        fieldsContainer.innerHTML = '<p class="text-sm text-red-600">Failed to load form fields.</p>';
    }
});

// Restore selection after validation error
if (document.getElementById('document_type').value) {
    document.getElementById('document_type').dispatchEvent(new Event('change'));
}

// Success modal functions
function copyTracking() {
    const trackingCode = '{{ request("code") }}';
    navigator.clipboard.writeText(trackingCode).then(() => {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('bg-green-100', 'text-green-700');
        btn.classList.remove('bg-gray-100', 'text-gray-700');
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('bg-green-100', 'text-green-700');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        }, 2000);
    });
}

function closeModal() {
    document.getElementById('success-modal').remove();
}
</script>
@endpush
@endsection
