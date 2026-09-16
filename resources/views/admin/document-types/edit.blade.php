@extends('layouts.admin')
@section('title', 'Edit Document Type | USSC Admin')
@section('content')
<div class="max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold">Edit: {{ $documentType->name }}</h1>

    {{-- Document Type Info --}}
    <form method="POST" action="{{ route('admin.document-types.update', $documentType) }}" class="space-y-4 rounded-lg border bg-white p-6">
        @csrf @method('PUT')

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Name
            <input required name="name" value="{{ old('name', $documentType->name) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </label>

        <label class="block text-xs font-bold text-gray-600 uppercase">
            Description
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description', $documentType->description) }}</textarea>
        </label>

        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $documentType->is_active) ? 'checked' : '' }} class="rounded">
            Active (shown to users)
        </label>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('admin.document-types.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Save Changes</button>
        </div>
    </form>

    {{-- Fields Management --}}
    <div class="rounded-lg border bg-white p-6">
        <h2 class="text-lg font-bold mb-4">Form Fields</h2>

        @if($documentType->fields->isEmpty())
            <p class="text-sm text-gray-500 mb-4">No fields yet. Add your first field below.</p>
        @else
            <div id="fields-list" class="space-y-2 mb-6">
                @foreach($documentType->fields as $field)
                <div class="flex items-center gap-3 rounded-lg border bg-gray-50 px-4 py-3 text-sm" data-id="{{ $field->id }}">
                    <span class="cursor-grab text-gray-400 hover:text-gray-600"><i class="fa-solid fa-grip-vertical"></i></span>
                    <span class="font-semibold">{{ $field->field_label }}</span>
                    <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-600">{{ $field->field_type }}</span>
                    @if($field->is_required)
                        <span class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">Required</span>
                    @endif
                    <div class="ml-auto flex gap-2">
                        <form method="POST" action="{{ route('admin.document-types.fields.delete', $field) }}" onsubmit="return confirm('Delete this field?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-xs"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- Add Field Form --}}
        <div class="rounded-lg border-2 border-dashed border-gray-300 p-4">
            <h3 class="text-sm font-bold text-gray-600 mb-3">Add New Field</h3>
            <form method="POST" action="{{ route('admin.document-types.fields.add', $documentType) }}" class="space-y-3">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Name (internal)
                        <input required name="field_name" pattern="[a-z_]+" placeholder="e.g. student_id" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                        <span class="text-[10px] text-gray-400 normal-case">Lowercase letters and underscores only</span>
                    </label>
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Label (display)
                        <input required name="field_label" placeholder="e.g. Student ID Number" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Type
                        <select required name="field_type" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Select (dropdown)</option>
                        </select>
                    </label>
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Validation Rules (optional)
                        <input name="validation_rules" placeholder="e.g. max:255" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    </label>
                </div>

                <label class="block text-xs font-bold text-gray-600 uppercase">
                    Options for Select (JSON array, optional)
                    <textarea name="field_options" rows="2" placeholder='["Option 1", "Option 2"]' class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-mono"></textarea>
                </label>

                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="checkbox" name="is_required" value="1" class="rounded">
                    Required field
                </label>

                <button class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
                    <i class="fa-solid fa-plus mr-1"></i> Add Field
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
