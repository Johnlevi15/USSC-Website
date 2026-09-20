<?php

namespace Tests\Feature;

use App\Mail\DocumentRequestStatusUpdated;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DocumentRequestStatusUpdatedMailTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_status_update_email_header_shows_linked_logo_before_title(): void
    {
        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);
        $documentType = DocumentType::create([
            'name' => 'Document Fee Request Form',
            'description' => 'Standard form',
            'is_active' => true,
        ]);
        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'status' => 'ready',
            'submitted_at' => '2026-09-20 08:00:00',
        ]);

        $mailable = new DocumentRequestStatusUpdated($documentRequest);

        $mailable
            ->assertSeeInHtml('src="'.asset('logo.png').'"', false)
            ->assertDontSeeInHtml('src="cid:', false)
            ->assertDontSeeInHtml('src="data:image/png;base64,', false)
            ->assertSeeInOrderInHtml([
                'src="'.asset('logo.png').'"',
                'USSC Document Request Update',
            ], false);
    }
}
