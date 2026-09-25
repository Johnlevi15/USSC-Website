<article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <div>
                <span @class([
                    'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                    'bg-red-100 text-red-800' => $item->status === 'lost',
                    'bg-blue-100 text-blue-800' => $item->status === 'found',
                    'bg-green-100 text-green-800' => $item->status === 'claimed',
                    'bg-gray-100 text-gray-700' => ! in_array($item->status, ['lost', 'found', 'claimed'], true),
                ])>
                    {{ $item->status }}
                </span>

                <span @class([
                    'ml-1 rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                    'bg-yellow-100 text-yellow-800' => $item->approval_status === 'pending',
                    'bg-green-100 text-green-800' => $item->approval_status === 'approved',
                    'bg-red-100 text-red-800' => $item->approval_status === 'rejected',
                    'bg-gray-100 text-gray-700' => ! in_array($item->approval_status, ['pending', 'approved', 'rejected'], true),
                ])>
                    {{ $item->approval_status }}
                </span>
            </div>

            <h3 class="mt-2 font-bold">{{ $item->item_name }}</h3>
            <p class="text-xs text-gray-500">{{ $item->category }} Â· {{ $item->place }} Â· Submitted {{ $item->submitted_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</p>
        </div>

        <a href="{{ route('admin.lost-found.review', $item) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">
            <i class="fa-solid fa-eye"></i>
            Review
        </a>
    </div>
</article>
