<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LostFoundItemFormTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_item_report_rejects_uploaded_image_with_disallowed_extension(): void
    {
        Storage::fake('public');

        $this->from('/report-item')->post('/report-item', [
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'item_name' => 'Blue Umbrella',
            'category' => 'Accessories',
            'description' => 'Found near the main lobby.',
            'image' => UploadedFile::fake()->image('proof.php'),
            'status' => 'found',
            'place' => 'Main Lobby',
        ])
            ->assertRedirect('/report-item')
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('lost_found_items', [
            'item_name' => 'Blue Umbrella',
        ]);
        Storage::disk('public')->assertDirectoryEmpty('/');
    }

    public function test_submitted_lost_found_image_can_be_streamed_for_admin_review(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Portal Administrator',
            'email' => 'admin@example.test',
            'password_hash' => 'not-used',
        ]);

        $this->post('/report-item', [
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'item_name' => 'Blue Umbrella',
            'category' => 'Accessories',
            'description' => 'Found near the main lobby.',
            'image' => UploadedFile::fake()->image('blue-umbrella.jpg'),
            'status' => 'found',
            'place' => 'Main Lobby',
        ])->assertRedirect(route('lost-found'));

        $item = LostFoundItem::query()->firstOrFail();

        Storage::disk('public')->assertExists((string) $item->image_path);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.lost-found.review', $item))
            ->assertOk()
            ->assertSee('src="/lost-found-items/'.$item->item_id.'/image"', false);

        $this->actingAs($admin, 'admin')
            ->get(route('lost-found-items.image', $item))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_lost_found_images_use_the_configured_upload_disk(): void
    {
        config(['filesystems.uploads.lost_found' => 'local']);
        Storage::fake('local');
        Storage::fake('public');

        $this->post('/report-item', [
            'full_name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'item_name' => 'Blue Umbrella',
            'category' => 'Accessories',
            'description' => 'Found near the main lobby.',
            'image' => UploadedFile::fake()->image('blue-umbrella.jpg'),
            'status' => 'found',
            'place' => 'Main Lobby',
        ])->assertRedirect(route('lost-found'));

        $item = LostFoundItem::query()->firstOrFail();

        Storage::disk('local')->assertExists((string) $item->image_path);
        Storage::disk('public')->assertMissing((string) $item->image_path);
    }

    public function test_lost_found_gallery_uses_same_origin_image_route_urls(): void
    {
        config(['app.url' => 'https://ussc.test']);
        Storage::fake('public');

        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        LostFoundItem::create([
            'posted_by' => $user->id,
            'item_name' => 'Blue Umbrella',
            'category' => 'Accessories',
            'description' => 'Found near the main lobby.',
            'image_path' => 'lost-found/blue-umbrella.jpg',
            'status' => 'found',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'place' => 'Main Lobby',
        ]);

        Storage::disk('public')->put('lost-found/blue-umbrella.jpg', 'umbrella image');

        $this->get(route('lost-found'))
            ->assertOk()
            ->assertSee('src="/lost-found-items/1/image"', false)
            ->assertDontSee('https://ussc.test/lost-found-items/1/image', false);

        $response = $this->get(route('lost-found-items.image', 1))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertStringContainsString('umbrella image', $response->streamedContent());
    }

    public function test_lost_found_gallery_has_view_more_pagination_controls(): void
    {
        $user = User::create([
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
        ]);

        foreach (range(1, 11) as $index) {
            LostFoundItem::create([
                'posted_by' => $user->id,
                'item_name' => "Blue Umbrella {$index}",
                'category' => 'Accessories',
                'description' => 'Found near the main lobby.',
                'status' => 'found',
                'approval_status' => 'approved',
                'submitted_at' => now(),
                'place' => 'Main Lobby',
            ]);
        }

        $this->get(route('lost-found'))
            ->assertOk()
            ->assertSee('id="view-more"', false)
            ->assertSee('const pageSize = 10;', false)
            ->assertSee('visibleLimits[active] += pageSize;', false)
            ->assertSeeText('View More');
    }
}
