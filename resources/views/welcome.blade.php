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
            <h3 class="font-bold text-lg text-red-900"><i class="fa-solid fa-calendar-days"></i> UNIVERSITY CALENDAR</h3>
            <span class="text-xs font-semibold bg-red-50 text-red-900 px-3 py-1 rounded-full">August 2026</span>
        </div>
        <div class="grid grid-cols-7 text-center bg-yellow-100 text-xs font-bold text-red-950">
            @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $day)
                <div class="py-2">{{ $day }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 gap-px bg-gray-200 text-xs">
            @for($day = 1; $day <= 31; $day++)
                <div class="bg-white min-h-16 p-2 font-bold">
                    {{ $day }}
                    @if($day === 18)
                        <span class="block mt-1 p-1 bg-red-900 text-white text-[10px] rounded">Intramurals Opening</span>
                    @endif
                </div>
            @endfor
        </div>
    </section>
</div>
@endsection