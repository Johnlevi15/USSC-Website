@extends('layouts.admin')
@section('title', 'Document Types | USSC Admin')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Document Types</h1>
        <a href="{{ route('admin.document-types.create') }}" class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
            <i class="fa-solid fa-plus mr-1"></i> Create New
        </a>
    </div>

    @if($documentTypes->isEmpty())
        <div class="rounded-lg border bg-white p-8 text-center text-gray-500">
            No document types found. Create your first one to get started.
        </div>
    @else
        <div class="rounded-lg border bg-white overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Fields</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($documentTypes as $type)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $type->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $type->fields_count }}</td>
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
                            <form method="POST" action="{{ route('admin.document-types.destroy', $type) }}" class="inline" onsubmit="return confirm('Delete this document type?')">
                                @csrf @method('DELETE')
                                <button class="ml-3 text-red-600 hover:underline font-semibold text-xs">
                                    <i class="fa-solid fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
