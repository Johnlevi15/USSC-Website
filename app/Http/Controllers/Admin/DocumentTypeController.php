<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\DocumentTypeField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function index(): View
    {
        $documentTypes = DocumentType::withCount(['fields', 'documentRequests'])->get();

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
        $requestCount = $documentType->documentRequests()->count();

        if ($requestCount > 0 && ! request()->boolean('delete_requests')) {
            return back()->with('error', 'Cannot delete document type with existing requests.');
        }

        DB::transaction(function () use ($documentType): void {
            $documentType->documentRequests()
                ->select('request_id')
                ->each(function ($documentRequest): void {
                    Storage::disk(config('filesystems.uploads.documents', 'local'))
                        ->deleteDirectory("document-uploads/{$documentRequest->request_id}");
                    $documentRequest->delete();
                });

            $documentType->delete();
        });

        return redirect()->route('admin.document-types.index')
            ->with('success', $requestCount > 0
                ? "Document type and {$requestCount} existing request(s) deleted successfully."
                : 'Document type deleted successfully.');
    }

    public function addField(Request $request, DocumentType $documentType): RedirectResponse
    {
        $validated = $request->validate([
            'field_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-z_]+$/'],
            'field_label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'in:text,email,textarea,number,date,select,checkbox,file,image'],
            'field_options' => ['nullable', 'string', 'max:2000'],
            'is_required' => ['sometimes', 'boolean'],
            'validation_rules' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['field_name'] = $this->fieldName(
            $validated['field_name'] ?? null,
            $validated['field_label'],
            $documentType,
        );
        $validated['is_required'] = $request->boolean('is_required');

        $validated['field_options'] = $this->fieldOptions($validated);

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
            'field_type' => ['required', 'in:text,email,textarea,number,date,select,checkbox,file,image'],
            'field_options' => ['nullable', 'string', 'max:2000'],
            'is_required' => ['sometimes', 'boolean'],
            'validation_rules' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');

        $validated['field_options'] = $this->fieldOptions($validated);

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

    private function fieldName(?string $fieldName, string $fieldLabel, DocumentType $documentType): string
    {
        if (is_string($fieldName) && trim($fieldName) !== '') {
            return trim($fieldName);
        }

        $baseName = trim((string) Str::of($fieldLabel)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z]+/', '_'), '_');

        $baseName = $baseName !== '' ? Str::limit($baseName, 100, '') : 'field';
        $generatedName = $baseName;

        while ($documentType->fields()->where('field_name', $generatedName)->exists()) {
            $generatedName = Str::limit($baseName, 95, '').'_copy';
            $baseName = $generatedName;
        }

        return $generatedName;
    }

    /**
     * @param  array{field_type: string, field_options?: string|null}  $validated
     * @return list<string>|null
     */
    private function fieldOptions(array $validated): ?array
    {
        $options = $this->optionLines($validated['field_options'] ?? null);

        if ($validated['field_type'] === 'checkbox' && ! is_array($options)) {
            throw ValidationException::withMessages([
                'field_options' => 'Checkbox fields need at least one option.',
            ]);
        }

        if (! is_array($options)) {
            return null;
        }

        $options = array_values(array_filter($options, fn ($option): bool => is_string($option) && trim($option) !== ''));

        if ($validated['field_type'] === 'checkbox' && $options === []) {
            throw ValidationException::withMessages([
                'field_options' => 'Checkbox fields need at least one option.',
            ]);
        }

        return $options;
    }

    /**
     * @return list<string>|null
     */
    private function optionLines(?string $fieldOptions): ?array
    {
        if (! is_string($fieldOptions) || trim($fieldOptions) === '') {
            return null;
        }

        $jsonOptions = json_decode($fieldOptions, true);

        if (is_array($jsonOptions)) {
            return array_values(array_filter(array_map(
                fn (mixed $option): string => is_string($option) ? trim($option) : '',
                $jsonOptions,
            ), fn (string $option): bool => $option !== ''));
        }

        $options = preg_split('/\R/', $fieldOptions) ?: [];

        return array_values(array_filter(array_map(
            fn (string $option): string => trim($option),
            $options,
        ), fn (string $option): bool => $option !== ''));
    }
}
