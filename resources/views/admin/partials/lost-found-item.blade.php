<article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3"><div><span class="rounded-full bg-red-100 px-2 py-1 text-[10px] font-bold uppercase text-red-800">{{ $item->status }}</span><span class="ml-1 rounded-full bg-yellow-100 px-2 py-1 text-[10px] font-bold uppercase text-yellow-800">{{ $item->approval_status }}</span><h3 class="mt-2 font-bold">{{ $item->item_name }}</h3><p class="text-xs text-gray-500">{{ $item->category }} · {{ $item->place }}</p></div><span class="text-xs text-gray-400">#{{ $item->item_id }}</span></div>
    <p class="mt-3 text-sm text-gray-600">{{ $item->description }}</p>
    <p class="mt-3 text-xs text-gray-500">Reported by {{ $item->poster?->name ?? 'Unknown user' }} · {{ $item->poster?->email }}</p>
    <p class="mt-1 text-xs text-gray-400">Submitted {{ $item->submitted_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</p>
    @include('admin.partials.lost-found-item-form', ['item' => $item])
</article>
