@extends('layouts.ussc')
@section('title', 'Track Request | CLSU USSC Portal')
@section('content')
    @php
        $statusLabels = [
            'pending' => ['Pending', 'bg-yellow-100 text-yellow-800 border-yellow-200', 'Your request has been received and is waiting for administrator review.'],
            'review' => ['Under Review', 'bg-blue-100 text-blue-800 border-blue-200', 'Your request is currently being reviewed by the USSC administration.'],
            'approved' => ['Approved', 'bg-green-100 text-green-800 border-green-200', 'Your request has been reviewed and approved.'],
            'ready' => ['Ready', 'bg-emerald-100 text-emerald-800 border-emerald-200', 'Your requested document is ready for the next step or release.'],
            'rejected' => ['Rejected', 'bg-red-100 text-red-800 border-red-200', 'Your request was not approved. Please contact the USSC office if you need clarification.'],
        ];
        $statusInfo = $trackedRequest ? ($statusLabels[$trackedRequest->status] ?? [ucfirst($trackedRequest->status), 'bg-gray-100 text-gray-700 border-gray-200', 'Please contact the USSC office for more information.']) : null;
    @endphp

    <div class="mx-auto w-full max-w-2xl p-4 md:p-6">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-900">
                    <i class="fa-solid fa-route"></i>
                </div>
                <h2 class="text-xl font-bold">Track Your Document Request</h2>
                <p class="mt-1 text-xs text-gray-500">No login is required. Enter the tracking number you received after submitting your request.</p>
            </div>

            <form method="GET" action="{{ route('track-request') }}" class="mx-auto flex max-w-md gap-2">
                <input
                    id="code"
                    name="code"
                    required
                    value="{{ $code }}"
                    placeholder="e.g. USSC-2026-0001"
                    class="flex-grow rounded-lg border px-3 py-2 text-sm uppercase focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900"
                >
                <button class="rounded-lg bg-red-900 px-5 py-2 text-sm font-bold text-white hover:bg-red-800">Track</button>
            </form>

            @if($trackingError)
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $trackingError }}
                </div>
            @endif

            @if($trackedRequest)
                <div class="mt-6 border-t pt-6">
                    <div class="rounded-lg border bg-gray-50 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-900">TRACKING NUMBER</span>
                                <p class="mt-2 text-sm font-bold">{{ $code }}</p>
                            </div>
                            <span class="inline-block rounded border px-2.5 py-1 text-xs font-semibold {{ $statusInfo[1] }}">{{ $statusInfo[0] }}</span>
                        </div>

                        <dl class="mt-4 grid grid-cols-1 gap-3 border-t pt-4 text-xs sm:grid-cols-2">
                            <div>
                                <dt class="font-bold uppercase text-gray-500">Document Type</dt>
                                <dd class="mt-1 text-gray-800">{{ $trackedRequest->documentType?->name ?? 'Document Request' }}</dd>
                            </div>
                            <div>
                                <dt class="font-bold uppercase text-gray-500">Submitted</dt>
                                <dd class="mt-1 text-gray-800">{{ $trackedRequest->submitted_at?->format('F j, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50 p-3 text-xs text-blue-900">
                        <p class="font-semibold">Current Status</p>
                        <p class="mt-1">{{ $statusInfo[2] }}</p>
                    </div>

                    @if(filled($trackedRequest->admin_remarks))
                        <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4 text-xs text-gray-800">
                            <p class="font-bold uppercase tracking-wide text-gray-500">Admin Remarks</p>
                            <p class="mt-2 whitespace-pre-line break-words leading-5">{{ $trackedRequest->admin_remarks }}</p>
                        </div>
                    @endif

                    <p class="mt-4 text-center text-[11px] text-gray-500">For privacy, only request status, admin remarks, and general document information are shown here.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
