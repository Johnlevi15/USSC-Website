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
        <div class="flex min-h-32 items-center justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $stat['label'] }}</p>
                <p class="mt-2 text-4xl font-black leading-none tracking-tight text-gray-900 sm:text-5xl">{{ $stat['value'] }}</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-700 sm:h-16 sm:w-16">
                <i class="fa-solid {{ $stat['icon'] }} text-xl sm:text-2xl"></i>
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
                <a href="{{ route('admin.documents.review', $request) }}" class="flex items-center justify-between gap-3 p-4 text-sm hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="truncate font-bold">{{ $request->user?->name ?? 'Unknown user' }}</p>
                        <p class="text-xs text-gray-500">{{ $request->documentType?->name ?? 'Unknown type' }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Submitted {{ $request->submitted_at?->diffForHumans() ?? 'at an unknown time' }}
                            @if($request->submitted_at)
                                <span aria-hidden="true">·</span>
                                <time datetime="{{ $request->submitted_at->toIso8601String() }}">{{ $request->submitted_at->format('M j, Y g:i A') }}</time>
                            @endif
                        </p>
                    </div>
                    <span @class([
                        'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                        'bg-green-100 text-green-800' => $request->status === 'ready',
                        'bg-red-100 text-red-800' => $request->status === 'rejected',
                        'bg-blue-100 text-blue-800' => in_array($request->status, ['review', 'approved']),
                        'bg-yellow-100 text-yellow-800' => $request->status === 'pending',
                    ])>{{ $request->status === 'review' ? 'Under Review' : $request->status }}</span>
                </a>
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
                <a href="{{ route('admin.lost-found.review', $item) }}" class="flex items-center justify-between gap-3 p-4 text-sm hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="truncate font-bold">{{ $item->item_name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->place ?: 'Location not provided' }}</p>
                        <p class="mt-1 text-xs text-gray-500">Reported by {{ $item->poster?->name ?? 'Unknown user' }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Submitted {{ $item->submitted_at?->diffForHumans() ?? 'at an unknown time' }}
                            @if($item->submitted_at)
                                <span aria-hidden="true">·</span>
                                <time datetime="{{ $item->submitted_at->toIso8601String() }}">{{ $item->submitted_at->format('M j, Y g:i A') }}</time>
                            @endif
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-red-100 px-2 py-1 text-[10px] font-bold uppercase text-red-800">{{ $item->status }}</span>
                </a>
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
