@extends('layouts.ussc')
@section('title', 'Lost & Found | CLSU USSC Portal')
@section('content')
<div class="max-w-4xl w-full mx-auto p-4 md:p-6 space-y-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-3 gap-2">
        <div>
            <h2 class="text-lg font-bold">Campus Lost & Found Gallery</h2>
            <p class="text-xs text-gray-500">Browse reported items or submit a report for missing items.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('report-item', ['type' => 'lost']) }}" class="px-3 py-1.5 bg-yellow-600 text-white text-xs rounded-lg font-bold">REPORT LOST</a>
            <a href="{{ route('report-item', ['type' => 'found']) }}" class="px-3 py-1.5 bg-red-900 text-white text-xs rounded-lg font-bold">REPORT FOUND</a>
        </div>
    </div>

    <div class="bg-white p-3 rounded-xl border shadow-sm">
        <input id="search" type="search" placeholder="Search lost or found items..." class="w-full px-3 py-2 text-xs border rounded-lg" oninput="filterItems()">
        <div class="flex gap-1 mt-3 pt-2 border-t text-xs font-semibold">
            <button onclick="setFilter('ALL')" class="filter px-3 py-1 rounded-md bg-white text-red-900 shadow" data-filter="ALL">ALL</button>
            <button onclick="setFilter('LOST')" class="filter px-3 py-1 rounded-md text-gray-600" data-filter="LOST">LOST</button>
            <button onclick="setFilter('FOUND')" class="filter px-3 py-1 rounded-md text-gray-600" data-filter="FOUND">FOUND</button>
        </div>
    </div>

    <div id="gallery" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach([
            ['FOUND', 'Black Samsung Smartphone', 'Found in CLIRDEC'],
            ['FOUND', 'White Wireless Earbuds', 'Found in Main Library'],
            ['LOST', 'Brown Leather Wallet & CLSU ID', 'Lost near College of Engineering'],
            ['LOST', 'Matte Black Hydroflask Tumbler', 'Lost at University Gymnasium'],
        ] as $item)
            <article class="item bg-white rounded-xl shadow-sm border p-4" data-status="{{ $item[0] }}" data-text="{{ strtolower(implode(' ', $item)) }}">
                <span class="text-[10px] font-bold text-red-900 bg-red-50 px-2 py-1 rounded">{{ $item[0] }}</span>
                <h3 class="text-sm font-bold mt-3">{{ $item[1] }}</h3>
                <p class="text-xs text-gray-600 mt-1"><i class="fa-solid fa-location-dot text-red-700 mr-1"></i>{{ $item[2] }}</p>
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