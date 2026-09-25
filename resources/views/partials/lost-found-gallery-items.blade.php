@foreach($items as $item)
    <article class="item bg-white rounded-xl shadow-sm border p-4" data-status="{{ strtoupper($item->status) }}" data-text="{{ strtolower($item->item_name.' '.$item->category.' '.$item->description.' '.$item->place.' '.($item->poster?->name ?? '')) }}">
        @if($item->imageUrl())
            <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener" aria-label="View full image of {{ $item->item_name }}" class="block">
                <img src="{{ $item->imageUrl() }}" alt="{{ $item->item_name }}" class="mx-auto mb-3 aspect-square h-32 w-32 cursor-zoom-in rounded-lg object-cover">
            </a>
        @endif
        <span @class([
            'rounded px-2 py-1 text-[10px] font-bold uppercase',
            'bg-red-100 text-red-800' => $item->status === 'lost',
            'bg-blue-100 text-blue-800' => $item->status === 'found',
            'bg-green-100 text-green-800' => $item->status === 'claimed',
            'bg-gray-100 text-gray-700' => ! in_array($item->status, ['lost', 'found', 'claimed'], true),
        ])>{{ strtoupper($item->status) }}</span>
        <h3 class="text-sm font-bold mt-3">{{ $item->item_name }}</h3>
        <p class="text-xs text-gray-600 mt-1"><i class="fa-solid fa-location-dot text-red-700 mr-1"></i>{{ $item->place }}</p>
        <p class="text-xs text-gray-600 mt-2"><i class="fa-solid fa-user text-red-700 mr-1"></i>Reported by {{ $item->poster?->name ?? 'Unknown user' }}</p>
        <p class="text-xs text-gray-400 mt-1">Submitted {{ $item->submitted_at?->format('M j, Y') ?? 'Unknown date' }}</p>
        @if($item->description)
            <p class="text-xs text-gray-500 mt-2">{{ $item->description }}</p>
        @endif
    </article>
@endforeach
