<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex flex-col sm:flex-row justify-between gap-2 border-b pb-3 mb-4">
        <div>
            <h2 class="text-lg font-bold">Report an Item</h2>
            <p class="text-xs text-gray-500">Submit a lost or found item to the campus gallery.</p>
        </div>
      
        <div class="flex flex-wrap items-center gap-2 self-start">
            <button type="button" onclick="toggleReportForm(false)" class="px-3 py-2 text-xs font-bold text-gray-600 border rounded-lg hover:bg-gray-50">
                CLOSE
            </button>
            <div class="grid grid-cols-2 gap-1 p-1 bg-gray-100 rounded-lg">
                <button type="button" onclick="setType('found')" id="found" class="px-3 py-2 rounded-md bg-red-900 text-white text-xs font-bold">FOUND</button>
                <button type="button" onclick="setType('lost')" id="lost" class="px-3 py-2 rounded-md text-gray-600 text-xs font-bold">LOST</button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('report-item.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <label class="block text-xs font-bold text-gray-600 uppercase">
            Name
            <input required name="full_name" value="{{ old('full_name') }}" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Email Address
            <input required type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Item Title / Name
            <input required name="item_name" value="{{ old('item_name') }}" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Category
            <select required name="category" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
                <option value="">Select Item Category...</option>
                @foreach(['Cellphone', 'Gadgets', 'Earphones', 'ID', 'Wallet', 'Bags', 'Tumblers', 'Others'] as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase md:col-span-2">
            Description
            <textarea required name="description" rows="3" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">{{ old('description') }}</textarea>
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase md:col-span-2">
            Picture <span class="font-normal normal-case text-gray-400">(optional, maximum 5 MB)</span>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg bg-white">
        </label>

        <input type="hidden" name="status" id="status" value="{{ old('status', request('type', 'found')) }}">

        <label class="block text-xs font-bold text-gray-600 uppercase md:col-span-2">
            <span id="place-label">{{ old('status', request('type', 'found')) === 'lost' ? 'Location (Where Lost)' : 'Place Found' }}</span>
            <input required name="place" value="{{ old('place') }}" class="mt-1 w-full px-3 py-2 text-sm border rounded-lg">
        </label>

        <button id="submit" class="md:col-span-2 w-full py-2.5 bg-red-900 text-white font-bold rounded-lg text-sm">
            {{ old('status', request('type', 'found')) === 'lost' ? 'SUBMIT LOST ITEM REPORT' : 'SUBMIT FOUND ITEM REPORT' }}
        </button>
    </form>
</div>

@push('scripts')
    <script>
        function toggleReportForm(show = null) {
            const form = document.getElementById('report-form');
            const button = document.querySelector('[aria-controls="report-form"]');
            const shouldShow = show === null ? form.classList.contains('hidden') : show;

            form.classList.toggle('hidden', !shouldShow);
            button.setAttribute('aria-expanded', shouldShow ? 'true' : 'false');
            button.classList.toggle('hidden', shouldShow);

            if (shouldShow) {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function setType(type) {
            const lost = type === 'lost';
            document.getElementById('status').value = type;
            document.getElementById('found').className = lost
                ? 'px-3 py-2 rounded-md text-gray-600 text-xs font-bold'
                : 'px-3 py-2 rounded-md bg-red-900 text-white text-xs font-bold';
            document.getElementById('lost').className = lost
                ? 'px-3 py-2 rounded-md bg-yellow-600 text-white text-xs font-bold'
                : 'px-3 py-2 rounded-md text-gray-600 text-xs font-bold';
            document.getElementById('place-label').textContent = lost
                ? 'Location (Where Lost)'
                : 'Place Found';
            document.getElementById('submit').textContent = lost
                ? 'SUBMIT LOST ITEM REPORT'
                : 'SUBMIT FOUND ITEM REPORT';
        }
    </script>
@endpush
