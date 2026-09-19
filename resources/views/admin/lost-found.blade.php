@extends('layouts.admin')
@section('title', 'Lost & Found Desk | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Approve Lost & Found Reports</h2>
    <p class="text-sm text-gray-500">Review pending reports, then manage approved or rejected items separately.</p>
</div>

<section class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold">Pending Reports</h3>
            <p class="text-xs text-gray-500">These reports are not visible publicly yet.</p>
        </div>
        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">{{ $pendingItems->count() }} pending</span>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ $processedItems->count() }} processed</span>
    </div>

    <div class="space-y-3">
        @forelse($processedItems as $item)
            <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <div>
                            <span @class([
                                'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                                'bg-red-100 text-red-800' => $item->status === 'lost',
                                'bg-blue-100 text-blue-800' => $item->status === 'found',
                                'bg-green-100 text-green-800' => $item->status === 'claimed',
                                'bg-gray-100 text-gray-700' => ! in_array($item->status, ['lost', 'found', 'claimed'], true),
                            ])>
                                {{ $item->status }}
                            </span>

                            <span @class([
                                'ml-1 rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                                'bg-yellow-100 text-yellow-800' => $item->approval_status === 'pending',
                                'bg-green-100 text-green-800' => $item->approval_status === 'approved',
                                'bg-red-100 text-red-800' => $item->approval_status === 'rejected',
                                'bg-gray-100 text-gray-700' => ! in_array($item->approval_status, ['pending', 'approved', 'rejected'], true),
                            ])>
                                {{ $item->approval_status }}
                            </span>
                        </div>

                        <h3 class="mt-2 font-bold">{{ $item->item_name }}</h3>
                        <p class="text-xs text-gray-500">{{ $item->category }} · {{ $item->place }} · Submitted {{ $item->submitted_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</p>
                    </div>

                    <a href="{{ route('admin.lost-found.review', $item) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">
                        <i class="fa-solid fa-eye"></i>
                        Review
                    </a>
                </div>
            </article>
        @empty
            <p class="rounded-xl border bg-white p-10 text-center text-sm text-gray-500">No approved or rejected reports yet.</p>
        @endforelse
    </div>
</section>
@endsection
