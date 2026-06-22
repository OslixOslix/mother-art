<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Services\ArtworkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_import_queues_supported_images_in_batch(): void
    {
        Bus::fake();

        $path = storage_path('framework/testing/import-artworks-dispatch');
        File::ensureDirectoryExists($path);
        File::put($path.'/first-work.jpg', 'fake image');
        File::put($path.'/notes.txt', 'ignored');

        $result = app(ArtworkImportService::class)->dispatchImport($path);

        $this->assertSame(1, $result['queued']);
        $this->assertSame(1, $result['ignored']);
        $this->assertNotNull($result['batch_id']);

        Bus::assertBatched(function ($batch) {
            return $batch->name === 'import-artworks' && $batch->jobs->count() === 1;
        });

        $this->assertFileExists($path.'/first-work.jpg');

        File::deleteDirectory($path);
    }

    public function test_title_from_filename_formats_name(): void
    {
        $service = app(ArtworkImportService::class);

        $this->assertSame('First Work', $service->titleFromFilename('first-work.jpg'));
        $this->assertSame('Новая работа', $service->titleFromFilename('.jpg'));
    }

    public function test_imported_artwork_image_url_is_relative(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/sample.jpg', 'fake image');

        $artwork = Artwork::factory()->create([
            'image_path' => 'artworks/sample.jpg',
        ]);

        $this->assertSame('/storage/'.$artwork->image_path, $artwork->imageUrl());
    }

    public function test_public_storage_files_are_served(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/sample.jpg', 'fake image');

        $this->get('/storage/artworks/sample.jpg')
            ->assertOk();
    }
}
