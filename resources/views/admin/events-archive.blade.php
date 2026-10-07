@extends('layouts.admin')
@section('title', 'Archived Events | USSC Admin')
@section('content')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('admin.events') }}" class="mb-2 inline-flex items-center gap-2 text-xs font-bold text-red-900 hover:underline">
            <i class="fa-solid fa-arrow-left"></i>
            Back to events
        </a>
        <h2 class="text-2xl font-extrabold text-gray-900">Archived Events</h2>
        <p class="text-sm text-gray-500">Archived events are hidden from the student calendar and can be restored at any time.</p>
    </div>
    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ $events->total() }} archived</span>
</div>

<section class="mt-6 space-y-3">
    @forelse($events as $event)
        <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <span class="rounded-full bg-gray-900 px-2 py-1 text-[10px] font-bold uppercase text-white">Archived</span>
                    <h3 class="mt-2 font-bold text-gray-900">{{ $event->title }}</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $event->event_date->format('F j, Y') }}
                        &middot; {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                        - {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}
                        &middot; Archived {{ $event->archived_at?->format('M j, Y g:i A') ?? 'Unknown date' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Archived by {{ $event->archiver?->name ?? 'Unknown admin' }}</p>
                    @if($event->description)
                        <p class="mt-2 whitespace-pre-line text-xs leading-5 text-gray-600">{{ $event->description }}</p>
                    @endif
                </div>

                <form method="POST"
                      action="{{ route('admin.events.restore', $event) }}"
                      data-confirm-title="Restore event?"
                      data-confirm-message="Restore {{ $event->title }} to the student calendar?"
                      data-confirm-button="Restore event">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 sm:w-auto">
                        <i class="fa-solid fa-rotate-left"></i>
                        Restore
                    </button>
                </form>
            </div>
        </article>
    @empty
        <p class="rounded-xl border bg-white p-10 text-center text-sm text-gray-500">No archived events yet.</p>
    @endforelse
</section>

@if($events->hasPages())
    <div class="mt-4 flex items-center justify-between border-t p-4">
        @if($events->currentPage() > 1)
            <a href="{{ $events->previousPageUrl() }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">
                Previous events
            </a>
        @else
            <span></span>
        @endif

        @if($events->hasMorePages())
            <a href="{{ $events->nextPageUrl() }}" class="inline-flex items-center rounded-lg bg-red-900 px-4 py-2 text-sm font-bold text-white hover:bg-red-800">
                See more events
                <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
        @endif
    </div>
@endif
@endsection
