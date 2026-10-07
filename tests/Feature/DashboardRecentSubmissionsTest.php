<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardRecentSubmissionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_shows_recent_submissions_by_time_with_submitter_details(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(12, 0));

        $admin = Admin::create([
            'name' => 'Dashboard Admin',
            'email' => 'dashboard-admin@example.test',
            'password_hash' => 'unused',
        ]);
        $newestStudent = User::create([
            'name' => 'Newest Student',
            'email' => 'newest@example.test',
        ]);
        $olderStudent = User::create([
            'name' => 'Older Student',
            'email' => 'older@example.test',
        ]);
        $newestDocumentType = DocumentType::create([
            'name' => 'Newest Request Type',
            'is_active' => true,
        ]);
        $olderDocumentType = DocumentType::create([
            'name' => 'Older Request Type',
            'is_active' => true,
        ]);

        DocumentRequest::create([
            'user_id' => $newestStudent->id,
            'document_type_id' => $newestDocumentType->id,
            'status' => 'pending',
            'submitted_at' => now()->subHours(2),
        ]);
        DocumentRequest::create([
            'user_id' => $olderStudent->id,
            'document_type_id' => $olderDocumentType->id,
            'status' => 'pending',
            'submitted_at' => now()->subHours(5),
        ]);

        LostFoundItem::create([
            'posted_by' => $newestStudent->id,
            'item_name' => 'Newest Report',
            'category' => 'Personal Item',
            'description' => 'Recently reported item.',
            'status' => 'found',
            'approval_status' => 'pending',
            'submitted_at' => now()->subMinutes(30),
            'place' => 'Library',
        ]);
        LostFoundItem::create([
            'posted_by' => $olderStudent->id,
            'item_name' => 'Older Report',
            'category' => 'Personal Item',
            'description' => 'Earlier reported item.',
            'status' => 'found',
            'approval_status' => 'pending',
            'submitted_at' => now()->subHours(4),
            'place' => 'Gym',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Newest Student')
            ->assertSeeText('Newest Request Type')
            ->assertSeeText('Submitted 2 hours ago')
            ->assertSeeText('Newest Report')
            ->assertSeeText('Reported by Newest Student')
            ->assertSeeText('Submitted 30 minutes ago')
            ->assertSeeText('Older Report');

        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Newest Request Type') < strpos($content, 'Older Request Type'));
        $this->assertTrue(strpos($content, 'Newest Report') < strpos($content, 'Older Report'));
    }

    public function test_pending_document_cards_prioritize_the_latest_submission_time(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(12, 0));

        $admin = Admin::create([
            'name' => 'Dashboard Admin',
            'email' => 'dashboard-admin@example.test',
            'password_hash' => 'unused',
        ]);
        $student = User::create([
            'name' => 'Pending Student',
            'email' => 'pending@example.test',
        ]);
        $newestDocumentType = DocumentType::create([
            'name' => 'Latest Submission',
            'is_active' => true,
        ]);
        $olderDocumentType = DocumentType::create([
            'name' => 'Earlier Submission',
            'is_active' => true,
        ]);

        DocumentRequest::create([
            'user_id' => $student->id,
            'document_type_id' => $newestDocumentType->id,
            'status' => 'pending',
            'submitted_at' => now()->subMinutes(15),
        ]);
        DocumentRequest::create([
            'user_id' => $student->id,
            'document_type_id' => $olderDocumentType->id,
            'status' => 'pending',
            'submitted_at' => now()->subHours(3),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.documents'))
            ->assertOk()
            ->assertSeeText('Submitted 15 minutes ago')
            ->assertSeeText('Latest Submission');

        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Latest Submission') < strpos($content, 'Earlier Submission'));
    }
}
