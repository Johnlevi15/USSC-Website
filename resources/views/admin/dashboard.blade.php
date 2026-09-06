@extends('layouts.admin')
@section('title', 'Admin Dashboard | USSC Portal')
@section('content')
<div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-bold uppercase tracking-wider text-red-900">Staff workspace</p>
        <h2 class="text-2xl font-extrabold text-gray-900">Admin Dashboard</h2>
        <p class="text-sm text-gray-500">Monitor submissions and keep the student portal current.</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['label' => 'Pending Documents', 'value' => $pendingDocuments, 'icon' => 'fa-file-signature', 'color' => 'yellow'],
        ['label' => 'Ready for Pickup', 'value' => $readyDocuments, 'icon' => 'fa-box-archive', 'color' => 'green'],
        ['label' => 'Lost & Found Items', 'value' => $unclaimedItems, 'icon' => 'fa-hand-holding-hand', 'color' => 'red'],
        ['label' => 'Events This Month', 'value' => $monthlyEvents, 'icon' => 'fa-calendar-days', 'color' => 'blue'],
    ] as $stat)
        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-800">{{ $stat['value'] }}</p>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600">
                <i class="fa-solid {{ $stat['icon'] }}"></i>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b p-4">
            <h3 class="font-bold">Recent Document Requests</h3>
            <a href="{{ route('admin.documents') }}" class="text-xs font-bold text-red-900">Review all</a>
        </div>
        <div class="divide-y">
            @forelse($recentDocuments as $request)
                <div class="flex items-center justify-between gap-3 p-4 text-sm">
                    <div><p class="font-bold">{{ $request->user?->name ?? 'Unknown user' }}</p><p class="text-xs text-gray-500">{{ $request->document_type }}</p></div>
                    <span class="rounded-full bg-yellow-100 px-2 py-1 text-[10px] font-bold uppercase text-yellow-800">{{ $request->status }}</span>
                </div>
            @empty
                <p class="p-6 text-center text-sm text-gray-500">No document requests yet.</p>
            @endforelse
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b p-4">
            <h3 class="font-bold">Recent Lost & Found Reports</h3>
            <a href="{{ route('admin.lost-found') }}" class="text-xs font-bold text-red-900">Review all</a>
        </div>
        <div class="divide-y">
            @forelse($recentItems as $item)
                <div class="flex items-center justify-between gap-3 p-4 text-sm">
                    <div><p class="font-bold">{{ $item->item_name }}</p><p class="text-xs text-gray-500">{{ $item->place }}</p></div>
                    <span class="rounded-full bg-red-100 px-2 py-1 text-[10px] font-bold uppercase text-red-800">{{ $item->status }}</span>
                </div>
            @empty
                <p class="p-6 text-center text-sm text-gray-500">No item reports yet.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b p-4"><h3 class="font-bold">Upcoming Events</h3><a href="{{ route('admin.events') }}" class="text-xs font-bold text-red-900">Manage events</a></div>
    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
        @forelse($upcomingEvents as $event)
            <div class="rounded-lg border p-3"><p class="font-bold text-sm">{{ $event->title }}</p><p class="mt-1 text-xs text-gray-500">{{ $event->event_date->format('M j, Y') }} · {{ $event->start_time }}</p></div>
        @empty
            <p class="col-span-full py-4 text-center text-sm text-gray-500">No upcoming events.</p>
        @endforelse
    </div>
</section>
@endsection
