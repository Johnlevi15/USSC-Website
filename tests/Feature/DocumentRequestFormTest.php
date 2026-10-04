<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\DocumentTypeField;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class DocumentRequestFormTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_document_request_form_shows_the_document_fee_request_option(): void
    {
        DocumentType::create([
            'name' => 'Document Fee Request Form',
            'description' => 'Standard form',
            'is_active' => true,
        ]);

        $this->get('/document-request')
            ->assertSee('<select required name="document_type_id"', false)
            ->assertSee('Document Fee Request Form');
    }

    public function test_document_type_fields_endpoint_includes_required_student_fields(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Document Fee Request Form',
            'description' => 'Standard form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'request_purpose',
            'field_label' => 'Purpose of Request',
            'field_type' => 'text',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->get(route('document-types.fields', $documentType))
            ->assertOk()
            ->assertJsonPath('0.field_name', 'full_name')
            ->assertJsonPath('0.field_label', 'Full Name')
            ->assertJsonPath('0.is_required', true)
            ->assertJsonPath('1.field_name', 'email')
            ->assertJsonPath('1.field_label', 'Email Address')
            ->assertJsonPath('1.is_required', true)
            ->assertJsonPath('2.field_name', 'request_purpose');
    }

    public function test_document_type_fields_endpoint_does_not_duplicate_base_student_fields(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Document Fee Request Form',
            'description' => 'Standard form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'full_name',
            'field_label' => 'Full Name',
            'field_type' => 'text',
            'is_required' => true,
            'display_order' => 1,
        ]);
        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'request_purpose',
            'field_label' => 'Purpose of Request',
            'field_type' => 'text',
            'is_required' => true,
            'display_order' => 2,
        ]);

        $response = $this->get(route('document-types.fields', $documentType))
            ->assertOk();

        $this->assertSame(['full_name', 'email', 'request_purpose'], collect($response->json())->pluck('field_name')->all());
    }

    public function test_document_request_validation_errors_keep_old_input_visible_for_repair(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Document Fee Request Form',
            'description' => 'Standard form',
            'is_active' => true,
        ]);

        $this->withSession([
            '_old_input' => [
                'document_type_id' => $documentType->id,
                'full_name' => 'Taylor Student',
                'email' => 'not-an-email',
            ],
            '_flash' => [
                'errors' => new MessageBag([
                    'email' => ['The email field must be a valid email address.'],
                ]),
            ],
        ])
            ->get('/document-request')
            ->assertOk()
            ->assertSee('validationErrors')
            ->assertSee('Taylor Student')
            ->assertSee('not-an-email')
            ->assertSee('The email field must be a valid email address.');
    }

    public function test_document_request_form_does_not_show_the_data_privacy_popup(): void
    {
        $this->get('/document-request')
            ->assertOk()
            ->assertDontSee('Data Privacy Notice')
            ->assertDontSee('privacy-modal');
    }

    public function test_document_request_form_rejects_an_unavailable_document_type(): void
    {
        $this->from('/document-request')->post('/document-request', [
            'document_type_id' => 999,
            'full_name' => 'Taylor Student',
            'student_id' => '20260001',
            'department' => 'College of Arts and Sciences',
            'year_section' => 'Fourth Year A',
            'email' => 'taylor@example.test',
            'purpose' => 'Scholarship requirements',
        ])
            ->assertRedirect('/document-request')
            ->assertSessionHasErrors('document_type_id');
    }

    public function test_document_request_submission_shows_tracking_number_save_instruction(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Document Fee Request Form',
            'description' => 'Standard form',
            'is_active' => true,
        ]);

        $this->followingRedirects()
            ->post('/document-request', [
                'document_type_id' => $documentType->id,
                'full_name' => 'Taylor Student',
                'email' => 'taylor@example.test',
            ])
            ->assertOk()
            ->assertSeeText('Request Submitted Successfully')
            ->assertSeeText('Tracking Number')
            ->assertSeeText('Please copy it, save a copy, or take a screenshot.')
            ->assertSee('USSC-'.now()->year.'-0001');
    }

    public function test_document_request_form_rejects_negative_number_fields(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'family_members',
            'field_label' => 'Number of Family Members',
            'field_type' => 'number',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->from('/document-request')
            ->post('/document-request', [
                'document_type_id' => $documentType->id,
                'full_name' => 'Taylor Student',
                'email' => 'taylor@example.test',
                'family_members' => '-1',
            ])
            ->assertRedirect('/document-request')
            ->assertSessionHasErrors([
                'family_members' => 'The family members field must be at least 0.',
            ]);

        $this->assertDatabaseCount('document_requests', 0);
    }

    public function test_document_request_form_accepts_zero_for_number_fields(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'family_members',
            'field_label' => 'Number of Family Members',
            'field_type' => 'number',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'family_members' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'family_members',
            'field_value' => '0',
        ]);
    }

    public function test_admin_can_add_grouped_select_options(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Enrollment Request',
            'description' => 'Enrollment form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.document-types.edit', $documentType))
            ->assertSee('Group options')
            ->assertSee('+ Add a group')
            ->assertSee('id:##-####')
            ->assertSee('Validation Rules (optional)')
            ->assertSee('Show validation rule examples')
            ->assertSee('max:255')
            ->assertSee('size:10')
            ->assertDontSee('require a valid email address')
            ->assertDontSee('regex:/^[0-9]{10}$/');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_name' => 'college_program',
                'field_label' => 'College / Program',
                'field_type' => 'select',
                'field_options' => 'College of Engineering > Bachelor of Science in Information Technology',
                'is_required' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_name' => 'college_program',
            'field_options' => json_encode([
                'College of Engineering > Bachelor of Science in Information Technology',
            ]),
        ]);
    }

    public function test_admin_can_create_a_checkbox_field_for_a_document_type(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_name' => 'confirm_clearance',
                'field_label' => 'I confirm this request is accurate',
                'field_type' => 'checkbox',
                'field_options' => 'I confirm this request is accurate',
                'is_required' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_name' => 'confirm_clearance',
            'field_type' => 'checkbox',
            'field_options' => json_encode(['I confirm this request is accurate']),
            'is_required' => true,
        ]);
    }

    public function test_admin_document_type_edit_form_hides_internal_field_names(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'student_id_number',
            'field_label' => 'Student ID Number',
            'field_type' => 'text',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.document-types.edit', $documentType))
            ->assertOk()
            ->assertDontSee('name="field_name"', false)
            ->assertDontSeeText('Field Name (internal)')
            ->assertSeeText('Student ID Number')
            ->assertSeeText('Delete')
            ->assertSee('inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-red-700', false)
            ->assertSeeText('Enter one option per line. Use "Other" to let users type a custom answer.');
    }

    public function test_admin_can_create_a_document_type_field_without_typing_an_internal_name(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_label' => 'Student ID Number',
                'field_type' => 'text',
                'is_required' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_name' => 'student_id_number',
            'field_label' => 'Student ID Number',
            'field_type' => 'text',
            'is_required' => true,
        ]);
    }

    public function test_admin_id_format_is_applied_to_document_request_values(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_label' => 'Student ID Number',
                'field_type' => 'text',
                'validation_rules' => 'id:##-####',
                'is_required' => '1',
            ])
            ->assertRedirect();

        $field = $documentType->fields()->where('field_name', 'student_id_number')->firstOrFail();
        $this->assertSame('id_format:##-####', $field->validation_rules);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.document-types.edit', $documentType))
            ->assertSee('value="id:##-####"', false);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'student_id_number' => '24-0832',
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'student_id_number',
            'field_value' => '24-0832',
        ]);

        $this->from('/document-request')
            ->post('/document-request', [
                'document_type_id' => $documentType->id,
                'full_name' => 'Taylor Student',
                'email' => 'taylor@example.test',
                'student_id_number' => '4-0832',
            ])
            ->assertRedirect('/document-request')
            ->assertSessionHasErrors('student_id_number');

        $this->assertDatabaseCount('document', 1);
    }

    public function test_admin_cannot_save_an_id_format_with_unsupported_characters(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.document-types.edit', $documentType))
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_label' => 'Student ID Number',
                'field_type' => 'text',
                'validation_rules' => 'id:##-####!',
            ])
            ->assertRedirect(route('admin.document-types.edit', $documentType))
            ->assertSessionHasErrors('validation_rules');

        $this->assertDatabaseMissing('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_name' => 'student_id_number',
        ]);
    }

    public function test_admin_cannot_create_a_document_type_field_that_duplicates_base_student_fields(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.document-types.edit', $documentType))
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_label' => 'Full Name',
                'field_type' => 'text',
                'is_required' => '1',
            ])
            ->assertRedirect(route('admin.document-types.edit', $documentType))
            ->assertSessionHasErrors('field_label');

        $this->assertDatabaseMissing('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_label' => 'Full Name',
        ]);
    }

    public function test_generated_document_type_field_names_are_unique_within_the_document_type(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'student_id_number',
            'field_label' => 'Student ID Number',
            'field_type' => 'text',
            'is_required' => false,
            'display_order' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_label' => 'Student ID Number',
                'field_type' => 'text',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_name' => 'student_id_number_copy',
            'field_label' => 'Student ID Number',
            'field_type' => 'text',
        ]);
    }

    public function test_admin_can_update_an_existing_document_type_field(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        $field = DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'request_purpose',
            'field_label' => 'Purpose',
            'field_type' => 'text',
            'is_required' => false,
            'display_order' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.document-types.fields.update', $field), [
                'field_label' => 'Purpose of Request',
                'field_type' => 'select',
                'field_options' => "Scholarship Application\nOther",
                'is_required' => '1',
                'validation_rules' => 'max:255',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_type_fields', [
            'id' => $field->id,
            'field_label' => 'Purpose of Request',
            'field_type' => 'select',
            'field_options' => json_encode(['Scholarship Application', 'Other']),
            'is_required' => true,
            'validation_rules' => 'max:255',
        ]);
    }

    public function test_admin_document_requests_are_separated_into_pending_and_processed_sections(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        $pendingRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $processedRequests = collect(['review', 'approved', 'ready', 'rejected'])
            ->map(fn (string $status): DocumentRequest => DocumentRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $documentType->id,
                'status' => $status,
            ]));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.documents'))
            ->assertSeeText('Pending Requests')
            ->assertSeeText('Processed Requests')
            ->assertSee("Request #{$pendingRequest->request_id}")
            ->assertSee("Request #{$processedRequests->first()->request_id}");

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.documents.live'))
            ->assertOk()
            ->assertJsonPath('pending_count', 1)
            ->assertJsonPath('processed_count', 4);

        $pendingHtml = $response->json('pending_html');
        $processedHtml = $response->json('processed_html');
        $this->assertStringContainsString("Request #{$pendingRequest->request_id}", $pendingHtml);
        $this->assertStringNotContainsString("Request #{$processedRequests->first()->request_id}", $pendingHtml);
        $this->assertStringContainsString("Request #{$processedRequests->first()->request_id}", $processedHtml);
        $this->assertStringNotContainsString("Request #{$pendingRequest->request_id}", $processedHtml);

        foreach ($processedRequests as $processedRequest) {
            $this->assertStringContainsString("Request #{$processedRequest->request_id}", $processedHtml);
        }
    }

    public function test_admin_document_review_shows_success_modal_after_saving_a_status(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->followingRedirects()
            ->patch(route('admin.documents.update', $documentRequest), [
                'status' => 'approved',
            ])
            ->assertOk()
            ->assertSee('id="success-modal"', false)
            ->assertSee('role="dialog"', false)
            ->assertSeeText('Changes Saved')
            ->assertSeeText('Document review saved.')
            ->assertSee('onclick="closeSuccessModal()"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'Document review saved.'));

        $this->assertDatabaseHas('document_requests', [
            'request_id' => $documentRequest->request_id,
            'status' => 'approved',
            'reviewed_by' => $admin->admin_id,
        ]);
    }

    public function test_admin_can_review_document_request_details_with_saved_feedback(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
            'admin_remarks' => 'Please complete the requirement letter before processing.',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.documents.review', $documentRequest))
            ->assertOk()
            ->assertSeeText('Review Document Request')
            ->assertSeeText('Admin Remarks')
            ->assertSeeText('Please complete the requirement letter before processing.')
            ->assertSeeText('Request Information')
            ->assertSeeText('Student Information');
    }

    public function test_admin_can_delete_an_unused_document_type(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Unused Request',
            'description' => 'No requests use this type',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.document-types.destroy', $documentType))
            ->assertRedirect(route('admin.document-types.index'))
            ->assertSessionHas('success', 'Document type deleted successfully.');

        $this->assertDatabaseMissing('document_types', [
            'id' => $documentType->id,
        ]);
    }

    public function test_admin_cannot_delete_a_document_type_with_existing_requests(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Used Request',
            'description' => 'A request uses this type',
            'is_active' => true,
        ]);

        DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.document-types.index'))
            ->delete(route('admin.document-types.destroy', $documentType))
            ->assertRedirect(route('admin.document-types.index'))
            ->assertSessionHas('error', 'Cannot delete document type with existing requests.');

        $this->assertDatabaseHas('document_types', [
            'id' => $documentType->id,
        ]);
    }

    public function test_admin_can_delete_a_document_type_with_existing_requests_when_confirmed(): void
    {
        Storage::fake('local');

        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Used Request',
            'description' => 'A request uses this type',
            'is_active' => true,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $storedPath = "document-uploads/{$documentRequest->request_id}/registration.pdf";
        Storage::disk('local')->put($storedPath, 'registration file');

        Document::create([
            'request_id' => $documentRequest->request_id,
            'field_name' => 'registration_form',
            'field_value' => $storedPath,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.document-types.destroy', $documentType), [
                'delete_requests' => '1',
            ])
            ->assertRedirect(route('admin.document-types.index'))
            ->assertSessionHas('success', 'Document type and 1 existing request(s) deleted successfully.');

        $this->assertDatabaseMissing('document_types', [
            'id' => $documentType->id,
        ]);
        $this->assertDatabaseMissing('document_requests', [
            'request_id' => $documentRequest->request_id,
        ]);
        $this->assertDatabaseMissing('document', [
            'request_id' => $documentRequest->request_id,
            'field_name' => 'registration_form',
        ]);
        Storage::disk('local')->assertMissing($storedPath);
    }

    public function test_admin_can_create_a_file_upload_field_for_a_document_type(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.document-types.fields.add', $documentType), [
                'field_name' => 'registration_form',
                'field_label' => 'Registration Form',
                'field_type' => 'file',
                'is_required' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_type_fields', [
            'document_type_id' => $documentType->id,
            'field_name' => 'registration_form',
            'field_type' => 'file',
            'is_required' => true,
        ]);
    }

    public function test_document_request_form_stores_grouped_checkbox_field_values(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'requested_fees',
            'field_label' => 'Requested Fees',
            'field_type' => 'checkbox',
            'field_options' => ['ID Fee', 'Certification Fee', 'Transcript Fee'],
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'requested_fees' => ['ID Fee', 'Certification Fee'],
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'requested_fees',
            'field_value' => '["ID Fee","Certification Fee"]',
        ]);
    }

    public function test_document_request_form_stores_custom_other_select_values(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'request_purpose',
            'field_label' => 'Purpose of Request',
            'field_type' => 'select',
            'field_options' => ['Scholarship Application', 'Other'],
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'request_purpose' => 'Other',
            'request_purpose_other' => 'Student Council Election Requirement',
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'request_purpose',
            'field_value' => 'Other: Student Council Election Requirement',
        ]);
    }

    public function test_document_request_form_accepts_a_program_from_a_grouped_select(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Enrollment Request',
            'description' => 'Enrollment form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'college_program',
            'field_label' => 'College / Program',
            'field_type' => 'select',
            'field_options' => ['College of Engineering > Bachelor of Science in Information Technology'],
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->getJson(route('document-types.fields', $documentType))
            ->assertJsonPath('2.field_options.0', 'College of Engineering > Bachelor of Science in Information Technology');

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'college_program' => 'Bachelor of Science in Information Technology',
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'college_program',
            'field_value' => 'Bachelor of Science in Information Technology',
        ]);
    }

    public function test_document_request_form_rejects_a_college_name_as_a_select_value(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Enrollment Request',
            'description' => 'Enrollment form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'college_program',
            'field_label' => 'College / Program',
            'field_type' => 'select',
            'field_options' => ['College of Engineering > Bachelor of Science in Information Technology'],
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->from('/document-request')
            ->post('/document-request', [
                'document_type_id' => $documentType->id,
                'full_name' => 'Taylor Student',
                'email' => 'taylor@example.test',
                'college_program' => 'College of Engineering',
            ])
            ->assertRedirect('/document-request')
            ->assertSessionHasErrors('college_program');

        $this->assertDatabaseMissing('document', [
            'field_name' => 'college_program',
        ]);
    }

    public function test_document_request_form_stores_custom_other_checkbox_values(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'requested_fees',
            'field_label' => 'Requested Fees',
            'field_type' => 'checkbox',
            'field_options' => ['ID Fee', 'Other'],
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'requested_fees' => ['ID Fee', 'Other'],
            'requested_fees_other' => 'Student Council Election Requirement',
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'requested_fees',
            'field_value' => '["ID Fee","Other: Student Council Election Requirement"]',
        ]);
    }

    public function test_document_request_form_does_not_store_base_student_fields_as_custom_documents(): void
    {
        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'full_name',
            'field_label' => 'Full Name',
            'field_type' => 'text',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);
        $this->assertDatabaseMissing('document', [
            'field_name' => 'full_name',
        ]);
    }

    public function test_document_request_form_stores_uploaded_file_fields(): void
    {
        Storage::fake('local');

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'registration_form',
            'field_label' => 'Registration Form',
            'field_type' => 'file',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'registration_form' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('document', [
            'field_name' => 'registration_form',
        ]);

        $storedPath = (string) Document::query()
            ->where('field_name', 'registration_form')
            ->value('field_value');
        Storage::disk('local')->assertExists($storedPath);
    }

    public function test_document_request_file_uploads_use_the_configured_document_upload_disk(): void
    {
        config(['filesystems.uploads.documents' => 'public']);
        Storage::fake('local');
        Storage::fake('public');

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'student_photo',
            'field_label' => 'Student Photo',
            'field_type' => 'image',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'student_photo' => UploadedFile::fake()->image('student-photo.jpg'),
        ])->assertRedirect();

        $storedPath = (string) Document::query()
            ->where('field_name', 'student_photo')
            ->value('field_value');

        Storage::disk('public')->assertExists($storedPath);
        Storage::disk('local')->assertMissing($storedPath);
    }

    public function test_document_request_form_rejects_uploaded_file_with_disallowed_extension(): void
    {
        Storage::fake('local');

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'registration_form',
            'field_label' => 'Registration Form',
            'field_type' => 'file',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $this->from('/document-request')->post('/document-request', [
            'document_type_id' => $documentType->id,
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'registration_form' => UploadedFile::fake()->create('registration.php', 100, 'application/pdf'),
        ])
            ->assertRedirect('/document-request')
            ->assertSessionHasErrors('registration_form');

        $this->assertDatabaseMissing('document', [
            'field_name' => 'registration_form',
        ]);
        Storage::disk('local')->assertDirectoryEmpty('/');
    }

    public function test_admin_document_review_displays_checkbox_values_as_readable_options(): void
    {
        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Clearance Request',
            'description' => 'Clearance form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'requested_fees',
            'field_label' => 'Requested Fees',
            'field_type' => 'checkbox',
            'field_options' => ['ID Fee', 'Certification Fee', 'Transcript Fee'],
            'is_required' => true,
            'display_order' => 1,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        Document::create([
            'request_id' => $documentRequest->request_id,
            'field_name' => 'requested_fees',
            'field_value' => '["ID Fee","Certification Fee"]',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.documents.review', $documentRequest))
            ->assertOk()
            ->assertSeeText('Requested Fees')
            ->assertSeeText('ID Fee')
            ->assertSeeText('Certification Fee')
            ->assertDontSee('["ID Fee","Certification Fee"]');
    }

    public function test_admin_can_view_document_request_file_attachment(): void
    {
        Storage::fake('local');

        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'registration_form',
            'field_label' => 'Registration Form',
            'field_type' => 'file',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $storedPath = "document-uploads/{$documentRequest->request_id}/registration.pdf";
        Storage::disk('local')->put($storedPath, 'registration file');

        Document::create([
            'request_id' => $documentRequest->request_id,
            'field_name' => 'registration_form',
            'field_value' => $storedPath,
        ]);

        $attachmentUrl = route('admin.documents.attachments.show', [
            'documentRequest' => $documentRequest,
            'fieldName' => 'registration_form',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.documents.review', $documentRequest))
            ->assertOk()
            ->assertSee('Open Attachment')
            ->assertSee($attachmentUrl);

        $response = $this->actingAs($admin, 'admin')
            ->get($attachmentUrl)
            ->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('registration file', $response->streamedContent());
    }

    public function test_admin_can_view_document_request_attachment_saved_on_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'student_photo',
            'field_label' => 'Student Photo',
            'field_type' => 'image',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $storedPath = "document-uploads/{$documentRequest->request_id}/student-photo.jpg";
        Storage::disk('public')->put($storedPath, 'photo file');

        Document::create([
            'request_id' => $documentRequest->request_id,
            'field_name' => 'student_photo',
            'field_value' => $storedPath,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.documents.attachments.show', [
                'documentRequest' => $documentRequest,
                'fieldName' => 'student_photo',
            ]))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertStringContainsString('photo file', $response->streamedContent());
    }

    public function test_document_request_file_attachment_requires_admin_authentication(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        $documentType = DocumentType::create([
            'name' => 'Scholarship Request',
            'description' => 'Scholarship form',
            'is_active' => true,
        ]);

        DocumentTypeField::create([
            'document_type_id' => $documentType->id,
            'field_name' => 'registration_form',
            'field_label' => 'Registration Form',
            'field_type' => 'file',
            'is_required' => true,
            'display_order' => 1,
        ]);

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'pending',
        ]);

        $storedPath = "document-uploads/{$documentRequest->request_id}/registration.pdf";
        Storage::disk('local')->put($storedPath, 'registration file');

        Document::create([
            'request_id' => $documentRequest->request_id,
            'field_name' => 'registration_form',
            'field_value' => $storedPath,
        ]);

        $this->get(route('admin.documents.attachments.show', [
            'documentRequest' => $documentRequest,
            'fieldName' => 'registration_form',
        ]))->assertRedirect();
    }
}
