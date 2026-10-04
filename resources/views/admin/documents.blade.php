@extends('layouts.admin')
@section('title', 'Document Requests | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Review Document Requests</h2>
    <p class="text-sm text-gray-500">Review pending requests, then manage processed requests separately.</p>
</div>

<section class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold">Pending Requests</h3>
            <p class="text-xs text-gray-500">These requests are waiting for review.</p>
        </div>
        <span id="pending-document-request-count" class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">{{ $pendingRequests->count() }} pending</span>
    </div>

    <div id="pending-document-requests" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @include('admin.partials.document-request-list', [
            'requests' => $pendingRequests,
            'emptyMessage' => 'No pending document requests.',
        ])
    </div>
</section>

<section class="mt-8 space-y-4 border-t border-gray-200 pt-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold">Processed Requests</h3>
            <p class="text-xs text-gray-500">Requests that are under review, approved, ready, or rejected.</p>
        </div>
        <span id="processed-document-request-count" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ $processedRequests->count() }} processed</span>
    </div>

    <div id="processed-document-requests" class="space-y-3">
        @include('admin.partials.document-request-list', [
            'requests' => $processedRequests,
            'emptyMessage' => 'No processed document requests yet.',
        ])
    </div>
</section>
@endsection

@push('scripts')
<script>
    async function refreshDocumentRequests() {
        try {
            const response = await fetch('{{ route('admin.documents.live') }}', {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            document.getElementById('pending-document-requests').innerHTML = data.pending_html;
            document.getElementById('processed-document-requests').innerHTML = data.processed_html;
            document.getElementById('pending-document-request-count').textContent = `${data.pending_count} pending`;
            document.getElementById('processed-document-request-count').textContent = `${data.processed_count} processed`;
        } catch (error) {
            // Keep the current sections visible if a refresh fails.
        }
    }

    setInterval(refreshDocumentRequests, 10000);
</script>
@endpush
