@forelse($requests as $request)
    <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <div>
                    <span @class([
                        'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                        'bg-green-100 text-green-800' => $request->status === 'ready',
                        'bg-red-100 text-red-800' => $request->status === 'rejected',
                        'bg-blue-100 text-blue-800' => in_array($request->status, ['review', 'approved']),
                        'bg-yellow-100 text-yellow-800' => $request->status === 'pending',
                    ])>
                        {{ $request->status === 'review' ? 'Under Review' : $request->status }}
                    </span>
                </div>

                <h3 class="mt-2 font-bold">{{ $request->documentType?->name ?? 'Unknown type' }}</h3>
                <p class="text-xs text-gray-500">{{ $request->user?->name ?? 'Unknown user' }} · {{ $request->user?->email }}</p>
                <p class="mt-1 text-xs text-gray-400">Request #{{ $request->request_id }} · Submitted {{ $request->submitted_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</p>
            </div>

            <a href="{{ route('admin.documents.review', $request) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">
                <i class="fa-solid fa-eye"></i>
                Review
            </a>
        </div>
    </article>
@empty
    <p class="col-span-full rounded-xl border bg-white p-10 text-center text-sm text-gray-500">{{ $emptyMessage }}</p>
@endforelse
