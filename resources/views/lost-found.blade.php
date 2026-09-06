@extends('layouts.ussc')
@section('title', 'Lost & Found | CLSU USSC Portal')
@section('content')
<div class="max-w-4xl w-full mx-auto p-4 md:p-6 space-y-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-3 gap-2">
        <div>
            <h2 class="text-lg font-bold">Campus Lost & Found Gallery</h2>
            <p class="text-xs text-gray-500">Browse reported items or submit a report for missing items.</p>
        </div>
        <button
            type="button"
            id="report-toggle"
            onclick="toggleReportForm()"
            aria-controls="report-form"
            aria-expanded="false"
            class="px-3 py-1.5 bg-red-900 text-white text-xs rounded-lg font-bold"
        >
            REPORT AN ITEM
        </button>
    </div>

    <div id="report-form" class="hidden">
        @include('partials.report-item-form')
    </div>

    <div class="bg-white p-3 rounded-xl border shadow-sm">
        <input id="search" type="search" placeholder="Search lost or found items..." class="w-full px-3 py-2 text-xs border rounded-lg" oninput="filterItems()">
        <div class="flex gap-1 mt-3 pt-2 border-t text-xs font-semibold">
            <button onclick="setFilter('ALL')" class="filter px-3 py-1 rounded-md bg-white text-red-900 shadow" data-filter="ALL">ALL</button>
            <button onclick="setFilter('LOST')" class="filter px-3 py-1 rounded-md text-gray-600" data-filter="LOST">LOST</button>
            <button onclick="setFilter('FOUND')" class="filter px-3 py-1 rounded-md text-gray-600" data-filter="FOUND">FOUND</button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div id="gallery" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($items as $item)
            <article class="item bg-white rounded-xl shadow-sm border p-4" data-status="{{ strtoupper($item->status) }}" data-text="{{ strtolower($item->item_name.' '.$item->category.' '.$item->description.' '.$item->place.' '.($item->poster?->name ?? '')) }}">
                @if($item->image_path)
                    <a href="{{ Storage::disk('public')->url($item->image_path) }}" target="_blank" rel="noopener" aria-label="View full image of {{ $item->item_name }}" class="block">
                        <img src="{{ Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->item_name }}" class="mx-auto mb-3 aspect-square h-32 w-32 cursor-zoom-in rounded-lg object-cover">
                    </a>
                @endif
                <span class="text-[10px] font-bold text-red-900 bg-red-50 px-2 py-1 rounded">{{ strtoupper($item->status) }}</span>
                <h3 class="text-sm font-bold mt-3">{{ $item->item_name }}</h3>
                <p class="text-xs text-gray-600 mt-1"><i class="fa-solid fa-location-dot text-red-700 mr-1"></i>{{ $item->place }}</p>
                <p class="text-xs text-gray-600 mt-2"><i class="fa-solid fa-user text-red-700 mr-1"></i>Reported by {{ $item->poster?->name ?? 'Unknown user' }}</p>
                <p class="text-xs text-gray-400 mt-1">Submitted {{ $item->submitted_at?->format('M j, Y') ?? 'Unknown date' }}</p>
                @if($item->description)
                    <p class="text-xs text-gray-500 mt-2">{{ $item->description }}</p>
                @endif
            </article>
        @endforeach
    </div>
    <p id="empty" class="hidden bg-white rounded-xl p-8 text-center border text-sm text-gray-500">No items found.</p>
</div>
@endsection
@push('scripts')
    <script>
        let active = 'ALL';

        function setFilter(value) {
            active = value;
            document.querySelectorAll('.filter').forEach((button) => {
                button.className = 'filter px-3 py-1 rounded-md text-gray-600';
            });

            document.querySelector(`[data-filter="${value}"]`).className = 'filter px-3 py-1 rounded-md bg-white text-red-900 shadow';
            filterItems();
        }

        function filterItems() {
            const query = document.getElementById('search').value.toLowerCase();
            let count = 0;

            document.querySelectorAll('.item').forEach((item) => {
                const show = (active === 'ALL' || item.dataset.status === active) && item.dataset.text.includes(query);
                item.classList.toggle('hidden', !show);

                if (show) {
                    count++;
                }
            });

            document.getElementById('empty').classList.toggle('hidden', count > 0);
        }
    </script>
@endpush