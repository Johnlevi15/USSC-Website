@extends('layouts.admin')
@section('title', 'Create Document Type | USSC Admin')
@section('content')
<div class="w-full">
    <h1 class="mb-6 text-2xl font-bold">Create Document Type</h1>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
        <div class="space-y-6">
            <form id="create-document-type-form" method="POST" action="{{ route('admin.document-types.store') }}" class="space-y-4 rounded-lg border bg-white p-4 sm:p-6">
                @csrf

                <input type="hidden" id="field-draft-json" name="field_draft_json" value='[]'>

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

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.document-types.index') }}" class="inline-flex justify-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50">Cancel</a>
                    <button class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Create</button>
                </div>
            </form>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <div class="rounded-lg border bg-white p-4 sm:p-6">
                <h2 class="mb-4 text-lg font-bold">Form Fields</h2>

                <div id="draft-fields-list" class="mb-4 space-y-2"></div>

                <form id="draft-field-form" class="space-y-3 rounded-lg border-2 border-dashed border-gray-300 p-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase">
                            Field Label
                            <input required name="field_label" placeholder="e.g. Student ID Number" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                        </label>
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

                    <div class="block text-xs font-bold text-gray-600 uppercase">
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
                        <input name="validation_rules" placeholder="e.g. id:##-#### or max:255|alpha_num" aria-describedby="validation-rule-help-new" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal normal-case">
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

                    <div class="flex flex-col-reverse gap-2 sm:flex-row">
                        <button type="submit" class="rounded-lg bg-red-900 px-4 py-2 text-xs font-semibold text-white hover:bg-red-800">
                            <i class="fa-solid fa-plus mr-1"></i> Add Field
                        </button>
                        <button type="reset" class="rounded-lg border px-4 py-2 text-xs font-semibold hover:bg-gray-50">Clear</button>
                    </div>
                </form>
            </div>
        </aside>
    </div>
</div>

<script>
    function createOptionInput(value = '', placeholder = 'Option name', onChange, onRemove) {
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

    function syncDraftFieldOptions(form) {
        const fieldOptionsWrapper = form.querySelector('.field-options-wrapper');
        const fieldOptions = form.querySelector('.field-options');
        const selectOptionsBuilder = form.querySelector('.select-options-builder');
        const groupsContainer = form.querySelector('.option-groups');
        const flatOptionsContainer = form.querySelector('.flat-options-list');
        const fieldTypeSelect = form.querySelector('.field-type-select');
        const acceptsOptions = ['select', 'checkbox'].includes(fieldTypeSelect.value);
        const isSelect = fieldTypeSelect.value === 'select';

        fieldOptionsWrapper.classList.toggle('hidden', ! acceptsOptions);
        fieldOptions.closest('.field-options-textarea-wrapper').classList.toggle('hidden', isSelect);
        selectOptionsBuilder.classList.toggle('hidden', ! isSelect);
        fieldOptions.disabled = ! acceptsOptions;

        if (! acceptsOptions) {
            fieldOptions.value = '';
            return;
        }

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
            const option = optionInput.value.trim();
            if (option) {
                options.push(option);
            }
        });
        fieldOptions.value = options.join('\n');
    }

    function addGroupOption(group, value = '', syncFn) {
        const optionList = group.querySelector('.option-group-values');
        const { row, input } = createOptionInput(value, 'Option name', syncFn, () => {
            row.remove();
            syncFn();
        });
        input.classList.add('group-option');
        optionList.appendChild(row);
    }

    function addGroup(groupName = '', options = [], syncFn) {
        const groupsContainer = document.querySelector('#draft-field-form .option-groups');
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
        groupNameInput.addEventListener('input', syncFn);
        groupNameLabel.appendChild(groupNameInput);

        const optionList = document.createElement('div');
        optionList.className = 'option-group-values space-y-2';

        const actions = document.createElement('div');
        actions.className = 'flex flex-wrap gap-2';

        const addOptionButton = document.createElement('button');
        addOptionButton.type = 'button';
        addOptionButton.textContent = '+ Add option';
        addOptionButton.className = 'rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50';
        addOptionButton.addEventListener('click', () => addGroupOption(group, '', syncFn));

        const removeGroupButton = document.createElement('button');
        removeGroupButton.type = 'button';
        removeGroupButton.textContent = 'Remove group';
        removeGroupButton.className = 'rounded-lg px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50';
        removeGroupButton.addEventListener('click', () => {
            group.remove();
            syncFn();
        });

        actions.append(addOptionButton, removeGroupButton);
        group.append(groupNameLabel, optionList, actions);
        groupsContainer.appendChild(group);
        (options.length ? options : ['']).forEach((option) => addGroupOption(group, option, syncFn));
    }

    function addFlatOption(value = '', syncFn) {
        const form = document.getElementById('draft-field-form');
        const flatOptionsContainer = form.querySelector('.flat-options-list');
        const { row, input } = createOptionInput(value, 'e.g. Other', syncFn, () => {
            row.remove();
            syncFn();
        });
        input.classList.add('flat-option');
        flatOptionsContainer.appendChild(row);
    }

    function renderDraftFields() {
        const fields = JSON.parse(document.getElementById('field-draft-json').value || '[]');
        const list = document.getElementById('draft-fields-list');
        list.innerHTML = '';

        if (! fields.length) {
            list.innerHTML = '<p class="text-sm text-gray-500">No fields added yet. Add your first field to this document type.</p>';
            return;
        }

        fields.forEach((field, index) => {
            const card = document.createElement('div');
            card.className = 'rounded-lg border bg-gray-50 p-3';
            card.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="break-words text-sm font-semibold text-gray-900">${field.field_label}</p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <span class="rounded bg-gray-200 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600">${field.field_type}</span>
                            ${field.is_required ? '<span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">Required</span>' : ''}
                        </div>
                    </div>
                    <button type="button" data-index="${index}" class="remove-draft-field rounded-lg px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100">Remove</button>
                </div>
            `;
            list.appendChild(card);
        });

        list.querySelectorAll('.remove-draft-field').forEach((button) => {
            button.addEventListener('click', () => {
                const fields = JSON.parse(document.getElementById('field-draft-json').value || '[]');
                fields.splice(Number(button.dataset.index), 1);
                document.getElementById('field-draft-json').value = JSON.stringify(fields);
                renderDraftFields();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('draft-field-form');
        const fieldTypeSelect = form.querySelector('.field-type-select');
        const fieldOptions = form.querySelector('.field-options');
        const groupsContainer = form.querySelector('.option-groups');
        const flatOptionsContainer = form.querySelector('.flat-options-list');
        const addGroupButton = form.querySelector('.add-option-group');
        const addFlatOptionButton = form.querySelector('.add-flat-option');
        const mainForm = document.getElementById('create-document-type-form');
        const draftFieldsInput = document.getElementById('field-draft-json');

        const syncFn = () => syncDraftFieldOptions(form);

        addGroupButton.addEventListener('click', () => addGroup('', [], syncFn));
        addFlatOptionButton.addEventListener('click', () => addFlatOption('', syncFn));
        fieldTypeSelect.addEventListener('change', syncFn);
        syncFn();

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const fieldLabel = form.querySelector('[name="field_label"]').value.trim();
            if (! fieldLabel) {
                form.querySelector('[name="field_label"]').focus();
                return;
            }

            const fields = JSON.parse(draftFieldsInput.value || '[]');
            fields.push({
                field_label: fieldLabel,
                field_type: fieldTypeSelect.value,
                field_options: fieldOptions.value || null,
                validation_rules: form.querySelector('[name="validation_rules"]').value.trim() || null,
                is_required: form.querySelector('[name="is_required"]').checked,
            });

            draftFieldsInput.value = JSON.stringify(fields);
            renderDraftFields();
            form.reset();
            syncFn();
            fieldTypeSelect.value = 'text';
            syncFn();
        });

        mainForm.addEventListener('submit', () => {
            if (! draftFieldsInput.value) {
                draftFieldsInput.value = '[]';
            }
        });

        renderDraftFields();
    });
</script>
@endsection
