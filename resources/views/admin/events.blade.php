@extends('layouts.admin')
@section('title', 'Events | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Calendar Events</h2>
    <p class="text-sm text-gray-500">Publish and manage events that appear on the student portal calendar.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,380px)_1fr]">
    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="font-bold">Publish an Event</h3>
        <form method="POST" action="{{ route('admin.events.store') }}" class="mt-4 space-y-4">
            @csrf

            <label class="block text-xs font-bold uppercase text-gray-600">
                Event title
                <input required name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                Description
                <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                Date
                <input required type="date" name="event_date" value="{{ old('event_date') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            </label>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="block text-xs font-bold uppercase text-gray-600">
                    Starts
                    <input required type="time" name="start_time" value="{{ old('start_time') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-bold uppercase text-gray-600">
                    Ends
                    <input required type="time" name="end_time" value="{{ old('end_time') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                </label>
            </div>

            <button class="w-full rounded-lg bg-red-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                <i class="fa-solid fa-calendar-plus mr-1"></i> PUBLISH TO STUDENT PORTAL
            </button>
        </form>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b p-5">
            <h3 class="font-bold">Published Events</h3>
            <p class="mt-1 text-xs text-gray-500">Edit event information or remove events that should no longer appear on the student portal.</p>
        </div>

        <div class="divide-y">
            @forelse($events as $event)
                <div class="p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900">{{ $event->title }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fa-regular fa-calendar mr-1"></i>
                                {{ $event->event_date->format('F j, Y') }}
                                <span class="mx-1">·</span>
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                                -
                                {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}
                            </p>

                            @if($event->description)
                                <p class="mt-2 whitespace-pre-line text-xs leading-5 text-gray-600">{{ $event->description }}</p>
                            @endif

                            <p class="mt-2 text-[11px] text-gray-400">
                                Published by {{ $event->creator?->name ?? 'Admin' }}
                            </p>
                        </div>

                        <div class="grid w-full grid-cols-2 gap-2 sm:flex sm:w-auto sm:shrink-0 sm:items-center">
                            <a href="{{ route('admin.events.edit', $event) }}"
                               class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.events.destroy', $event) }}"
                                  onsubmit="return confirm('Delete this event? It will also disappear from the student calendar.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-red-700 px-3 py-2 text-xs font-bold text-white hover:bg-red-800">
                                    <i class="fa-solid fa-trash"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="p-10 text-center text-sm text-gray-500">No events have been published.</p>
            @endforelse
        </div>

        @if($events->hasPages())
            <div class="border-t p-4">{{ $events->links() }}</div>
        @endif
    </section>
</div>
@endsection
