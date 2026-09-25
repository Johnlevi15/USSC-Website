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
                <div class="rounded-lg border bg-gray-50 text-sm" data-id="{{ $field->id }}">
                    <div class="flex items-center gap-3 px-4 py-3">
                        <span class="cursor-grab text-gray-400 hover:text-gray-600"><i class="fa-solid fa-grip-vertical"></i></span>
                        <span class="font-semibold">{{ $field->field_label }}</span>
                        <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-600">{{ $field->field_type }}</span>
                        @if($field->is_required)
                            <span class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">Required</span>
                        @endif
                        <div class="ml-auto flex gap-2">
                            <button type="button" onclick="toggleFieldEditor('field-editor-{{ $field->id }}')" class="text-xs font-semibold text-red-900 hover:text-red-700">
                                <i class="fa-solid fa-pen-to-square mr-1"></i>Edit
                            </button>
                            <form method="POST" action="{{ route('admin.document-types.fields.delete', $field) }}" onsubmit="return confirm('Delete this field?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-xs"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <div id="field-editor-{{ $field->id }}" class="hidden border-t bg-white p-4">
                        <form method="POST" action="{{ route('admin.document-types.fields.update', $field) }}" class="space-y-3">
                            @csrf @method('PUT')

                            <div class="grid grid-cols-2 gap-3">
                                <label class="block text-xs font-bold text-gray-600 uppercase">
                                    Field Label
                                    <input required name="field_label" value="{{ $field->field_label }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                                </label>
                                <label class="block text-xs font-bold text-gray-600 uppercase">
                                    Field Type
                                    <select required name="field_type" class="field-type-select mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                                        <option value="text" @selected($field->field_type === 'text')>Text</option>
                                        <option value="email" @selected($field->field_type === 'email')>Email</option>
                                        <option value="textarea" @selected($field->field_type === 'textarea')>Textarea</option>
                                        <option value="number" @selected($field->field_type === 'number')>Number</option>
                                        <option value="date" @selected($field->field_type === 'date')>Date</option>
                                        <option value="select" @selected($field->field_type === 'select')>Select (dropdown)</option>
                                        <option value="checkbox" @selected($field->field_type === 'checkbox')>Checkbox</option>
                                        <option value="file" @selected($field->field_type === 'file')>File Upload</option>
                                        <option value="image" @selected($field->field_type === 'image')>Image Upload</option>
                                    </select>
                                </label>
                            </div>

                            <label class="block text-xs font-bold text-gray-600 uppercase">
                                Validation Rules (optional)
                                <input name="validation_rules" value="{{ $field->validation_rules }}" placeholder="e.g. max:255" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                            </label>

                            <label class="field-options-wrapper hidden text-xs font-bold text-gray-600 uppercase">
                                Options for Select or Checkbox
                                <textarea name="field_options" rows="4" placeholder="Option 1&#10;Option 2&#10;Other" class="field-options mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ $field->field_options ? implode("\n", $field->field_options) : '' }}</textarea>
                                <span class="text-[10px] text-gray-400 normal-case">Enter one option per line. Use "Other" to let users type a custom answer.</span>
                            </label>

                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                                <input type="checkbox" name="is_required" value="1" @checked($field->is_required) class="rounded">
                                Required field
                            </label>

                            <div class="flex gap-2">
                                <button class="rounded-lg bg-red-900 px-4 py-2 text-xs font-semibold text-white hover:bg-red-800">Save Field</button>
                                <button type="button" onclick="toggleFieldEditor('field-editor-{{ $field->id }}')" class="rounded-lg border px-4 py-2 text-xs font-semibold hover:bg-gray-50">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- Add Field Form --}}
        <div class="rounded-lg border-2 border-dashed border-gray-300 p-4">
            <h3 class="text-sm font-bold text-gray-600 mb-3">Add New Field</h3>
            <form id="add-field-form" method="POST" action="{{ route('admin.document-types.fields.add', $documentType) }}" class="space-y-3">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Name (internal)
                        <input name="field_name" pattern="[a-z_]+" placeholder="auto-generated from label" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                        <span class="text-[10px] text-gray-400 normal-case">Generated from the label. Lowercase letters and underscores only.</span>
                    </label>
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Label (display)
                        <input required name="field_label" placeholder="e.g. Student ID Number" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Type
                        <select required name="field_type" class="field-type-select mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Select (dropdown)</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="file">File Upload</option>
                            <option value="image">Image Upload</option>
                        </select>
                    </label>
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Validation Rules (optional)
                        <input name="validation_rules" placeholder="e.g. max:255" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    </label>
                </div>

                <label class="field-options-wrapper hidden text-xs font-bold text-gray-600 uppercase">
                    Options for Select or Checkbox
                    <textarea name="field_options" rows="4" placeholder="Option 1&#10;Option 2&#10;Other" class="field-options mt-1 w-full rounded-lg border px-3 py-2 text-sm"></textarea>
                    <span class="text-[10px] text-gray-400 normal-case">Enter one option per line. Use "Other" to let users type a custom answer.</span>
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
<script>
    function toggleFieldEditor(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function toggleFieldOptions(select) {
        const form = select.closest('form');
        const fieldOptionsWrapper = form.querySelector('.field-options-wrapper');
        const fieldOptions = form.querySelector('.field-options');
        const acceptsOptions = ['select', 'checkbox'].includes(select.value);

        fieldOptionsWrapper.classList.toggle('hidden', ! acceptsOptions);
        fieldOptionsWrapper.classList.toggle('block', acceptsOptions);
        fieldOptions.disabled = ! acceptsOptions;

        if (! acceptsOptions) {
            fieldOptions.value = '';
        }
    }

    document.querySelectorAll('.field-type-select').forEach((select) => {
        select.addEventListener('change', () => toggleFieldOptions(select));
        toggleFieldOptions(select);
    });

    const addFieldForm = document.getElementById('add-field-form');

    if (addFieldForm) {
        const fieldName = addFieldForm.querySelector('input[name="field_name"]');
        const fieldLabel = addFieldForm.querySelector('input[name="field_label"]');
        let fieldNameWasEdited = false;

        const generatedFieldName = (label) => label
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .slice(0, 100);

        fieldName.addEventListener('input', () => {
            fieldNameWasEdited = fieldName.value.trim() !== '';
        });

        fieldLabel.addEventListener('input', () => {
            if (! fieldNameWasEdited) {
                fieldName.value = generatedFieldName(fieldLabel.value);
            }
        });

        fieldLabel.addEventListener('blur', () => {
            if (fieldName.value.trim() === '') {
                fieldName.value = generatedFieldName(fieldLabel.value);
                fieldNameWasEdited = false;
            }
        });
    }
</script>
@endsection
