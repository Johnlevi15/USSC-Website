@extends('layouts.admin')
@section('title', 'Create Document Type | USSC Admin')
@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Create Document Type</h1>

    <form method="POST" action="{{ route('admin.document-types.store') }}" class="space-y-4 rounded-lg border bg-white p-6">
        @csrf

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Name
            <input required name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Description (optional)
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </label>

        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded">
            Active (shown to users)
        </label>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('admin.document-types.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Create</button>
        </div>
    </form>
</div>
@endsection
