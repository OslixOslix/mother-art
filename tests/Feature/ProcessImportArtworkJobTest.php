<?php

namespace Tests\Feature;

use App\Jobs\ProcessImportArtworkJob;
use App\Models\Artwork;
use App\Services\ArtworkImportService;
use App\Services\OpenRouterImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessImportArtworkJobTest extends TestCase
{
    use RefreshDatabase;

    private const FAKE_PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('openrouter.api_key', 'test-key');
        Config::set('openrouter.base_url', 'https://openrouter.ai/api/v1');
        Config::set('openrouter.model', 'sourceful/riverflow-v2.5-pro');
    }

    public function test_job_processes_image_and_creates_draft_artwork(): void
    {
        Storage::fake('public');

        $path = storage_path('framework/testing/import-artworks-job');
        File::ensureDirectoryExists($path);
        File::put($path.'/ceramic-vase.jpg', 'fake source image');

        Http::fake([
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'images' => [
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => self::FAKE_PNG_DATA_URL],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $job = new ProcessImportArtworkJob($path.'/ceramic-vase.jpg');
        $job->handle(app(OpenRouterImageService::class), app(ArtworkImportService::class));

        $this->assertDatabaseHas('artworks', [
            'title' => 'Ceramic Vase',
            'is_published' => false,
        ]);

        $artwork = Artwork::firstOrFail();
        Storage::disk('public')->assertExists($artwork->image_path);
        $this->assertStringEndsWith('.png', $artwork->image_path);
        $this->assertFileDoesNotExist($path.'/ceramic-vase.jpg');

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && data_get($body, 'model') === 'sourceful/riverflow-v2.5-pro'
                && data_get($body, 'image_config.aspect_ratio') === '1:1'
                && data_get($body, 'image_config.background_hex_color') === '#FFFFFF'
                && str_starts_with((string) data_get($body, 'messages.0.content.0.image_url.url'), 'data:image/jpeg;base64,');
        });

        File::deleteDirectory($path);
    }

    public function test_job_uses_temp_public_url_for_large_files(): void
    {
        Storage::fake('public');
        Config::set('openrouter.import.max_base64_bytes', 10);

        $path = storage_path('framework/testing/import-artworks-job-large');
        File::ensureDirectoryExists($path);
        File::put($path.'/large-piece.jpg', str_repeat('x', 100));

        Http::fake([
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'images' => [
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => self::FAKE_PNG_DATA_URL],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $job = new ProcessImportArtworkJob($path.'/large-piece.jpg');
        $job->handle(app(OpenRouterImageService::class), app(ArtworkImportService::class));

        Http::assertSent(function ($request) {
            $imageUrl = (string) data_get($request->data(), 'messages.0.content.0.image_url.url');

            return str_contains($imageUrl, '/storage/import-temp/')
                && ! str_starts_with($imageUrl, 'data:');
        });

        Storage::disk('public')->assertMissing('import-temp/large-piece.jpg');

        File::deleteDirectory($path);
    }

    public function test_job_skips_missing_source_file(): void
    {
        Storage::fake('public');

        $job = new ProcessImportArtworkJob(storage_path('framework/testing/missing-file.jpg'));
        $job->handle(app(OpenRouterImageService::class), app(ArtworkImportService::class));

        $this->assertDatabaseCount('artworks', 0);
        Http::assertNothingSent();
    }

    public function test_job_imports_original_when_content_moderation_rejects(): void
    {
        Storage::fake('public');

        $path = storage_path('framework/testing/import-artworks-job-moderation');
        File::ensureDirectoryExists($path);
        File::put($path.'/nude-study.jpg', 'original image bytes');

        Http::fake([
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Provider returned error',
                    'metadata' => [
                        'raw' => '{"error":"Generated image rejected by content moderation."}',
                    ],
                ],
            ], 400),
        ]);

        $job = new ProcessImportArtworkJob($path.'/nude-study.jpg');
        $job->handle(app(OpenRouterImageService::class), app(ArtworkImportService::class));

        $this->assertDatabaseHas('artworks', [
            'title' => 'Nude Study',
            'is_published' => false,
        ]);

        $artwork = Artwork::firstOrFail();
        $this->assertStringEndsWith('.jpg', $artwork->image_path);
        Storage::disk('public')->assertExists($artwork->image_path);
        $this->assertSame('original image bytes', Storage::disk('public')->get($artwork->image_path));
        $this->assertFileDoesNotExist($path.'/nude-study.jpg');

        File::deleteDirectory($path);
    }
}
