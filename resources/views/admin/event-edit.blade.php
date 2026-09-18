@extends('layouts.admin')
@section('title', 'Edit Event | USSC Admin')
@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900">Edit Event</h2>
            <p class="text-sm text-gray-500">Update the information shown on the student portal calendar.</p>
        </div>
        <a href="{{ route('admin.events') }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Events
        </a>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.events.update', $event) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <label class="block text-xs font-bold uppercase text-gray-600">
                Event title
                <input required name="title" value="{{ old('title', $event->title) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                Description
                <textarea name="description" rows="5" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description', $event->description) }}</textarea>
            </label>

            <label class="block text-xs font-bold uppercase text-gray-600">
                Date
                <input required type="date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            </label>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="block text-xs font-bold uppercase text-gray-600">
                    Starts
                    <input required type="time" name="start_time" value="{{ old('start_time', substr($event->start_time, 0, 5)) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                </label>

                <label class="block text-xs font-bold uppercase text-gray-600">
                    Ends
                    <input required type="time" name="end_time" value="{{ old('end_time', substr($event->end_time, 0, 5)) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                </label>
            </div>

            <div class="rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-500">
                Originally published by <span class="font-semibold text-gray-700">{{ $event->creator?->name ?? 'Admin' }}</span>.
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t pt-5">
                <a href="{{ route('admin.events') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="rounded-lg bg-red-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
