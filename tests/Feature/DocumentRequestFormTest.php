<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentRequestFormTest extends TestCase
{
    public function test_document_request_form_shows_the_document_fee_request_option(): void
    {
        $this->get('/document-request')
            ->assertSee('<select required name="document_type"', false)
            ->assertSee('Document Fee Request Form');
    }

    public function test_document_request_form_rejects_an_unavailable_document_type(): void
    {
        $this->from('/document-request')->post('/document-request', [
            'document_type' => 'Unavailable document',
            'full_name' => 'Taylor Student',
            'student_id' => '20260001',
            'department' => 'College of Arts and Sciences',
            'year_section' => 'Fourth Year A',
            'email' => 'taylor@example.test',
            'purpose' => 'Scholarship requirements',
        ])
            ->assertRedirect('/document-request')
            ->assertSessionHasErrors('document_type');
    }
}
