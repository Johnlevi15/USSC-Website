<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentTypeField;
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
                'field_options' => '["I confirm this request is accurate"]',
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
                'field_options' => '["Scholarship Application", "Other"]',
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
}
