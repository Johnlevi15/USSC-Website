@extends('layouts.admin')
@section('title', 'Lost & Found Desk | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Approve Lost & Found Reports</h2>
    <p class="text-sm text-gray-500">Review pending reports, then manage approved or rejected items separately.</p>
</div>

@if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="flex justify-end">
    <a href="{{ route('admin.lost-found.archive') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
        <i class="fa-solid fa-box-archive"></i>
        Archive
    </a>
</div>

<section class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold">Pending Reports</h3>
            <p class="text-xs text-gray-500">These reports are not visible publicly yet.</p>
        </div>
        <span id="pending-lost-found-count" class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">{{ $pendingItems->count() }} pending</span>
    </div>

    <div id="pending-lost-found-items" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse($pendingItems as $item)
            @include('admin.partials.lost-found-item', ['item' => $item])
        @empty
            <p class="col-span-full rounded-xl border bg-white p-10 text-center text-sm text-gray-500">No pending reports.</p>
        @endforelse
    </div>
</section>

<section class="mt-8 space-y-4 border-t border-gray-200 pt-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold">Approved / Rejected Reports</h3>
            <p class="text-xs text-gray-500">Use Edit to change approval or item state.</p>
        </div>
        <span id="processed-lost-found-count" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ $processedItems->count() }} processed</span>
    </div>

    <div id="processed-lost-found-items" class="space-y-3">
        @forelse($processedItems as $item)
            @include('admin.partials.lost-found-processed-item', ['item' => $item])
        @empty
            <p class="rounded-xl border bg-white p-10 text-center text-sm text-gray-500">No approved or rejected reports yet.</p>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>
    async function refreshLostFoundDesk() {
        try {
            const response = await fetch('{{ route('admin.lost-found.live') }}', {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            document.getElementById('pending-lost-found-items').innerHTML = data.pending_html;
            document.getElementById('processed-lost-found-items').innerHTML = data.processed_html;
            document.getElementById('pending-lost-found-count').textContent = `${data.pending_count} pending`;
            document.getElementById('processed-lost-found-count').textContent = `${data.processed_count} processed`;
        } catch (error) {
            // Keep the current lists visible if a refresh fails.
        }
    }

    setInterval(refreshLostFoundDesk, 10000);
</script>
@endpush
