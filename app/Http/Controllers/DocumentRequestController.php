<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    public function create(): View
    {
        $documentTypes = DocumentType::active()->orderBy('name')->get(['id', 'name', 'description']);

        return view('document-request', compact('documentTypes'));
    }

    public function getFields(DocumentType $documentType): JsonResponse
    {
        if (! $documentType->is_active) {
            abort(404);
        }

        $fields = $documentType->fields()
            ->orderBy('display_order')
            ->get(['field_name', 'field_label', 'field_type', 'field_options', 'is_required', 'validation_rules']);

        return response()->json($fields);
    }

    public function index(): JsonResponse
    {
        return response()->json(DocumentRequest::with(['fields', 'user', 'reviewer', 'documentType'])->get());
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            $documentType = DocumentType::findOrFail($request->document_type_id);

            // Build validation rules from document type fields
            $rules = [
                'document_type_id' => ['required', 'exists:document_types,id'],
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
            ];

            foreach ($documentType->fields as $field) {
                $fieldRules = [];
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                }
                $fieldRules[] = 'string';
                if ($field->validation_rules) {
                    $fieldRules[] = $field->validation_rules;
                }
                $rules[$field->field_name] = $fieldRules;
            }

            $validated = $request->validate($rules);

            $documentRequest = $this->createFromForm($validated, $documentType);

            return redirect()->route('track-request', [
                'code' => 'USSC-'.now()->year.'-'.str_pad((string) $documentRequest->request_id, 4, '0', STR_PAD_LEFT),
            ])->with('success', 'Your document request was submitted successfully.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'status' => ['sometimes', 'string', 'max:20'],
            'fields' => ['sometimes', 'array'],
            'fields.*.field_name' => ['required_with:fields', 'string', 'max:100'],
            'fields.*.field_value' => ['required_with:fields', 'string', 'max:500'],
        ]);

        $documentRequest = DB::transaction(function () use ($validated): DocumentRequest {
            $fields = $validated['fields'] ?? [];
            unset($validated['fields']);

            $documentRequest = DocumentRequest::create([
                ...$validated,
                'status' => $validated['status'] ?? 'pending',
            ]);

            $documentRequest->fields()->createMany($fields);

            return $documentRequest->load('fields');
        });

        return response()->json($documentRequest, 201);
    }

    private function createFromForm(array $validated, DocumentType $documentType): DocumentRequest
    {
        return DB::transaction(function () use ($validated, $documentType): DocumentRequest {
            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['full_name'],
                ],
            );

            $documentRequest = DocumentRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $documentType->id,
                'status' => 'pending',
            ]);

            // Store all dynamic fields
            $fieldsToCreate = [];
            foreach ($documentType->fields as $field) {
                if (isset($validated[$field->field_name])) {
                    $fieldsToCreate[] = [
                        'field_name' => $field->field_name,
                        'field_value' => $validated[$field->field_name],
                    ];
                }
            }

            if (! empty($fieldsToCreate)) {
                $documentRequest->fields()->createMany($fieldsToCreate);
            }

            return $documentRequest;
        });
    }

    public function show(DocumentRequest $documentRequest): JsonResponse
    {
        return response()->json($documentRequest->load(['fields', 'user', 'reviewer', 'documentType']));
    }

    public function update(Request $request, DocumentRequest $documentRequest): JsonResponse
    {
        $validated = $request->validate([
            'document_type_id' => ['sometimes', 'integer', 'exists:document_types,id'],
            'status' => ['sometimes', 'string', 'max:20'],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:admins,admin_id'],
        ]);

        $documentRequest->update($validated);

        return response()->json($documentRequest->fresh(['fields', 'user', 'reviewer', 'documentType']));
    }

    public function destroy(DocumentRequest $documentRequest): JsonResponse
    {
        $documentRequest->delete();

        return response()->json(null, 204);
    }
}
