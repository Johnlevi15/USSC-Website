@extends('layouts.admin')
@section('title', 'Edit Document Type | USSC Admin')
@section('content')
<div class="max-w-3xl space-y-6">
    <h1 class="break-words text-2xl font-bold">Edit: {{ $documentType->name }}</h1>

    {{-- Document Type Info --}}
    <form method="POST" action="{{ route('admin.document-types.update', $documentType) }}" class="space-y-4 rounded-lg border bg-white p-4 sm:p-6">
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

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center">
            <a href="{{ route('admin.document-types.index') }}" class="inline-flex justify-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Save Changes</button>
        </div>
    </form>

    {{-- Fields Management --}}
    <div class="rounded-lg border bg-white p-4 sm:p-6">
        <h2 class="text-lg font-bold mb-4">Form Fields</h2>

        @if($documentType->fields->isEmpty())
            <p class="text-sm text-gray-500 mb-4">No fields yet. Add your first field below.</p>
        @else
            <div id="fields-list" class="space-y-2 mb-6">
                @foreach($documentType->fields as $field)
                @php
                    $fieldValidationRules = str_replace('id_format:', 'id:', $field->validation_rules ?? '');
                @endphp
                <div class="rounded-lg border bg-gray-50 text-sm" data-id="{{ $field->id }}">
                    <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <span class="mt-0.5 cursor-grab text-gray-400 hover:text-gray-600"><i class="fa-solid fa-grip-vertical"></i></span>
                            <div class="min-w-0">
                                <span class="block break-words font-semibold">{{ $field->field_label }}</span>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-600">{{ $field->field_type }}</span>
                                    @if($field->is_required)
                                        <span class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">Required</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0">
                            <button type="button" onclick="toggleFieldEditor('field-editor-{{ $field->id }}')" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.document-types.fields.delete', $field) }}" class="document-field-delete-form" data-field-name="{{ $field->field_label }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-red-700 px-3 py-2 text-xs font-bold text-white hover:bg-red-800">
                                    <i class="fa-solid fa-trash"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="field-editor-{{ $field->id }}" class="hidden border-t bg-white p-4">
                        <form method="POST" action="{{ route('admin.document-types.fields.update', $field) }}" class="space-y-3">
                            @csrf @method('PUT')

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="block text-xs font-bold text-gray-600 uppercase">
                                    Field Label
                                    <input required name="field_label" value="{{ $field->field_label }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                                </label>
                                <label class="block text-xs font-bold text-gray-600 uppercase">
                                    Field Type
                                    <select required name="field_type" class="field-type-select mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                                        <option value="text" @selected($field->field_type === 'text')>Text</option>
                                        <option value="email" @selected($field->field_type === 'email')>Email</option>
                                        <option value="phone" @selected($field->field_type === 'phone')>Phone Number (Philippines)</option>
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

                            <div class="block text-xs font-bold text-gray-600 uppercase">
                                <span class="flex items-center gap-1.5">
                                    Validation Rules (optional)
                                    <span class="group relative inline-flex normal-case">
                                        <button type="button" aria-label="Show validation rule examples" aria-describedby="validation-rule-help-{{ $field->id }}" class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-400 text-[10px] font-bold text-gray-600 hover:border-red-800 hover:text-red-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-800">?</button>
                                        <span id="validation-rule-help-{{ $field->id }}" role="tooltip" class="invisible absolute left-0 top-full z-50 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-3 text-left text-xs font-normal normal-case text-gray-700 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                                            <span class="mb-2 block font-bold text-gray-900">Basic rule examples</span>
                                            <span class="mb-1 block"><code class="font-semibold">max:255</code> — limit text to 255 characters.</span>
                                            <span class="mb-1 block"><code class="font-semibold">size:10</code> — require exactly 10 characters.</span>
                                            <span class="mb-1 block"><code class="font-semibold">id:##-####</code> — ID mask; e.g. 24-0001.</span>
                                            <span class="mt-2 block text-gray-500">Combine rules with |, e.g. id:##-####|max:20.</span>
                                        </span>
                                    </span>
                                </span>
                                <input id="validation-rules-{{ $field->id }}" name="validation_rules" value="{{ $fieldValidationRules }}" placeholder="e.g. id:##-#### or max:255|alpha_num" aria-describedby="validation-rule-help-{{ $field->id }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case">
                            </div>

                            <div class="field-options-wrapper hidden space-y-3">
                                <div class="select-options-builder hidden space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <div>
                                        <p class="text-xs font-bold text-gray-700">Group options</p>
                                        <p class="mt-1 text-[11px] font-normal normal-case text-gray-500">Add a group, then list the options in it. Options such as “Other” can be added outside groups.</p>
                                    </div>
                                    <div class="option-groups space-y-3"></div>
                                    <button type="button" class="add-option-group rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100">+ Add a group</button>
                                    <div class="border-t border-gray-200 pt-3">
                                        <p class="text-xs font-bold text-gray-700">Options outside groups</p>
                                        <div class="flat-options-list mt-2 space-y-2"></div>
                                        <button type="button" class="add-flat-option mt-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100">+ Add standalone option</button>
                                    </div>
                                </div>
                                <div class="field-options-textarea-wrapper">
                                    <label class="block text-xs font-bold text-gray-600">
                                        Options (one per line)
                                        <textarea name="field_options" rows="4" placeholder="Option 1&#10;Other" class="field-options mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case">{{ $field->field_options ? implode("\n", $field->field_options) : '' }}</textarea>
                                    </label>
                                    <span class="field-options-help text-[10px] font-normal normal-case text-gray-400">Enter one option per line. Use "Other" to let users type a custom answer.</span>
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                                <input type="checkbox" name="is_required" value="1" @checked($field->is_required) class="rounded">
                                Required field
                            </label>

                            <div class="flex flex-col-reverse gap-2 sm:flex-row">
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

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="block text-xs font-bold text-gray-600 uppercase">
                        Field Label (display)
                        <input required name="field_label" placeholder="e.g. Student ID Number" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase">
                        Field Type
                        <select required name="field_type" class="field-type-select mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone Number (Philippines)</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Select (dropdown)</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="file">File Upload</option>
                            <option value="image">Image Upload</option>
                        </select>
                    </label>
                </div>

                <label class="block text-xs font-bold text-gray-600 uppercase">
                    <span class="flex items-center gap-1.5">
                        Validation Rules (optional)
                        <span class="group relative inline-flex normal-case">
                            <button type="button" aria-label="Show validation rule examples" aria-describedby="validation-rule-help-new" class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-400 text-[10px] font-bold text-gray-600 hover:border-red-800 hover:text-red-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-800">?</button>
                            <span id="validation-rule-help-new" role="tooltip" class="invisible absolute left-0 top-full z-50 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-3 text-left text-xs font-normal normal-case text-gray-700 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                                <span class="mb-2 block font-bold text-gray-900">Basic rule examples</span>
                                <span class="mb-1 block"><code class="font-semibold">max:255</code> — limit text to 255 characters.</span>
                                <span class="mb-1 block"><code class="font-semibold">size:10</code> — require exactly 10 characters.</span>
                                <span class="mb-1 block"><code class="font-semibold">id:##-####</code> — ID mask; e.g. 24-0001.</span>
                                <span class="mt-2 block text-gray-500">Combine rules with |, e.g. id:##-####|max:20.</span>
                            </span>
                        </span>
                    </span>
                    <input id="validation-rules-new" name="validation_rules" placeholder="e.g. id:##-#### or max:255|alpha_num" aria-describedby="validation-rule-help-new" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case">
                </div>

                <div class="field-options-wrapper hidden space-y-3">
                    <div class="select-options-builder hidden space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <div>
                            <p class="text-xs font-bold text-gray-700">Group options</p>
                            <p class="mt-1 text-[11px] font-normal normal-case text-gray-500">Add a group, then list the options in it. Options such as “Other” can be added outside groups.</p>
                        </div>
                        <div class="option-groups space-y-3"></div>
                        <button type="button" class="add-option-group rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100">+ Add a group</button>
                        <div class="border-t border-gray-200 pt-3">
                            <p class="text-xs font-bold text-gray-700">Options outside groups</p>
                            <div class="flat-options-list mt-2 space-y-2"></div>
                            <button type="button" class="add-flat-option mt-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100">+ Add standalone option</button>
                        </div>
                    </div>
                    <div class="field-options-textarea-wrapper">
                        <label class="block text-xs font-bold text-gray-600">
                            Options (one per line)
                            <textarea name="field_options" rows="4" placeholder="Option 1&#10;Other" class="field-options mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case"></textarea>
                        </label>
                        <span class="field-options-help text-[10px] font-normal normal-case text-gray-400">Enter one option per line. Use "Other" to let users type a custom answer.</span>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="checkbox" name="is_required" value="1" class="rounded">
                    Required field
                </label>

                <button class="w-full rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 sm:w-auto">
                    <i class="fa-solid fa-plus mr-1"></i> Add Field
                </button>
            </form>
        </div>
    </div>
</div>

<div id="deleteDocumentFieldModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/35 p-4 backdrop-blur-[1px]" role="dialog" aria-modal="true" aria-labelledby="deleteDocumentFieldTitle">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="border-b px-5 py-4">
            <h2 id="deleteDocumentFieldTitle" class="text-lg font-bold text-gray-900">Delete Form Field?</h2>
            <p class="mt-1 text-sm text-gray-500">This action cannot be undone.</p>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-700">You are about to delete <span id="deleteDocumentFieldName" class="font-bold text-gray-900"></span>.</p>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t bg-gray-50 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" id="cancelDeleteDocumentField" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" id="confirmDeleteDocumentField" class="rounded-lg bg-red-900 px-4 py-2 text-sm font-bold text-white hover:bg-red-800">Delete field</button>
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
        const fieldOptionsTextareaWrapper = form.querySelector('.field-options-textarea-wrapper');
        const selectOptionsBuilder = form.querySelector('.select-options-builder');
        const acceptsOptions = ['select', 'checkbox'].includes(select.value);
        const isSelect = select.value === 'select';

        fieldOptionsWrapper.classList.toggle('hidden', ! acceptsOptions);
        fieldOptionsTextareaWrapper.classList.toggle('hidden', isSelect);
        selectOptionsBuilder.classList.toggle('hidden', ! isSelect);
        fieldOptions.disabled = ! acceptsOptions;

        if (! acceptsOptions) {
            fieldOptions.value = '';
        }
    }

    function createOptionInput(value, placeholder, onChange, onRemove) {
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';

        const input = document.createElement('input');
        input.type = 'text';
        input.value = value;
        input.placeholder = placeholder;
        input.className = 'min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-normal normal-case';
        input.addEventListener('input', onChange);

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Remove';
        removeButton.className = 'rounded-lg px-2 py-2 text-xs font-semibold text-red-700 hover:bg-red-50';
        removeButton.addEventListener('click', onRemove);

        row.append(input, removeButton);
        return { row, input };
    }

    function initializeFieldEditor(form) {
        const fieldTypeSelect = form.querySelector('.field-type-select');
        if (!fieldTypeSelect) {
            return;
        }

        const fieldOptions = form.querySelector('.field-options');
        const groupsContainer = form.querySelector('.option-groups');
        const flatOptionsContainer = form.querySelector('.flat-options-list');
        const addGroupButton = form.querySelector('.add-option-group');
        const addFlatOptionButton = form.querySelector('.add-flat-option');
        function syncOptions() {
            const options = [];

            groupsContainer.querySelectorAll('.option-group-card').forEach((group) => {
                const groupName = group.querySelector('.option-group-name').value.trim();
                group.querySelectorAll('.group-option').forEach((optionInput) => {
                    const option = optionInput.value.trim();
                    if (groupName && option) {
                        options.push(`${groupName} > ${option}`);
                    }
                });
            });

            flatOptionsContainer.querySelectorAll('.flat-option').forEach((optionInput) => {
                if (optionInput.value.trim()) {
                    options.push(optionInput.value.trim());
                }
            });

            fieldOptions.value = options.join('\n');
        }

        function addGroupOption(group, value = '') {
            const optionList = group.querySelector('.option-group-values');
            const { row, input } = createOptionInput(value, 'Option name', syncOptions, () => {
                row.remove();
                syncOptions();
            });
            input.classList.add('group-option');
            optionList.appendChild(row);
        }

        function addGroup(groupName = '', options = []) {
            const group = document.createElement('section');
            group.className = 'option-group-card space-y-2 rounded-lg border border-gray-200 bg-white p-3';

            const groupNameLabel = document.createElement('label');
            groupNameLabel.className = 'block text-xs font-semibold text-gray-700';
            groupNameLabel.textContent = 'Group name';

            const groupNameInput = document.createElement('input');
            groupNameInput.type = 'text';
            groupNameInput.value = groupName;
            groupNameInput.placeholder = 'e.g. Year level or Category';
            groupNameInput.className = 'option-group-name mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-normal';
            groupNameInput.addEventListener('input', syncOptions);
            groupNameLabel.appendChild(groupNameInput);

            const optionList = document.createElement('div');
            optionList.className = 'option-group-values space-y-2';

            const actions = document.createElement('div');
            actions.className = 'flex flex-wrap gap-2';

            const addOptionButton = document.createElement('button');
            addOptionButton.type = 'button';
            addOptionButton.textContent = '+ Add option';
            addOptionButton.className = 'rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50';
            addOptionButton.addEventListener('click', () => addGroupOption(group));

            const removeGroupButton = document.createElement('button');
            removeGroupButton.type = 'button';
            removeGroupButton.textContent = 'Remove group';
            removeGroupButton.className = 'rounded-lg px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50';
            removeGroupButton.addEventListener('click', () => {
                group.remove();
                syncOptions();
            });

            actions.append(addOptionButton, removeGroupButton);
            group.append(groupNameLabel, optionList, actions);
            groupsContainer.appendChild(group);
            (options.length ? options : ['']).forEach((option) => addGroupOption(group, option));
        }

        function addFlatOption(value = '') {
            const { row, input } = createOptionInput(value, 'e.g. Other', syncOptions, () => {
                row.remove();
                syncOptions();
            });
            input.classList.add('flat-option');
            flatOptionsContainer.appendChild(row);
        }

        fieldOptions.value.split(/\r?\n/).forEach((line) => {
            const separatorIndex = line.indexOf(' > ');
            if (separatorIndex > 0) {
                const groupName = line.slice(0, separatorIndex).trim();
                const option = line.slice(separatorIndex + 3).trim();
                const existingGroup = Array.from(groupsContainer.querySelectorAll('.option-group-card'))
                    .find((group) => group.querySelector('.option-group-name').value === groupName);

                if (existingGroup) {
                    addGroupOption(existingGroup, option);
                } else {
                    addGroup(groupName, [option]);
                }
            } else if (line.trim()) {
                addFlatOption(line.trim());
            }
        });

        addGroupButton.addEventListener('click', () => addGroup());
        addFlatOptionButton.addEventListener('click', () => addFlatOption());
        fieldTypeSelect.addEventListener('change', () => {
            toggleFieldOptions(fieldTypeSelect);
        });
        toggleFieldOptions(fieldTypeSelect);

        form.addEventListener('submit', () => {
            syncOptions();
        });
    }

    document.querySelectorAll('form').forEach(initializeFieldEditor);

    const deleteDocumentFieldModal = document.getElementById('deleteDocumentFieldModal');
    const deleteDocumentFieldName = document.getElementById('deleteDocumentFieldName');
    const cancelDeleteDocumentField = document.getElementById('cancelDeleteDocumentField');
    const confirmDeleteDocumentField = document.getElementById('confirmDeleteDocumentField');
    let pendingDocumentFieldDeleteForm = null;

    function closeDeleteDocumentFieldModal() {
        deleteDocumentFieldModal.classList.add('hidden');
        deleteDocumentFieldModal.classList.remove('flex');
        pendingDocumentFieldDeleteForm = null;
    }

    document.querySelectorAll('.document-field-delete-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            pendingDocumentFieldDeleteForm = form;
            deleteDocumentFieldName.textContent = form.dataset.fieldName || 'this field';
            deleteDocumentFieldModal.classList.remove('hidden');
            deleteDocumentFieldModal.classList.add('flex');
            confirmDeleteDocumentField.focus();
        });
    });

    cancelDeleteDocumentField.addEventListener('click', closeDeleteDocumentFieldModal);
    deleteDocumentFieldModal.addEventListener('click', (event) => {
        if (event.target === deleteDocumentFieldModal) {
            closeDeleteDocumentFieldModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! deleteDocumentFieldModal.classList.contains('hidden')) {
            closeDeleteDocumentFieldModal();
        }
    });
    confirmDeleteDocumentField.addEventListener('click', () => {
        if (pendingDocumentFieldDeleteForm) {
            pendingDocumentFieldDeleteForm.submit();
        }
    });
</script>
@endsection
