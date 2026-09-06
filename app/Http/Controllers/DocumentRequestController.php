<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
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
        return view('document-request');
    }

    public function index(): JsonResponse
    {
        return response()->json(DocumentRequest::with(['fields', 'user', 'reviewer'])->get());
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            $validated = $request->validate([
                'document_type' => ['required', 'string', 'max:100'],
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'student_id' => ['required', 'string', 'max:255'],
                'department' => ['required', 'string', 'max:255'],
                'year_section' => ['required', 'string', 'max:255'],
                'purpose' => ['required', 'string', 'max:500'],
            ]);

            $documentRequest = $this->createFromForm($validated);

            return redirect()->route('track-request', [
                'code' => 'USSC-'.now()->year.'-'.str_pad((string) $documentRequest->request_id, 4, '0', STR_PAD_LEFT),
            ])->with('success', 'Your document request was submitted successfully.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'document_type' => ['required', 'string', 'max:100'],
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

    private function createFromForm(array $validated): DocumentRequest
    {
        return DB::transaction(function () use ($validated): DocumentRequest {
            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['full_name'],
                ],
            );

            $documentRequest = DocumentRequest::create([
                'user_id' => $user->id,
                'document_type' => $validated['document_type'],
                'status' => 'pending',
            ]);

            $documentRequest->fields()->createMany([
                ['field_name' => 'student_id', 'field_value' => $validated['student_id']],
                ['field_name' => 'department', 'field_value' => $validated['department']],
                ['field_name' => 'year_section', 'field_value' => $validated['year_section']],
                ['field_name' => 'purpose', 'field_value' => $validated['purpose']],
            ]);

            return $documentRequest;
        });
    }

    public function show(DocumentRequest $documentRequest): JsonResponse
    {
        return response()->json($documentRequest->load(['fields', 'user', 'reviewer']));
    }

    public function update(Request $request, DocumentRequest $documentRequest): JsonResponse
    {
        $validated = $request->validate([
            'document_type' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'max:20'],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:admins,admin_id'],
        ]);

        $documentRequest->update($validated);

        return response()->json($documentRequest->fresh(['fields', 'user', 'reviewer']));
    }

    public function destroy(DocumentRequest $documentRequest): JsonResponse
    {
        $documentRequest->delete();

        return response()->json(null, 204);
    }
}
