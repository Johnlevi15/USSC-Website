<?php

namespace Tests\Feature;

use App\Mail\LostFoundItemStatusUpdated;
use App\Models\Admin;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
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

        LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Archived Jacket',
            'category' => 'Clothing',
            'description' => 'Old record.',
            'status' => 'found',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Gym',
            'archived_at' => now(),
            'archived_by' => $admin->admin_id,
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.lost-found.live'))
            ->assertOk()
            ->assertJsonPath('pending_count', 1)
            ->assertJsonPath('processed_count', 1)
            ->assertSee('Blue Umbrella')
            ->assertSee('Red Notebook')
            ->assertDontSee('Archived Jacket');
    }

    public function test_admin_can_archive_and_restore_lost_found_item_without_deleting_image(): void
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
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);
        Storage::disk('public')->put('lost-found/blue-umbrella.jpg', 'umbrella image');

        $this->actingAs($admin, 'admin')
            ->followingRedirects()
            ->patch(route('admin.lost-found.archive.store', $item))
            ->assertOk()
            ->assertSee('id="success-modal"', false)
            ->assertSee('role="dialog"', false)
            ->assertSeeText('Changes Saved')
            ->assertSeeText('Lost-and-found item archived.')
            ->assertSee('onclick="closeSuccessModal()"', false);

        $this->assertNotNull($item->fresh()->archived_at);
        $this->assertSame($admin->admin_id, $item->fresh()->archived_by);
        Storage::disk('public')->assertExists('lost-found/blue-umbrella.jpg');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.lost-found.archive'))
            ->assertOk()
            ->assertSeeText('Blue Umbrella')
            ->assertSeeText('Restore');

        $this->actingAs($admin, 'admin')
            ->followingRedirects()
            ->patch(route('admin.lost-found.restore', $item))
            ->assertOk()
            ->assertSee('id="success-modal"', false)
            ->assertSeeText('Changes Saved')
            ->assertSeeText('Lost-and-found item restored.');

        $this->assertNull($item->fresh()->archived_at);
        $this->assertNull($item->fresh()->archived_by);
    }

    public function test_admin_can_update_lost_found_status_without_sending_email(): void
    {
        Mail::fake();

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
            ->followingRedirects()
            ->patch(route('admin.lost-found.update', $item), [
                'approval_status' => 'approved',
                'status' => 'found',
                'admin_remarks' => 'Ready for claiming at the USSC office.',
            ])
            ->assertOk()
            ->assertSee('id="success-modal"', false)
            ->assertSeeText('Changes Saved')
            ->assertSeeText('Lost-and-found item status updated.');

        $this->assertDatabaseHas('lost_found_items', [
            'item_id' => $item->item_id,
            'approval_status' => 'approved',
            'status' => 'found',
            'admin_remarks' => 'Ready for claiming at the USSC office.',
        ]);
        Mail::assertNothingSent();
    }

    public function test_admin_can_send_lost_found_status_email_for_lost_to_found_update(): void
    {
        Mail::fake();

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
            'description' => 'Lost near the student center lobby.',
            'status' => 'lost',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.lost-found.update', $item), [
                'approval_status' => 'approved',
                'status' => 'found',
                'admin_remarks' => 'Please bring your student ID to verify ownership.',
                'notify_student' => '1',
            ])
            ->assertRedirect(route('admin.lost-found.review', $item))
            ->assertSessionHas('success', 'Lost-and-found item status updated. Student notification sent.');

        Mail::assertSent(
            LostFoundItemStatusUpdated::class,
            function (LostFoundItemStatusUpdated $mail): bool {
                return $mail->oldStatus === 'lost'
                    && $mail->item->status === 'found'
                    && $mail->envelope()->subject === 'USSC Lost & Found Update: Your item may have been found';
            },
        );
    }

    public function test_lost_found_email_includes_status_details_and_admin_remarks(): void
    {
        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);
        $item = LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Blue Umbrella',
            'category' => 'Personal Item',
            'description' => 'Lost near the student center lobby.',
            'status' => 'found',
            'approval_status' => 'approved',
            'admin_remarks' => 'Please bring your student ID to verify ownership.',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);

        $mailable = new LostFoundItemStatusUpdated($item->load('poster'), 'lost');

        $mailable
            ->assertSeeInHtml('Blue Umbrella')
            ->assertSeeInHtml('Lost')
            ->assertSeeInHtml('Found')
            ->assertSeeInHtml('Approved')
            ->assertSeeInHtml('Student Center')
            ->assertSeeInHtml('Please bring your student ID to verify ownership.');
    }

    public function test_lost_found_update_with_blank_reporter_email_saves_without_sending_email(): void
    {
        Mail::fake();

        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);
        $user = User::create([
            'name' => 'Taylor Student',
            'email' => '',
        ]);
        $item = LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Blue Umbrella',
            'category' => 'Personal Item',
            'description' => 'Lost near the student center lobby.',
            'status' => 'lost',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.lost-found.update', $item), [
                'approval_status' => 'approved',
                'status' => 'found',
                'notify_student' => '1',
            ])
            ->assertRedirect(route('admin.lost-found.review', $item))
            ->assertSessionHas('success', 'Lost-and-found item status updated. The update was saved, but the reporter has no email address to notify.');

        $this->assertSame('found', $item->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_lost_found_update_saves_when_email_notification_fails(): void
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
            'description' => 'Lost near the student center lobby.',
            'status' => 'lost',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Student Center',
        ]);
        Mail::shouldReceive('to')
            ->once()
            ->with('taylor@example.test')
            ->andThrow(new RuntimeException('SMTP failed'));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.lost-found.update', $item), [
                'approval_status' => 'approved',
                'status' => 'found',
                'notify_student' => '1',
            ])
            ->assertRedirect(route('admin.lost-found.review', $item))
            ->assertSessionHas('success', 'Lost-and-found item status updated. The update was saved, but the email notification could not be sent.');

        $this->assertSame('found', $item->fresh()->status);
    }
}
