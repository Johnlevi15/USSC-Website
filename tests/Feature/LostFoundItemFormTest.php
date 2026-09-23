<?php

namespace Tests\Feature;

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

    public function test_lost_found_gallery_uses_same_origin_storage_image_urls(): void
    {
        config(['app.url' => 'https://ussc.test']);

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

        $this->get(route('lost-found'))
            ->assertOk()
            ->assertSee('src="/storage/lost-found/blue-umbrella.jpg"', false)
            ->assertDontSee('https://ussc.test/storage/lost-found/blue-umbrella.jpg', false);
    }
}
