@extends('layouts.admin')
@section('title', 'Review Document Request | USSC Admin')
@section('content')
@php
    $typeFields = $documentRequest->documentType?->fields?->keyBy('field_name') ?? collect();
@endphp

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('admin.documents') }}" class="mb-2 inline-flex items-center gap-2 text-xs font-bold text-red-900 hover:underline">
            <i class="fa-solid fa-arrow-left"></i>
            Back to document requests
        </a>
        <h2 class="text-2xl font-extrabold text-gray-900">Review Document Request</h2>
        <p class="text-sm text-gray-500">Review the submitted details before changing the processing status.</p>
    </div>

    <span @class([
        'w-fit rounded-full px-3 py-1.5 text-xs font-bold uppercase',
        'bg-green-100 text-green-800' => $documentRequest->status === 'ready',
        'bg-red-100 text-red-800' => $documentRequest->status === 'rejected',
        'bg-blue-100 text-blue-800' => in_array($documentRequest->status, ['review', 'approved']),
        'bg-yellow-100 text-yellow-800' => $documentRequest->status === 'pending',
    ])>
        {{ $documentRequest->status === 'review' ? 'Under Review' : $documentRequest->status }}
    </span>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Request Information</h3>
            </div>
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Request Number</p>
                    <p class="mt-1 font-semibold text-gray-900">#{{ $documentRequest->request_id }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Document Type</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $documentRequest->documentType?->name ?? 'Unknown type' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Submitted</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $documentRequest->submitted_at?->format('F j, Y g:i A') ?? 'Not available' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Reviewed By</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $documentRequest->reviewer?->name ?? 'Not reviewed yet' }}</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Student Information</h3>
            </div>
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Full Name</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $documentRequest->user?->name ?? 'Unknown user' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Email Address</p>
                    <p class="mt-1 break-all text-sm text-gray-700">{{ $documentRequest->user?->email ?? 'Not available' }}</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Submitted Form Details</h3>
            </div>

            @if($documentRequest->fields->isNotEmpty())
                <div class="divide-y">
                    @foreach($documentRequest->fields as $field)
                        @php
                            $definition = $typeFields->get($field->field_name);
                            $label = $definition?->field_label ?? \Illuminate\Support\Str::headline($field->field_name);
                        @endphp
                        <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-[220px_1fr] sm:gap-6">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="whitespace-pre-line break-words text-sm text-gray-900">{{ $field->field_value }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="p-6 text-center text-sm text-gray-500">No additional form details were submitted.</p>
            @endif
        </section>
    </div>

    <aside class="lg:col-span-1">
        <div class="sticky top-32 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Review Decision</h3>
                <p class="mt-1 text-xs text-gray-500">Update the request after checking all submitted details.</p>
            </div>

            <form method="POST" action="{{ route('admin.documents.update', $documentRequest) }}" class="space-y-4 p-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-600">Request Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-red-800 focus:ring-2 focus:ring-red-100">
                        <option value="pending" @selected(old('status', $documentRequest->status) === 'pending')>Pending</option>
                        <option value="review" @selected(old('status', $documentRequest->status) === 'review')>Under Review</option>
                        <option value="approved" @selected(old('status', $documentRequest->status) === 'approved')>Approved</option>
                        <option value="ready" @selected(old('status', $documentRequest->status) === 'ready')>Ready for Pickup</option>
                        <option value="rejected" @selected(old('status', $documentRequest->status) === 'rejected')>Rejected</option>
                    </select>
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-2">
                        <label for="admin_remarks" class="block text-xs font-bold uppercase tracking-wide text-gray-600">Admin Remarks</label>
                        <span id="remarksRequirement" class="hidden text-[10px] font-bold uppercase text-red-700">Required for rejection</span>
                    </div>
                    <textarea
                        id="admin_remarks"
                        name="admin_remarks"
                        rows="5"
                        maxlength="2000"
                        placeholder="Add instructions, approval notes, or the reason for rejection..."
                        class="w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none focus:border-red-800 focus:ring-2 focus:ring-red-100"
                    >{{ old('admin_remarks', $documentRequest->admin_remarks) }}</textarea>
                    <p class="mt-1 text-[11px] text-gray-500">Students can see these remarks on the tracking page. A reason is required when the request is rejected.</p>
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <input
                        type="checkbox"
                        name="notify_student"
                        value="1"
                        @checked(old('notify_student'))
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-red-900 focus:ring-red-800"
                    >
                    <span>
                        <span class="block text-xs font-bold text-gray-800">Email the student about this update</span>
                        <span class="mt-0.5 block text-[11px] leading-4 text-gray-500">The email includes the new status, tracking number, and your remarks.</span>
                    </span>
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Review
                </button>
            </form>
        </div>
    </aside>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const status = document.getElementById('status');
        const remarks = document.getElementById('admin_remarks');
        const requirement = document.getElementById('remarksRequirement');

        const syncRemarksRequirement = () => {
            const rejected = status.value === 'rejected';
            remarks.required = rejected;
            requirement.classList.toggle('hidden', !rejected);
        };

        status.addEventListener('change', syncRemarksRequirement);
        syncRemarksRequirement();
    });
</script>
@endpush
@endsection
