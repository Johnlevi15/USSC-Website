@extends('layouts.admin')
@section('title', 'Review Lost & Found Item | USSC Admin')
@section('content')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('admin.lost-found') }}" class="mb-2 inline-flex items-center gap-2 text-xs font-bold text-red-900 hover:underline">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Lost & Found
        </a>
        <h2 class="text-2xl font-extrabold text-gray-900">Review Lost & Found Item</h2>
        <p class="text-sm text-gray-500">Review the submitted item information before changing its approval or item status.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @if($item->archived_at)
            <span class="w-fit rounded-full bg-gray-900 px-3 py-1.5 text-xs font-bold uppercase text-white">
                archived
            </span>
        @endif
        <span @class([
            'w-fit rounded-full px-3 py-1.5 text-xs font-bold uppercase',
            'bg-red-100 text-red-800' => $item->status === 'lost',
            'bg-blue-100 text-blue-800' => $item->status === 'found',
            'bg-green-100 text-green-800' => $item->status === 'claimed',
            'bg-gray-100 text-gray-700' => ! in_array($item->status, ['lost', 'found', 'claimed'], true),
        ])>
            {{ $item->status }}
        </span>
        <span @class([
            'w-fit rounded-full px-3 py-1.5 text-xs font-bold uppercase',
            'bg-yellow-100 text-yellow-800' => $item->approval_status === 'pending',
            'bg-green-100 text-green-800' => $item->approval_status === 'approved',
            'bg-red-100 text-red-800' => $item->approval_status === 'rejected',
            'bg-gray-100 text-gray-700' => ! in_array($item->approval_status, ['pending', 'approved', 'rejected'], true),
        ])>
            {{ $item->approval_status }}
        </span>
    </div>
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

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Item Information</h3>
            </div>
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Item Number</p>
                    <p class="mt-1 font-semibold text-gray-900">#{{ $item->item_id }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Item Name</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $item->item_name }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Category</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $item->category }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Place</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $item->place ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Submitted</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $item->submitted_at?->format('F j, Y g:i A') ?? 'Not available' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Reviewed By</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $item->reviewer?->name ?? 'Not reviewed yet' }}</p>
                </div>
                @if($item->archived_at)
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Archived</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $item->archived_at->format('F j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Archived By</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $item->archiver?->name ?? 'Unknown admin' }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Reporter Information</h3>
            </div>
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Full Name</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $item->poster?->name ?? 'Unknown user' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Email Address</p>
                    <p class="mt-1 break-all text-sm text-gray-700">{{ $item->poster?->email ?? 'Not available' }}</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Submitted Item Details</h3>
            </div>
            <div class="space-y-5 p-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Description</p>
                    <p class="mt-2 whitespace-pre-line break-words text-sm text-gray-900">{{ $item->description }}</p>
                </div>

                @if(filled($item->admin_remarks))
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Admin Remarks</p>
                        <p class="mt-2 whitespace-pre-line break-words text-sm text-gray-900">{{ $item->admin_remarks }}</p>
                    </div>
                @endif

                @if($item->imageUrl())
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Submitted Image</p>
                        <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener" class="mt-2 block overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->item_name }}" class="max-h-96 w-full object-contain">
                        </a>
                    </div>
                @else
                    <p class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-5 text-center text-sm text-gray-500">No image was submitted for this item.</p>
                @endif
            </div>
        </section>
    </div>

    <aside class="lg:col-span-1">
        <div class="sticky top-32 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b bg-gray-50 px-5 py-4">
                <h3 class="font-bold text-gray-900">Review Decision</h3>
                <p class="mt-1 text-xs text-gray-500">Set the public visibility and current item state.</p>
            </div>

            @include('admin.partials.lost-found-item-form', ['item' => $item, 'reviewPage' => true])

            <div class="border-t p-5">
                @if($item->archived_at)
                    <form method="POST" action="{{ route('admin.lost-found.restore', $item) }}">
                        @csrf
                        @method('PATCH')
                        <button class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-rotate-left mr-1"></i> RESTORE ITEM
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.lost-found.archive.store', $item) }}" onsubmit="return confirm('Archive this lost-and-found item?')">
                        @csrf
                        @method('PATCH')
                        <button class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-box-archive mr-1"></i> ARCHIVE ITEM
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    function closeSuccessModal() {
        document.getElementById('success-modal')?.remove();
    }
</script>
@endpush
