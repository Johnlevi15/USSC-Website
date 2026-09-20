<?php

namespace Tests\Feature;

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
}
