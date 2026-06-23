<?php

namespace Tests\Feature;

use App\Jobs\GenerateArtworkMetadataJob;
use App\Models\Artwork;
use App\Services\OpenRouterArtworkNamingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateArtworkMetadataJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('openrouter.api_key', 'test-key');
        Config::set('openrouter.base_url', 'https://openrouter.ai/api/v1');
        Config::set('openrouter.naming.model', 'google/gemini-3.1-flash-image');
    }

    public function test_job_generates_metadata_and_updates_artwork(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/painting.jpg', 'fake image');

        $artwork = Artwork::factory()->draft()->create([
            'title' => 'Старое название',
            'slug' => 'staroe-nazvanie',
            'description' => 'Старое описание',
            'image_path' => 'artworks/painting.jpg',
        ]);

        $metadata = [
            'title' => 'Тихий вечер',
            'slug' => 'tikhiy-vecher',
            'quatrain' => [
                'Строка первая',
                'Строка вторая',
                'Строка третья',
                'Строка четвёртая',
            ],
        ];

        Http::fake([
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $job = new GenerateArtworkMetadataJob($artwork->id);
        $job->handle(app(OpenRouterArtworkNamingService::class));

        $artwork->refresh();

        $this->assertSame('Тихий вечер', $artwork->title);
        $this->assertSame('tixii-vecer', $artwork->slug);
        $this->assertSame("Строка первая\nСтрока вторая\nСтрока третья\nСтрока четвёртая", $artwork->description);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && data_get($body, 'model') === 'google/gemini-3.1-flash-image'
                && data_get($body, 'response_format.type') === 'json_schema'
                && data_get($body, 'response_format.json_schema.name') === 'artwork_metadata'
                && str_starts_with((string) data_get($body, 'messages.0.content.0.image_url.url'), 'data:image/jpeg;base64,')
                && filled(data_get($body, 'messages.0.content.1.text'));
        });
    }

    public function test_job_skips_artwork_without_image_path(): void
    {
        Storage::fake('public');

        $artwork = Artwork::factory()->draft()->create([
            'image_path' => null,
        ]);

        Http::fake();

        $job = new GenerateArtworkMetadataJob($artwork->id);
        $job->handle(app(OpenRouterArtworkNamingService::class));

        $this->assertSame($artwork->fresh()->title, $artwork->title);
        Http::assertNothingSent();
    }

    public function test_job_normalizes_string_quatrain_with_literal_newlines(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/painting.jpg', 'fake image');

        $artwork = Artwork::factory()->draft()->create([
            'image_path' => 'artworks/painting.jpg',
        ]);

        Http::fake([
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Весна',
                                'slug' => 'vesna',
                                'quatrain' => 'Первая строка\\nВторая строка\\nТретья строка\\nЧетвёртая строка',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $job = new GenerateArtworkMetadataJob($artwork->id);
        $job->handle(app(OpenRouterArtworkNamingService::class));

        $this->assertSame(
            "Первая строка\nВторая строка\nТретья строка\nЧетвёртая строка",
            $artwork->fresh()->description,
        );
    }

    public function test_job_ensures_unique_slug_on_collision(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/first.jpg', 'fake image one');
        Storage::disk('public')->put('artworks/second.jpg', 'fake image two');

        Artwork::factory()->draft()->create([
            'title' => 'Тихий вечер',
            'slug' => 'tixii-vecer',
        ]);

        $artwork = Artwork::factory()->draft()->create([
            'image_path' => 'artworks/second.jpg',
        ]);

        Http::fake([
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Тихий вечер',
                                'slug' => 'tikhiy-vecher',
                                'quatrain' => 'Четверостишие',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $job = new GenerateArtworkMetadataJob($artwork->id);
        $job->handle(app(OpenRouterArtworkNamingService::class));

        $this->assertSame('tixii-vecer-2', $artwork->fresh()->slug);
    }
}
