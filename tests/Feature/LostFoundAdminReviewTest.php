<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LostFoundAdminReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_review_lost_found_item_details(): void
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

        $item = LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Blue Umbrella',
            'category' => 'Personal Item',
            'description' => 'Found near the student center lobby.',
            'status' => 'found',
            'approval_status' => 'pending',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);

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
            ->assertSeeText('Approval Status')
            ->assertSeeText('Item Status');
    }
}
