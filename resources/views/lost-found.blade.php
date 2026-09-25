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
            aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"
            @class([
                'px-3 py-1.5 bg-red-900 text-white text-xs rounded-lg font-bold',
                'hidden' => $errors->any(),
            ])
        >
            REPORT AN ITEM
        </button>
    </div>

    @if(session('success'))
        <div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" role="dialog" aria-modal="true" aria-labelledby="success-modal-title">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg">
                <div class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <i class="fa-solid fa-check text-green-700"></i>
                    </div>
                    <h3 id="success-modal-title" class="text-lg font-bold text-gray-900">Report Submitted Successfully!</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ session('success') }}</p>
                </div>

                <button type="button" onclick="closeSuccessModal()" class="mt-5 w-full rounded-lg bg-red-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                    Close
                </button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <p class="mb-2 text-sm font-bold text-red-800">Please check your item report:</p>
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="report-form" @class(['hidden' => ! $errors->any()])>
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

    <div id="gallery" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @include('partials.lost-found-gallery-items', ['items' => $items])
    </div>
    <div id="gallery-pagination" class="hidden text-center">
        <button type="button" id="view-more" onclick="viewMoreItems()" class="rounded-lg border border-red-900 px-4 py-2 text-xs font-bold text-red-900 hover:bg-red-50">
            View More
        </button>
    </div>
    <p id="empty" class="hidden bg-white rounded-xl p-8 text-center border text-sm text-gray-500">No items found.</p>
</div>
@endsection
@push('scripts')
    <script>
        const pageSize = 10;
        const visibleLimits = {
            ALL: pageSize,
            LOST: pageSize,
            FOUND: pageSize,
        };
        let active = 'ALL';

        function setFilter(value) {
            active = value;
            document.querySelectorAll('.filter').forEach((button) => {
                button.className = 'filter px-3 py-1 rounded-md text-gray-600';
            });

            document.querySelector(`[data-filter="${value}"]`).className = 'filter px-3 py-1 rounded-md bg-white text-red-900 shadow';
            filterItems();
        }

        function viewMoreItems() {
            visibleLimits[active] += pageSize;
            filterItems();
        }

        async function refreshLostFoundItems() {
            try {
                const response = await fetch('{{ route('lost-found.items') }}', {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                document.getElementById('gallery').innerHTML = data.html;
                filterItems();
            } catch (error) {
                // Keep the current gallery visible if a refresh fails.
            }
        }

        function filterItems() {
            const query = document.getElementById('search').value.toLowerCase();
            const items = Array.from(document.querySelectorAll('.item'));
            const matches = items.filter((item) => {
                return (active === 'ALL' || item.dataset.status === active) && item.dataset.text.includes(query);
            });

            items.forEach((item) => item.classList.add('hidden'));

            matches.slice(0, visibleLimits[active]).forEach((item) => {
                item.classList.remove('hidden');
            });

            document.getElementById('empty').classList.toggle('hidden', matches.length > 0);
            document.getElementById('gallery-pagination').classList.toggle('hidden', matches.length <= visibleLimits[active]);
        }

        filterItems();
        setInterval(refreshLostFoundItems, 10000);

        function closeSuccessModal() {
            document.getElementById('success-modal')?.remove();
        }
    </script>
@endpush
