@extends('layouts.admin')
@section('title', 'Document Types | USSC Admin')
@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold">Document Types</h1>
        <a href="{{ route('admin.document-types.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 sm:w-auto">
            <i class="fa-solid fa-plus mr-1"></i> Create New
        </a>
    </div>

    @if($documentTypes->isEmpty())
        <div class="rounded-lg border bg-white p-8 text-center text-gray-500">
            No document types found. Create your first one to get started.
        </div>
    @else
        <div class="space-y-3 md:hidden">
            @foreach($documentTypes as $type)
                <article class="rounded-lg border bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="break-words text-sm font-bold text-gray-900">{{ $type->name }}</h2>
                            <p class="mt-1 text-xs text-gray-500">{{ $type->fields_count }} fields &middot; {{ $type->document_requests_count }} requests</p>
                        </div>
                        @if($type->is_active)
                            <span class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Active</span>
                        @else
                            <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Inactive</span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.document-types.edit', $type) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-pen"></i>
                            Edit
                        </a>
                        <form
                            method="POST"
                            action="{{ route('admin.document-types.destroy', $type) }}"
                            class="document-type-delete-form"
                            data-name="{{ $type->name }}"
                            data-requests="{{ $type->document_requests_count }}"
                        >
                            @csrf @method('DELETE')
                            @if($type->document_requests_count > 0)
                                <input type="hidden" name="delete_requests" value="1">
                            @endif
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-3 py-2 text-xs font-bold text-white hover:bg-red-800">
                                <i class="fa-solid fa-trash"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="hidden overflow-hidden rounded-lg border bg-white md:block">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Fields</th>
                        <th class="px-4 py-3">Requests</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($documentTypes as $type)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $type->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $type->fields_count }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $type->document_requests_count }}</td>
                        <td class="px-4 py-3">
                            @if($type->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.document-types.edit', $type) }}" class="text-red-900 hover:underline font-semibold text-xs">
                                <i class="fa-solid fa-pen mr-1"></i> Edit
                            </a>
                            <form
                                method="POST"
                                action="{{ route('admin.document-types.destroy', $type) }}"
                                class="inline document-type-delete-form"
                                data-name="{{ $type->name }}"
                                data-requests="{{ $type->document_requests_count }}"
                            >
                                @csrf @method('DELETE')
                                @if($type->document_requests_count > 0)
                                    <input type="hidden" name="delete_requests" value="1">
                                @endif
                                <button type="submit" class="ml-3 text-red-600 hover:underline font-semibold text-xs">
                                    <i class="fa-solid fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</div>

<div id="deleteDocumentTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/35 p-4 backdrop-blur-[1px]" role="dialog" aria-modal="true" aria-labelledby="deleteDocumentTypeTitle">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="border-b px-5 py-4">
            <h2 id="deleteDocumentTypeTitle" class="text-lg font-bold text-gray-900">Delete Document Type</h2>
            <p class="mt-1 text-sm text-gray-500">This action cannot be undone.</p>
        </div>

        <div class="space-y-3 p-5">
            <p class="text-sm text-gray-700">
                You are about to delete <span id="deleteDocumentTypeName" class="font-bold text-gray-900"></span>.
            </p>
            <p id="deleteDocumentTypeRequests" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"></p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t bg-gray-50 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" id="cancelDeleteDocumentType" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" id="confirmDeleteDocumentType" class="rounded-lg bg-red-900 px-4 py-2 text-sm font-bold text-white hover:bg-red-800">
                Delete
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('deleteDocumentTypeModal');
        const nameTarget = document.getElementById('deleteDocumentTypeName');
        const requestsTarget = document.getElementById('deleteDocumentTypeRequests');
        const cancelButton = document.getElementById('cancelDeleteDocumentType');
        const confirmButton = document.getElementById('confirmDeleteDocumentType');
        let pendingForm = null;

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingForm = null;
        };

        document.querySelectorAll('.document-type-delete-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                pendingForm = form;

                const requestCount = Number(form.dataset.requests || 0);
                nameTarget.textContent = form.dataset.name || 'this document type';

                requestsTarget.classList.toggle('hidden', requestCount === 0);
                requestsTarget.textContent = requestCount > 0
                    ? `This will also delete ${requestCount} existing request${requestCount === 1 ? '' : 's'} connected to this document type.`
                    : '';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                confirmButton.focus();
            });
        });

        cancelButton.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
                closeModal();
            }
        });
        confirmButton.addEventListener('click', () => {
            if (pendingForm) {
                pendingForm.submit();
            }
        });
    });
</script>
@endpush
