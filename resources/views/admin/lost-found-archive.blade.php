@extends('layouts.admin')
@section('title', 'Lost & Found Archive | USSC Admin')
@section('content')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('admin.lost-found') }}" class="mb-2 inline-flex items-center gap-2 text-xs font-bold text-red-900 hover:underline">
            <i class="fa-solid fa-arrow-left"></i>
            Back to active desk
        </a>
        <h2 class="text-2xl font-extrabold text-gray-900">Lost & Found Archive</h2>
        <p class="text-sm text-gray-500">Archived records are hidden from active admin lists and the public gallery.</p>
    </div>
    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ $items->count() }} archived</span>
</div>

@if(session('success'))
    <div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" role="dialog" aria-modal="true" aria-labelledby="success-modal-title">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg">
            <div class="text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <i class="fa-solid fa-check text-green-700"></i>
                </div>
                <h3 id="success-modal-title" class="text-lg font-bold text-gray-900">Changes Saved</h3>
                <p class="mt-2 text-sm text-gray-600">{{ session('success') }}</p>
            </div>

            <button type="button" onclick="closeSuccessModal()" class="mt-5 w-full rounded-lg bg-red-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                Close
            </button>
        </div>
    </div>
@endif

<section class="space-y-3">
    @forelse($items as $item)
        <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <div class="flex flex-wrap gap-1">
                        <span class="rounded-full bg-gray-900 px-2 py-1 text-[10px] font-bold uppercase text-white">archived</span>
                        <span @class([
                            'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                            'bg-red-100 text-red-800' => $item->status === 'lost',
                            'bg-blue-100 text-blue-800' => $item->status === 'found',
                            'bg-green-100 text-green-800' => $item->status === 'claimed',
                            'bg-gray-100 text-gray-700' => ! in_array($item->status, ['lost', 'found', 'claimed'], true),
                        ])>{{ $item->status }}</span>
                        <span @class([
                            'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                            'bg-yellow-100 text-yellow-800' => $item->approval_status === 'pending',
                            'bg-green-100 text-green-800' => $item->approval_status === 'approved',
                            'bg-red-100 text-red-800' => $item->approval_status === 'rejected',
                            'bg-gray-100 text-gray-700' => ! in_array($item->approval_status, ['pending', 'approved', 'rejected'], true),
                        ])>{{ $item->approval_status }}</span>
                    </div>
                    <h3 class="mt-2 font-bold">{{ $item->item_name }}</h3>
                    <p class="text-xs text-gray-500">{{ $item->category }} &middot; {{ $item->place }} &middot; Archived {{ $item->archived_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</p>
                    <p class="mt-1 text-xs text-gray-400">Archived by {{ $item->archiver?->name ?? 'Unknown admin' }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.lost-found.review', $item) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">
                        <i class="fa-solid fa-eye"></i>
                        Review
                    </a>
                    <form method="POST" action="{{ route('admin.lost-found.restore', $item) }}">
                        @csrf
                        @method('PATCH')
                        <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-rotate-left"></i>
                            Restore
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <p class="rounded-xl border bg-white p-10 text-center text-sm text-gray-500">No archived lost-and-found items yet.</p>
    @endforelse
</section>
@endsection

@push('scripts')
<script>
    function closeSuccessModal() {
        document.getElementById('success-modal')?.remove();
    }
</script>
@endpush
