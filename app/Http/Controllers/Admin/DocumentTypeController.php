<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\DocumentTypeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DocumentTypeController extends Controller
{
    public function index(): View
    {
        $documentTypes = DocumentType::withCount('fields')->get();
        return view('admin.document-types.index', compact('documentTypes'));
    }

    public function create(): View
    {
        return view('admin.document-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        DocumentType::create($validated);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    public function edit(DocumentType $documentType): View
    {
        $documentType->load('fields');
        return view('admin.document-types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $documentType->update($validated);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        if ($documentType->documentRequests()->count() > 0) {
            return back()->with('error', 'Cannot delete document type with existing requests.');
        }

        $documentType->delete();

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }

    public function addField(Request $request, DocumentType $documentType): RedirectResponse
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:100', 'regex:/^[a-z_]+$/'],
            'field_label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'in:text,email,textarea,number,date,select'],
            'field_options' => ['nullable', 'json'],
            'is_required' => ['sometimes', 'boolean'],
            'validation_rules' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');

        // Parse field options if JSON string
        if (is_string($validated['field_options'])) {
            $validated['field_options'] = json_decode($validated['field_options'], true);
        }

        // Get the next display order
        $maxOrder = $documentType->fields()->max('display_order') ?? 0;
        $validated['display_order'] = $maxOrder + 1;

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            ...$validated,
        ]);

        return back()->with('success', 'Field added successfully.');
    }

    public function updateField(Request $request, DocumentTypeField $field): RedirectResponse
    {
        $validated = $request->validate([
            'field_label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'in:text,email,textarea,number,date,select'],
            'field_options' => ['nullable', 'json'],
            'is_required' => ['sometimes', 'boolean'],
            'validation_rules' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');

        // Parse field options if JSON string
        if (isset($validated['field_options']) && is_string($validated['field_options'])) {
            $validated['field_options'] = json_decode($validated['field_options'], true);
        }

        $field->update($validated);

        return back()->with('success', 'Field updated successfully.');
    }

    public function deleteField(DocumentTypeField $field): RedirectResponse
    {
        $field->delete();

        return back()->with('success', 'Field deleted successfully.');
    }

    public function reorderFields(Request $request, DocumentType $documentType): RedirectResponse
    {
        $validated = $request->validate([
            'field_ids' => ['required', 'array'],
            'field_ids.*' => ['exists:document_type_fields,id'],
        ]);

        foreach ($validated['field_ids'] as $index => $fieldId) {
            DocumentTypeField::where('id', $fieldId)
                ->where('document_type_id', $documentType->id)
                ->update(['display_order' => $index + 1]);
        }

        return back()->with('success', 'Fields reordered successfully.');
    }
}
