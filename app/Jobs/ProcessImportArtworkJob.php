<?php

namespace App\Jobs;

use App\Models\Artwork;
use App\Services\ArtworkImportService;
use App\Services\OpenRouterImageService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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

        try {
            $processedImage = $openRouter->processForProductCard($this->sourcePath);
            $extension = 'png';
        } catch (RuntimeException $exception) {
            if (! $this->isContentModerationRejection($exception)) {
                throw $exception;
            }

            Log::warning('OpenRouter: модерация отклонила изображение, сохраняем оригинал', [
                'source' => $filename,
            ]);

            $processedImage = file_get_contents($this->sourcePath);
            $extension = $this->extensionFromFilename($filename);

            if ($processedImage === false) {
                throw new RuntimeException("Не удалось прочитать файл: {$this->sourcePath}");
            }
        }

        $destination = 'artworks/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($destination, $processedImage);
        File::delete($this->sourcePath);

        Artwork::create([
            'title' => $title,
            'slug' => Artwork::uniqueSlug($title),
            'image_path' => $destination,
            'is_published' => false,
        ]);
    }

    private function isContentModerationRejection(RuntimeException $exception): bool
    {
        return str_contains($exception->getMessage(), 'content moderation');
    }

    private function extensionFromFilename(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpeg' => 'jpg',
            'jpg', 'png', 'webp', 'gif' => $extension,
            default => 'jpg',
        };
    }
}
