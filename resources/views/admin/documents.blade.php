@extends('layouts.admin')
@section('title', 'Document Requests | USSC Admin')
@section('content')
<div><h2 class="text-2xl font-extrabold text-gray-900">Review Document Requests</h2><p class="text-sm text-gray-500">Update the processing status of student submissions.</p></div>
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500"><tr><th class="p-4">Requester</th><th class="p-4">Document</th><th class="p-4">Submitted</th><th class="p-4">Status</th><th class="p-4 text-right">Review</th></tr></thead>
            <tbody class="divide-y">
                @forelse($requests as $request)
                    <tr>
                        <td class="p-4"><p class="font-bold">{{ $request->user?->name ?? 'Unknown user' }}</p><p class="text-xs text-gray-500">{{ $request->user?->email }}</p></td>
                        <td class="p-4"><p class="font-semibold">{{ $request->document_type }}</p><p class="text-xs text-gray-500">Request #{{ $request->request_id }}</p></td>
                        <td class="p-4 text-xs text-gray-500">{{ $request->submitted_at?->format('M j, Y g:i A') }}</td>
                        <td class="p-4"><span class="rounded-full bg-yellow-100 px-2 py-1 text-[10px] font-bold uppercase text-yellow-800">{{ $request->status }}</span></td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('admin.documents.update', $request) }}" class="flex justify-end gap-2">
                                @csrf @method('PATCH')
                                <select name="status" class="rounded-lg border px-2 py-1 text-xs"><option value="pending" @selected($request->status === 'pending')>Pending</option><option value="review" @selected($request->status === 'review')>Under Review</option><option value="approved" @selected($request->status === 'approved')>Approved</option><option value="ready" @selected($request->status === 'ready')>Ready</option><option value="rejected" @selected($request->status === 'rejected')>Rejected</option></select>
                                <button class="rounded-lg bg-red-900 px-3 py-1 text-xs font-bold text-white hover:bg-red-800">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-sm text-gray-500">No document requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t p-4">{{ $requests->links() }}</div>
</div>
@endsection
