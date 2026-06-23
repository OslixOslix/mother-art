<?php

namespace Tests\Feature;

use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Jobs\GenerateArtworkMetadataJob;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArtworkBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_bulk_publish_artworks(): void
    {
        $this->actingAs(User::factory()->create());

        $artworks = Artwork::factory()->count(2)->draft()->create();

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('publish', $artworks);

        foreach ($artworks as $artwork) {
            $artwork->refresh();

            $this->assertTrue($artwork->is_published);
            $this->assertNotNull($artwork->published_at);
        }
    }

    public function test_can_bulk_unpublish_artworks(): void
    {
        $this->actingAs(User::factory()->create());

        $artworks = Artwork::factory()->count(2)->create();

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('unpublish', $artworks);

        foreach ($artworks as $artwork) {
            $artwork->refresh();

            $this->assertFalse($artwork->is_published);
            $this->assertNull($artwork->published_at);
        }
    }

    public function test_can_bulk_move_artworks_to_category(): void
    {
        $this->actingAs(User::factory()->create());

        $oldCategory = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $artworks = Artwork::factory()->count(2)->create(['category_id' => $oldCategory->id]);

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('moveToCategory', $artworks, data: [
                'category_id' => $newCategory->id,
            ]);

        foreach ($artworks as $artwork) {
            $this->assertSame($newCategory->id, $artwork->fresh()->category_id);
        }
    }

    public function test_can_bulk_delete_artworks(): void
    {
        $this->actingAs(User::factory()->create());

        $artworks = Artwork::factory()->count(2)->create();
        $ids = $artworks->pluck('id')->all();

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('delete', $artworks);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('artworks', ['id' => $id]);
        }
    }

    public function test_can_bulk_dispatch_generate_title_and_description(): void
    {
        Bus::fake();
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Storage::disk('public')->put('artworks/first.jpg', 'fake image');
        Storage::disk('public')->put('artworks/second.jpg', 'fake image');

        $artworks = Artwork::factory()->count(2)->draft()->sequence(
            ['image_path' => 'artworks/first.jpg'],
            ['image_path' => 'artworks/second.jpg'],
        )->create();

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('generateTitleAndDescription', $artworks);

        Bus::assertBatched(function ($batch) {
            return $batch->name === 'generate-artwork-metadata'
                && $batch->jobs->count() === 2
                && $batch->jobs->every(fn ($job) => $job instanceof GenerateArtworkMetadataJob);
        });
    }

    public function test_bulk_generate_skips_artworks_without_image(): void
    {
        Bus::fake();
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Storage::disk('public')->put('artworks/with-photo.jpg', 'fake image');

        $withPhoto = Artwork::factory()->draft()->create([
            'image_path' => 'artworks/with-photo.jpg',
        ]);
        $withoutPhoto = Artwork::factory()->draft()->create([
            'image_path' => null,
        ]);

        Livewire::test(ListArtworks::class)
            ->callTableBulkAction('generateTitleAndDescription', collect([$withPhoto, $withoutPhoto]));

        Bus::assertBatched(function ($batch) use ($withPhoto) {
            return $batch->name === 'generate-artwork-metadata'
                && $batch->jobs->count() === 1
                && $batch->jobs->first()->artworkId === $withPhoto->id;
        });
    }
}
