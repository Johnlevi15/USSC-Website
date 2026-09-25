<form method="POST" action="{{ route('admin.lost-found.update', $item) }}" @class([
    'grid grid-cols-1 gap-2 border-t pt-3 sm:grid-cols-3' => empty($reviewPage),
    'space-y-4 p-5' => ! empty($reviewPage),
])>
    @csrf
    @method('PATCH')

    <div>
        <label for="approval_status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-600">Approval Status</label>
        <select id="approval_status" name="approval_status" class="min-w-0 rounded-lg border px-2 py-2 text-xs">
            <option value="approved" @selected($item->approval_status === 'approved')>Approved</option>
            <option value="rejected" @selected($item->approval_status === 'rejected')>Rejected</option>
            <option value="pending" @selected($item->approval_status === 'pending')>Keep Pending</option>
        </select>
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-600">Item Status</label>
        <select id="status" name="status" class="min-w-0 rounded-lg border px-2 py-2 text-xs">
            <option value="lost" @selected($item->status === 'lost')>Lost</option>
            <option value="found" @selected($item->status === 'found')>Found</option>
            <option value="claimed" @selected($item->status === 'claimed')>Claimed</option>
        </select>
    </div>

    <div @class([
        'sm:col-span-3' => empty($reviewPage),
    ])>
        <label for="admin_remarks" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-600">Admin Remarks</label>
        <textarea
            id="admin_remarks"
            name="admin_remarks"
            rows="{{ ! empty($reviewPage) ? 4 : 2 }}"
            class="w-full rounded-lg border px-2 py-2 text-xs"
            placeholder="Optional note for records or email notification"
        >{{ old('admin_remarks', $item->admin_remarks) }}</textarea>
    </div>

    <label @class([
        'flex items-center gap-2 text-xs text-gray-600' => true,
        'sm:col-span-3' => empty($reviewPage),
    ])>
        <input type="checkbox" name="notify_student" value="1" @checked(old('notify_student')) class="rounded border-gray-300 text-red-900 focus:ring-red-900">
        Notify student by email
    </label>

    <button class="rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800">Save Review</button>
</form>
