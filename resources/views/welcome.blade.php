@extends('layouts.ussc')
@section('title', 'CLSU USSC Portal')
@section('content')
<div class="max-w-6xl w-full mx-auto p-4 md:p-6 space-y-6">
    <section class="bg-white rounded-xl shadow-sm border p-8 text-center">
        <img src="{{ asset('logo.png') }}" alt="USSC Logo" class="w-24 h-24 object-contain mx-auto mb-3" onerror="this.src='https://via.placeholder.com/96'">
        <h2 class="text-xl font-bold">UNIVERSITY SUPREME STUDENT COUNCIL</h2>
        <p class="text-sm text-gray-600">Central Luzon State University</p>
        <p class="text-xs text-gray-600 max-w-md mx-auto mt-4">Serving as the official governing student body of CLSU, committed to student representation, advocacy, and welfare across the campus community.</p>
    </section>

    <section class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex flex-col gap-4 border-b pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-bold text-lg text-red-900"><i class="fa-solid fa-calendar-days"></i> UNIVERSITY EVENTS</h3>
                <p class="mt-1 text-xs text-gray-500">Select a month to see scheduled campus activities.</p>
            </div>
            <div class="flex items-center justify-between gap-3 sm:justify-end">
                <a href="{{ route('home', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:border-red-900 hover:text-red-900" aria-label="Previous month">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                <span class="min-w-32 text-center text-sm font-bold text-red-900">{{ $month->format('F Y') }}</span>
                <a href="{{ route('home', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:border-red-900 hover:text-red-900" aria-label="Next month">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <div class="min-w-[42rem]">
                <div class="grid grid-cols-7 border-l border-t border-gray-200 bg-gray-50 text-center text-[11px] font-bold uppercase tracking-wide text-gray-500">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                        <div class="border-b border-r border-gray-200 py-2">{{ $dayName }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 border-l border-t border-gray-200">
                    @foreach($calendarDays as $day)
                        @php($dayEvents = $eventsByDate->get($day->toDateString(), collect()))
                        <div @class([
                            'min-h-32 border-b border-r border-gray-200 p-2 transition',
                            'bg-gray-50 text-gray-400' => ! $day->isSameMonth($month),
                            'bg-red-50/50' => $day->isToday(),
                            'bg-red-50/40 ring-2 ring-inset ring-red-200' => $dayEvents->isNotEmpty() && ! $day->isToday(),
                        ])>
                            <div class="mb-2 flex items-center justify-between">
                                <span @class([
                                    'flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold',
                                    'bg-red-900 text-white' => $day->isToday(),
                                ])>{{ $day->day }}</span>
                                @if($dayEvents->isNotEmpty())
                                    <span class="rounded-full bg-red-900 px-2 py-0.5 text-[10px] font-bold text-white">{{ $dayEvents->count() }} event{{ $dayEvents->count() > 1 ? 's' : '' }}</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach($dayEvents->take(2) as $event)
                                    <article class="rounded-md border-l-4 border-red-900 bg-white px-2 py-1 text-[10px] leading-tight text-red-950 shadow-sm" title="{{ $event->title }} - {{ $event->start_time }} to {{ $event->end_time }}">
                                        <span class="block font-extrabold text-red-900">{{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}</span>
                                        <span class="font-semibold">{{ $event->title }}</span>
                                    </article>
                                @endforeach
                                @if($dayEvents->count() > 2)
                                    <p class="rounded bg-red-900 px-2 py-1 text-[10px] font-bold text-white">+{{ $dayEvents->count() - 2 }} more</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 border-t pt-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h4 class="text-sm font-extrabold uppercase tracking-wide text-red-900">Events This Month</h4>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-900">{{ $events->count() }} scheduled</span>
            </div>

            @if($events->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach($events as $event)
                        <article class="rounded-lg border border-red-100 bg-red-50/40 p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-red-900 text-white">
                                    <span class="text-[10px] font-bold uppercase">{{ $event->event_date->format('M') }}</span>
                                    <span class="text-lg font-extrabold leading-none">{{ $event->event_date->format('j') }}</span>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="font-bold text-gray-900">{{ $event->title }}</h5>
                                    <p class="mt-1 text-xs font-semibold text-red-900">
                                        {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                                        -
                                        {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}
                                    </p>
                                    @if($event->description)
                                        <p class="mt-2 line-clamp-2 text-xs leading-5 text-gray-600">{{ $event->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-500">No events are scheduled for this month.</p>
            @endif
        </div>
    </section>
</div>
@endsection
