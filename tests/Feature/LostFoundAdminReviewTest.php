<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LostFoundAdminReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_review_lost_found_item_details(): void
    {
        config(['filesystems.uploads.lost_found' => 'public']);
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

        $item = LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Blue Umbrella',
            'category' => 'Personal Item',
            'description' => 'Found near the student center lobby.',
            'image_path' => 'lost-found/blue-umbrella.jpg',
            'status' => 'found',
            'approval_status' => 'pending',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);

        Storage::disk('public')->put('lost-found/blue-umbrella.jpg', 'umbrella image');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.lost-found.review', $item))
            ->assertOk()
            ->assertSeeText('Review Lost & Found Item')
            ->assertSeeText('Blue Umbrella')
            ->assertSeeText('Personal Item')
            ->assertSeeText('Student Center')
            ->assertSeeText('Found near the student center lobby.')
            ->assertSeeText('Taylor Student')
            ->assertSeeText('taylor@example.test')
            ->assertSee('src="/lost-found-items/'.$item->item_id.'/image"', false)
            ->assertSeeText('Approval Status')
            ->assertSeeText('Item Status');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('lost-found-items.image', $item))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertStringContainsString('umbrella image', $response->streamedContent());
    }

    public function test_admin_lost_found_live_endpoint_returns_pending_and_processed_items(): void
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

        LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Blue Umbrella',
            'category' => 'Personal Item',
            'description' => 'Found near the student center lobby.',
            'status' => 'found',
            'approval_status' => 'pending',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);

        LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Red Notebook',
            'category' => 'School Supply',
            'description' => 'Found near the library.',
            'status' => 'found',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Library',
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.lost-found.live'))
            ->assertOk()
            ->assertJsonPath('pending_count', 1)
            ->assertJsonPath('processed_count', 1)
            ->assertSee('Blue Umbrella')
            ->assertSee('Red Notebook');
    }
}
