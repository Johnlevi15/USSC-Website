@forelse($requests as $request)
    <tr class="hover:bg-gray-50/70">
        <td class="p-4">
            <p class="font-bold">{{ $request->user?->name ?? 'Unknown user' }}</p>
            <p class="text-xs text-gray-500">{{ $request->user?->email }}</p>
        </td>
        <td class="p-4">
            <p class="font-semibold">{{ $request->documentType?->name ?? 'Unknown type' }}</p>
            <p class="text-xs text-gray-500">Request #{{ $request->request_id }}</p>
        </td>
        <td class="p-4 text-xs text-gray-500">{{ $request->submitted_at?->format('M j, Y g:i A') }}</td>
        <td class="p-4">
            <span @class([
                'rounded-full px-2 py-1 text-[10px] font-bold uppercase',
                'bg-green-100 text-green-800' => $request->status === 'ready',
                'bg-red-100 text-red-800' => $request->status === 'rejected',
                'bg-blue-100 text-blue-800' => in_array($request->status, ['review', 'approved']),
                'bg-yellow-100 text-yellow-800' => $request->status === 'pending',
            ])>
                {{ $request->status === 'review' ? 'Under Review' : $request->status }}
            </span>
        </td>
        <td class="p-4 text-right">
            <a href="{{ route('admin.documents.review', $request) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-900 px-3 py-2 text-xs font-bold text-white hover:bg-red-800">
                <i class="fa-solid fa-eye"></i>
                Review
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="p-10 text-center text-sm text-gray-500">No document requests found.</td>
    </tr>
@endforelse
