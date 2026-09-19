@extends('layouts.admin')
@section('title', 'Document Requests | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Review Document Requests</h2>
    <p class="text-sm text-gray-500">Open a request to review the student's submitted information before updating its status.</p>
</div>

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="p-4">Requester</th>
                    <th class="p-4">Document</th>
                    <th class="p-4">Submitted</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
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
            </tbody>
        </table>
    </div>
    <div class="border-t p-4">{{ $requests->links() }}</div>
</div>
@endsection
