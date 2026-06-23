<?php

namespace Tests\Feature;

use App\Enums\ArtworkImagePreset;
use App\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_image_url_without_preset_returns_original_storage_path(): void
    {
        $this->seedSampleImage('artworks/sample.jpg');

        $artwork = Artwork::factory()->create([
            'image_path' => 'artworks/sample.jpg',
        ]);

        $this->assertSame('/storage/artworks/sample.jpg', $artwork->imageUrl());
    }

    public function test_image_url_with_preset_returns_glide_route(): void
    {
        $this->seedSampleImage('artworks/sample.jpg');

        $artwork = Artwork::factory()->create([
            'image_path' => 'artworks/sample.jpg',
        ]);

        $url = $artwork->imageUrl(ArtworkImagePreset::Card);

        $this->assertNotNull($url);
        $this->assertStringContainsString('/img/card/artworks/sample.jpg', $url);
    }

    public function test_image_route_returns_manipulated_image(): void
    {
        $this->seedSampleImage('artworks/sample.jpg');

        $response = $this->get('/img/card/artworks/sample.jpg');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertNotSame(
            Storage::disk('public')->get('artworks/sample.jpg'),
            $response->getContent(),
        );
    }

    public function test_image_route_rejects_paths_outside_artworks_directory(): void
    {
        $this->seedSampleImage('imports/sample.jpg');

        $this->get('/img/card/imports/sample.jpg')->assertNotFound();
    }

    public function test_image_route_rejects_unknown_preset(): void
    {
        $this->seedSampleImage('artworks/sample.jpg');

        $this->get('/img/unknown/artworks/sample.jpg')->assertNotFound();
    }

    public function test_artwork_detail_page_uses_detail_preset_and_original_for_zoom(): void
    {
        $this->withoutVite();
        $this->seedSampleImage('artworks/sample.jpg');

        $artwork = Artwork::factory()->create([
            'title' => 'Zoom Work',
            'image_path' => 'artworks/sample.jpg',
        ]);

        $response = $this->get(route('artworks.show', $artwork));

        $response->assertOk();
        $response->assertSee('/img/detail/artworks/sample.jpg', false);
        $response->assertSee('data-zoom-src="/storage/artworks/sample.jpg"', false);
        $response->assertSee('data-artwork-zoom', false);
    }

    private function seedSampleImage(string $path): void
    {
        Storage::disk('public')->put($path, $this->minimalJpeg());
    }

    private function minimalJpeg(): string
    {
        $image = imagecreatetruecolor(40, 50);
        $background = imagecolorallocate($image, 120, 80, 40);
        imagefill($image, 0, 0, $background);

        ob_start();
        imagejpeg($image, quality: 90);
        $contents = ob_get_clean() ?: '';
        imagedestroy($image);

        return $contents;
    }
}
