@extends('layouts.admin')
@section('title', 'Lost & Found Desk | USSC Admin')
@section('content')
<div><h2 class="text-2xl font-extrabold text-gray-900">Approve Lost & Found Reports</h2><p class="text-sm text-gray-500">Verify reports, mark claimed items, or reject invalid submissions.</p></div>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @forelse($items as $item)
        <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3"><div><span class="rounded-full bg-red-100 px-2 py-1 text-[10px] font-bold uppercase text-red-800">{{ $item->status }}</span><h3 class="mt-2 font-bold">{{ $item->item_name }}</h3><p class="text-xs text-gray-500">{{ $item->category }} · {{ $item->place }}</p></div><span class="text-xs text-gray-400">#{{ $item->item_id }}</span></div>
            <p class="mt-3 text-sm text-gray-600">{{ $item->description }}</p>
            <p class="mt-3 text-xs text-gray-500">Reported by {{ $item->poster?->name ?? 'Unknown user' }} · {{ $item->poster?->email }}</p>
            <form method="POST" action="{{ route('admin.lost-found.update', $item) }}" class="mt-4 flex gap-2 border-t pt-3">
                @csrf @method('PATCH')
                <select name="status" class="min-w-0 flex-1 rounded-lg border px-2 py-2 text-xs"><option value="approved" @selected($item->status === 'approved')>Approve</option><option value="claimed" @selected($item->status === 'claimed')>Mark Claimed</option><option value="rejected" @selected($item->status === 'rejected')>Reject</option><option value="lost" @selected($item->status === 'lost')>Keep Lost</option><option value="found" @selected($item->status === 'found')>Keep Found</option></select>
                <button class="rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">Save</button>
            </form>
        </article>
    @empty
        <p class="col-span-full rounded-xl border bg-white p-10 text-center text-sm text-gray-500">No lost-and-found reports found.</p>
    @endforelse
</div>
<div>{{ $items->links() }}</div>
@endsection
