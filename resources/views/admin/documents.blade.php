@extends('layouts.admin')
@section('title', 'Document Requests | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Review Document Requests</h2>
    <p class="text-sm text-gray-500">Open a request to review the student's submitted information before updating its status.</p>
</div>

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="p-4">Requester</th>
                    <th class="p-4">Document</th>
                    <th class="p-4">Submitted</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="document-requests-table" class="divide-y">
                @include('admin.partials.document-request-rows', ['requests' => $requests])
            </tbody>
        </table>
    </div>
    <div class="border-t p-4">{{ $requests->links() }}</div>
</div>
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
            document.getElementById('document-requests-table').innerHTML = data.html;
        } catch (error) {
            // Keep the current table visible if a refresh fails.
        }
    }

    setInterval(refreshDocumentRequests, 10000);
</script>
@endpush
