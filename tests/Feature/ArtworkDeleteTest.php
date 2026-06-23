<?php

namespace Tests\Feature;

use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArtworkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_artwork_removes_image_file_from_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/sample.jpg', 'fake image');

        $artwork = Artwork::factory()->create([
            'image_path' => 'artworks/sample.jpg',
        ]);

        $artwork->delete();

        $this->assertDatabaseMissing('artworks', ['id' => $artwork->id]);
        Storage::disk('public')->assertMissing('artworks/sample.jpg');
    }

    public function test_deleting_artwork_without_image_does_not_fail(): void
    {
        Storage::fake('public');

        $artwork = Artwork::factory()->create([
            'image_path' => null,
        ]);

        $artwork->delete();

        $this->assertDatabaseMissing('artworks', ['id' => $artwork->id]);
    }

    public function test_bulk_delete_removes_image_files_from_storage(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $paths = ['artworks/first.jpg', 'artworks/second.jpg'];

        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'fake image');
        }

        $artworks = Artwork::factory()->count(2)->sequence(
            ['image_path' => $paths[0]],
            ['image_path' => $paths[1]],
        )->create();

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('delete', $artworks);

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
