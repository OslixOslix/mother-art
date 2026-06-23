<?php

namespace App\Jobs;

use App\Models\Artwork;
use App\Services\OpenRouterArtworkNamingService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateArtworkMetadataJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public int $timeout = 600;

    public function __construct(
        public int $artworkId,
    ) {}

    public function handle(OpenRouterArtworkNamingService $namingService): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $artwork = Artwork::query()->find($this->artworkId);

        if ($artwork === null || blank($artwork->image_path)) {
            return;
        }

        $absolutePath = Storage::disk('public')->path($artwork->image_path);

        if (! is_file($absolutePath)) {
            return;
        }

        $metadata = $namingService->generateMetadata($absolutePath);

        $artwork->update([
            'title' => $metadata['title'],
            'slug' => Artwork::uniqueSlug($metadata['title'], $artwork->id),
            'description' => $metadata['quatrain'],
        ]);
    }
}
