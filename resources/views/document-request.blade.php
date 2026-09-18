@extends('layouts.ussc')
@section('title', 'Request Document | CLSU USSC Portal')
@section('content')
{{-- Data Privacy Notice Modal --}}
<div
    id="privacy-modal"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="privacy-title"
>
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">

        {{-- Header --}}
        <div class="bg-red-900 px-6 py-4 text-white">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400 text-red-900">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div>
                    <h2 id="privacy-title" class="text-lg font-bold">
                        Data Privacy Notice
                    </h2>

                    <p class="text-xs text-red-100">
                        Document Request Service
                    </p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="max-h-[70vh] overflow-y-auto p-6">

            <p class="mb-4 text-sm leading-6 text-gray-700">
                The Central Luzon State University – University Supreme
                Student Council (CLSU-USSC) respects and protects the privacy
                of students who use this document request service.
            </p>

            <p class="mb-4 text-sm leading-6 text-gray-700">
                To process your request, the system may collect personal
                information such as your name, email address, student ID
                number, college or department, year and section, purpose of
                request, and other information required for the document you
                are requesting.
            </p>

            <p class="mb-4 text-sm leading-6 text-gray-700">
                The information you provide will be used only for receiving,
                verifying, processing, tracking, and completing your document
                request. Access to this information should be limited to
                authorized personnel responsible for handling the request.
            </p>

            <p class="mb-5 text-sm leading-6 text-gray-700">
                By continuing, you acknowledge that the information you
                provide may be collected and processed in accordance with
                Republic Act No. 10173, also known as the
                <strong>Data Privacy Act of 2012</strong>, and applicable
                university policies.
            </p>

            <div class="space-y-4 border-t pt-5">

                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        id="privacy-agree"
                        type="checkbox"
                        class="mt-1 h-4 w-4 accent-red-900"
                    >

                    <span class="text-sm text-gray-700">
                        <strong>I Agree and Understand.</strong>
                        I have read and understood the Data Privacy Notice
                        and agree to the collection and processing of the
                        information necessary for my document request.
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        id="privacy-confirm"
                        type="checkbox"
                        class="mt-1 h-4 w-4 accent-red-900"
                    >

                    <span class="text-sm text-gray-700">
                        <strong>I Confirm.</strong>
                        I am submitting this request using my own information,
                        and the details I provide are true and accurate to the
                        best of my knowledge.
                    </span>
                </label>

            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex flex-col-reverse gap-2 border-t bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">

            <a
                href="{{ route('home') }}"
                class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-bold text-gray-600 transition hover:bg-gray-100"
            >
                Cancel
            </a>

            <button
                id="privacy-continue"
                type="button"
                disabled
                class="rounded-lg bg-red-900 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-gray-400"
            >
                I Agree & Continue
            </button>

        </div>
    </div>
</div>
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
			<form method="POST" action="{{ route('document-request.store') }}" class="space-y-3">
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
<label class="block text-xs font-bold text-gray-600 uppercase">
    Full Name
    <input
        type="text"
        name="full_name"
        value="{{ old('full_name') }}"
        required
        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"
    >
</label>

<label class="block text-xs font-bold text-gray-600 uppercase">
    Email Address
    <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        required
        class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"
    >
</label>
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

			<div class="flex gap-2">
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
    // ---------------------------------------------------------
// DATA PRIVACY NOTICE
// ---------------------------------------------------------

const privacyStorageKey = 'ussc_document_privacy_accepted_v1';

const privacyModal = document.getElementById('privacy-modal');
const privacyAgree = document.getElementById('privacy-agree');
const privacyConfirm = document.getElementById('privacy-confirm');
const privacyContinue = document.getElementById('privacy-continue');

function updatePrivacyButton() {
    privacyContinue.disabled = !(
        privacyAgree.checked &&
        privacyConfirm.checked
    );
}

privacyAgree.addEventListener('change', updatePrivacyButton);
privacyConfirm.addEventListener('change', updatePrivacyButton);

privacyContinue.addEventListener('click', function () {

    if (!privacyAgree.checked || !privacyConfirm.checked) {
        return;
    }

    localStorage.setItem(
        privacyStorageKey,
        'accepted'
    );

    privacyModal.classList.add('hidden');
    privacyModal.classList.remove('flex');
});

document.addEventListener('DOMContentLoaded', function () {

    const hasAcceptedPrivacy =
        localStorage.getItem(privacyStorageKey) === 'accepted';

    if (!hasAcceptedPrivacy) {
        privacyModal.classList.remove('hidden');
        privacyModal.classList.add('flex');
    }
});
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
        if (['full_name', 'email'].includes(field.field_name)) {
    return;
}
            const wrapper = document.createElement('label');
            wrapper.className = 'block text-xs font-bold text-gray-600 uppercase';

            const labelSpan = document.createElement('span');
            labelSpan.textContent = field.field_label + (field.is_required ? '' : ' (Optional)');
            wrapper.appendChild(labelSpan);

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
            }

            input.name = field.field_name;
            input.required = field.is_required;
            input.className = 'mt-1 w-full px-3 py-2 text-sm border rounded-lg';

            wrapper.appendChild(input);
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
