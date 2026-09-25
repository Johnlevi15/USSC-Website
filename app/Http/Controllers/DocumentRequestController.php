<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $baseFields = collect([
            [
                'field_name' => 'full_name',
                'field_label' => 'Full Name',
                'field_type' => 'text',
                'field_options' => null,
                'is_required' => true,
                'validation_rules' => null,
            ],
            [
                'field_name' => 'email',
                'field_label' => 'Email Address',
                'field_type' => 'email',
                'field_options' => null,
                'is_required' => true,
                'validation_rules' => null,
            ],
        ]);

        $fields = $documentType->fields()
            ->orderBy('display_order')
            ->get(['field_name', 'field_label', 'field_type', 'field_options', 'is_required', 'validation_rules']);

        return response()->json($baseFields->concat($fields)->values());
    }

    public function track(Request $request): View
    {
        $code = strtoupper(trim((string) $request->query('code', '')));
        $trackedRequest = null;
        $trackingError = null;

        if ($code !== '') {
            if (! preg_match('/^USSC-(\d{4})-(\d+)$/', $code, $matches)) {
                $trackingError = 'Please enter a valid tracking number, for example USSC-2026-0001.';
            } else {
                $year = (int) $matches[1];
                $requestId = (int) $matches[2];

                $trackedRequest = DocumentRequest::query()
                    ->with('documentType:id,name')
                    ->where('request_id', $requestId)
                    ->first();

                if ($trackedRequest === null || (int) $trackedRequest->submitted_at?->year !== $year) {
                    $trackedRequest = null;
                    $trackingError = 'No document request was found for that tracking number.';
                }
            }
        }

        return view('track-request', compact('code', 'trackedRequest', 'trackingError'));
    }

    public function index(): JsonResponse
    {
        return response()->json(DocumentRequest::with(['fields', 'user', 'reviewer', 'documentType'])->get());
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            $request->validate([
                'document_type_id' => ['required', 'exists:document_types,id'],
            ]);

            $documentType = DocumentType::findOrFail($request->document_type_id);

            // Build validation rules from document type fields
            $rules = [
                'document_type_id' => ['required', 'exists:document_types,id'],
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
            ];

            foreach ($documentType->fields as $field) {
                if ($this->hasOtherOption($field->field_options)) {
                    $rules[$field->field_name.'_other'] = ['nullable', 'string', 'max:150'];
                }

                if ($field->field_type === 'file') {
                    $rules[$field->field_name] = [
                        $field->is_required ? 'required' : 'nullable',
                        'file',
                        'mimes:pdf,doc,docx,jpg,jpeg,png',
                        'extensions:pdf,doc,docx,jpg,jpeg,png',
                        'max:5120',
                    ];

                    continue;
                }

                if ($field->field_type === 'image') {
                    $rules[$field->field_name] = [
                        $field->is_required ? 'required' : 'nullable',
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'extensions:jpg,jpeg,png,webp',
                        'max:5120',
                    ];

                    continue;
                }

                if ($field->field_type === 'checkbox') {
                    $options = $field->field_options ?? [];
                    $rules[$field->field_name] = $field->is_required
                        ? ['required', 'array', 'min:1']
                        : ['sometimes', 'array'];
                    $rules[$field->field_name.'.*'] = ['string', 'max:150', Rule::in($options)];

                    continue;
                }

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
            $this->validateOtherOptions($validated, $documentType);

            $documentRequest = $this->createFromForm($validated, $documentType, $request);

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

    private function createFromForm(array $validated, DocumentType $documentType, Request $request): DocumentRequest
    {
        return DB::transaction(function () use ($validated, $documentType, $request): DocumentRequest {
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
                if (in_array($field->field_type, ['file', 'image'], true)) {
                    if ($request->hasFile($field->field_name)) {
                        $file = $request->file($field->field_name);
                        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
                        $path = Storage::disk('local')->putFileAs(
                            "document-uploads/{$documentRequest->request_id}",
                            $file,
                            $fileName,
                        );

                        $fieldsToCreate[] = [
                            'field_name' => $field->field_name,
                            'field_value' => $path,
                        ];
                    }

                    continue;
                }

                if ($field->field_type === 'checkbox') {
                    $fieldsToCreate[] = [
                        'field_name' => $field->field_name,
                        'field_value' => json_encode($this->checkboxValues($validated, $field->field_name)),
                    ];

                    continue;
                }

                if (isset($validated[$field->field_name])) {
                    $fieldsToCreate[] = [
                        'field_name' => $field->field_name,
                        'field_value' => $this->scalarValue($validated, $field->field_name),
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

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateOtherOptions(array $validated, DocumentType $documentType): void
    {
        $errors = [];

        foreach ($documentType->fields as $field) {
            if (! $this->hasOtherOption($field->field_options)) {
                continue;
            }

            $otherKey = $field->field_name.'_other';
            $otherValue = trim((string) ($validated[$otherKey] ?? ''));

            if ($field->field_type === 'checkbox' && in_array('Other', $validated[$field->field_name] ?? [], true) && $otherValue === '') {
                $errors[$otherKey] = 'Please specify the other option.';
            }

            if ($field->field_type === 'select' && ($validated[$field->field_name] ?? null) === 'Other' && $otherValue === '') {
                $errors[$otherKey] = 'Please specify the other option.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<string>
     */
    private function checkboxValues(array $validated, string $fieldName): array
    {
        $values = array_values($validated[$fieldName] ?? []);
        $otherKey = $fieldName.'_other';
        $otherValue = trim((string) ($validated[$otherKey] ?? ''));

        return array_map(
            fn (string $value): string => $value === 'Other' && $otherValue !== '' ? 'Other: '.$otherValue : $value,
            $values,
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function scalarValue(array $validated, string $fieldName): string
    {
        $value = (string) $validated[$fieldName];
        $otherValue = trim((string) ($validated[$fieldName.'_other'] ?? ''));

        if ($value === 'Other' && $otherValue !== '') {
            return 'Other: '.$otherValue;
        }

        return $value;
    }

    /**
     * @param  array<int, string>|null  $options
     */
    private function hasOtherOption(?array $options): bool
    {
        return in_array('Other', $options ?? [], true);
    }
}
