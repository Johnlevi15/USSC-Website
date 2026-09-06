@extends('layouts.ussc')
@section('title', 'CLSU USSC Portal')
@section('content')
<div class="max-w-4xl w-full mx-auto p-4 md:p-6 space-y-6">
    <section class="bg-white rounded-xl shadow-sm border p-8 text-center">
        <img src="{{ asset('logo.png') }}" alt="USSC Logo" class="w-24 h-24 object-contain mx-auto mb-3" onerror="this.src='https://via.placeholder.com/96'">
        <h2 class="text-xl font-bold">UNIVERSITY SUPREME STUDENT COUNCIL</h2>
        <p class="text-sm text-gray-600">Central Luzon State University</p>
        <p class="text-xs text-gray-600 max-w-md mx-auto mt-4">Serving as the official governing student body of CLSU, committed to student representation, advocacy, and welfare across the campus community.</p>
    </section>
    <section class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="font-bold text-lg text-red-900"><i class="fa-solid fa-calendar-days"></i> UNIVERSITY EVENTS</h3>
            <span class="text-xs font-semibold bg-red-50 text-red-900 px-3 py-1 rounded-full">{{ now()->format('F Y') }}</span>
        </div>
        <div class="space-y-3">
            @forelse($events as $event)
                <article class="border rounded-lg p-3">
                    <h4 class="font-bold text-sm">{{ $event->title }}</h4>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $event->event_date->format('F j, Y') }} · {{ $event->start_time }} - {{ $event->end_time }}
                    </p>
                </article>
            @empty
                <p class="text-sm text-gray-500 text-center py-6">No events have been published yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection