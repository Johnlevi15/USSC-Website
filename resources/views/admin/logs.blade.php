@extends('layouts.admin')
@section('title', 'Activity Logs | USSC Admin')
@section('content')
<div>
    <h2 class="text-2xl font-extrabold text-gray-900">Admin Activity Logs</h2>
    <p class="text-sm text-gray-500">Review actions performed in the administration panel.</p>
</div>

<section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="divide-y">
        @forelse($logs as $log)
            <article class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-900">
                        <i class="fa-solid fa-{{ str_contains($log->action, 'created') ? 'plus' : 'pen-to-square' }} text-xs"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">{{ $log->description }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $log->admin?->user?->name ?? 'Unknown admin' }} · {{ str_replace('_', ' ', $log->action) }}</p>
                    </div>
                </div>
                <time class="text-xs text-gray-400" datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->format('M j, Y g:i A') }}</time>
            </article>
        @empty
            <p class="p-10 text-center text-sm text-gray-500">No admin activity has been recorded yet.</p>
        @endforelse
    </div>
    <div class="border-t p-4">{{ $logs->links() }}</div>
</section>
@endsection
