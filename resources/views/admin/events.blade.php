@extends('layouts.admin')
@section('title', 'Events | USSC Admin')
@section('content')
<div><h2 class="text-2xl font-extrabold text-gray-900">Create Calendar Events</h2><p class="text-sm text-gray-500">Publish events that appear on the student portal calendar.</p></div>
<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,380px)_1fr]">
    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="font-bold">Publish an Event</h3>
        <form method="POST" action="{{ route('admin.events.store') }}" class="mt-4 space-y-4">
            @csrf
            <label class="block text-xs font-bold uppercase text-gray-600">Event title<input required name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></label>
            <label class="block text-xs font-bold uppercase text-gray-600">Description<textarea name="description" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description') }}</textarea></label>
            <label class="block text-xs font-bold uppercase text-gray-600">Date<input required type="date" name="event_date" value="{{ old('event_date') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></label>
            <div class="grid grid-cols-2 gap-3"><label class="block text-xs font-bold uppercase text-gray-600">Starts<input required type="time" name="start_time" value="{{ old('start_time') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></label><label class="block text-xs font-bold uppercase text-gray-600">Ends<input required type="time" name="end_time" value="{{ old('end_time') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></label></div>
            <button class="w-full rounded-lg bg-red-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">PUBLISH TO STUDENT PORTAL</button>
        </form>
    </section>
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b p-5"><h3 class="font-bold">Published Events</h3></div>
        <div class="divide-y">
            @forelse($events as $event)
                <div class="flex flex-col justify-between gap-2 p-4 sm:flex-row sm:items-center"><div><p class="font-bold">{{ $event->title }}</p><p class="text-xs text-gray-500">{{ $event->event_date->format('F j, Y') }} · {{ $event->start_time }} - {{ $event->end_time }}</p>@if($event->description)<p class="mt-1 text-xs text-gray-600">{{ $event->description }}</p>@endif</div><span class="text-xs text-gray-400">{{ $event->creator?->user?->name ?? 'Admin' }}</span></div>
            @empty
                <p class="p-10 text-center text-sm text-gray-500">No events have been published.</p>
            @endforelse
        </div>
        <div class="border-t p-4">{{ $events->links() }}</div>
    </section>
</div>
@endsection
