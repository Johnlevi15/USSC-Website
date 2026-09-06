<form method="POST" action="{{ route('admin.lost-found.update', $item) }}" class="mt-4 grid grid-cols-1 gap-2 border-t pt-3 sm:grid-cols-3">
    @csrf @method('PATCH')
    <select name="approval_status" class="min-w-0 rounded-lg border px-2 py-2 text-xs"><option value="approved" @selected($item->approval_status === 'approved')>Approve</option><option value="rejected" @selected($item->approval_status === 'rejected')>Reject</option><option value="pending" @selected($item->approval_status === 'pending')>Keep Pending</option></select>
    <select name="status" class="min-w-0 rounded-lg border px-2 py-2 text-xs"><option value="lost" @selected($item->status === 'lost')>Lost</option><option value="found" @selected($item->status === 'found')>Found</option><option value="claimed" @selected($item->status === 'claimed')>Claimed</option></select>
    <button class="rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">Save</button>
</form>
