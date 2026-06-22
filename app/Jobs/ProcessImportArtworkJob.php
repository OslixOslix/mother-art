<?php

namespace App\Jobs;

use App\Models\Artwork;
use App\Services\ArtworkImportService;
use App\Services\OpenRouterImageService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessImportArtworkJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public int $timeout = 600;

    public function __construct(
        public string $sourcePath,
    ) {}

    public function handle(OpenRouterImageService $openRouter, ArtworkImportService $importService): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (! is_file($this->sourcePath)) {
            return;
        }

        $filename = basename($this->sourcePath);
        $title = $importService->titleFromFilename($filename);
        $processedImage = $openRouter->processForProductCard($this->sourcePath);

        $destination = 'artworks/'.Str::uuid().'.png';

        Storage::disk('public')->put($destination, $processedImage);
        File::delete($this->sourcePath);

        Artwork::create([
            'title' => $title,
            'slug' => Artwork::uniqueSlug($title),
            'image_path' => $destination,
            'is_published' => false,
        ]);
    }
}
