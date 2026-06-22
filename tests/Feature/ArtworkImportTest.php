<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Services\ArtworkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_draft_artworks_from_supported_images(): void
    {
        Storage::fake('public');

        $path = storage_path('framework/testing/import-artworks');
        File::ensureDirectoryExists($path);
        File::put($path.'/first-work.jpg', 'fake image');
        File::put($path.'/notes.txt', 'ignored');

        $result = app(ArtworkImportService::class)->importFrom($path);

        $this->assertSame(['created' => 1, 'ignored' => 1], $result);
        $this->assertDatabaseHas('artworks', [
            'title' => 'First Work',
            'is_published' => false,
        ]);

        $artwork = Artwork::firstOrFail();
        Storage::disk('public')->assertExists($artwork->image_path);
        $this->assertFileDoesNotExist($path.'/first-work.jpg');
        $this->assertFileExists($path.'/notes.txt');

        File::deleteDirectory($path);
    }

    public function test_imported_artwork_image_url_is_relative(): void
    {
        Storage::fake('public');

        $path = storage_path('framework/testing/import-artworks-url');
        File::ensureDirectoryExists($path);
        File::put($path.'/sample.jpg', 'fake image');

        app(ArtworkImportService::class)->importFrom($path);

        $artwork = Artwork::firstOrFail();

        $this->assertSame('/storage/'.$artwork->image_path, $artwork->imageUrl());

        File::deleteDirectory($path);
    }

    public function test_public_storage_files_are_served(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/sample.jpg', 'fake image');

        $this->get('/storage/artworks/sample.jpg')
            ->assertOk();
    }
}
