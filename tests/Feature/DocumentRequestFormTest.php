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

        $this->actingAs($admin, 'admin')
            ->get(route('admin.document-types.edit', $documentType))
            ->assertOk()
            ->assertDontSee('name="field_name"', false)
            ->assertDontSeeText('Field Name (internal)')
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

    public function test_admin_document_requests_live_endpoint_returns_latest_rows(): void
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

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.documents.live'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertSee('Taylor Student')
            ->assertSee('Clearance Request')
            ->assertSee("Request #{$documentRequest->request_id}");
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
