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
                            'min-h-32 border-b border-r border-gray-200 p-2',
                            'bg-gray-50 text-gray-400' => ! $day->isSameMonth($month),
                            'bg-red-50/50' => $day->isToday(),
                        ])>
                            <div class="mb-2 flex items-center justify-between">
                                <span @class([
                                    'flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold',
                                    'bg-red-900 text-white' => $day->isToday(),
                                ])>{{ $day->day }}</span>
                                @if($dayEvents->isNotEmpty())
                                    <span class="rounded-full bg-yellow-100 px-1.5 py-0.5 text-[10px] font-bold text-yellow-800">{{ $dayEvents->count() }}</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach($dayEvents->take(2) as $event)
                                    <article class="rounded bg-red-100 px-1.5 py-1 text-[10px] leading-tight text-red-950" title="{{ $event->title }} — {{ $event->start_time }} to {{ $event->end_time }}">
                                        <span class="font-bold">{{ $event->start_time }}</span> {{ $event->title }}
                                    </article>
                                @endforeach
                                @if($dayEvents->count() > 2)
                                    <p class="px-1 text-[10px] font-semibold text-red-800">+{{ $dayEvents->count() - 2 }} more</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
