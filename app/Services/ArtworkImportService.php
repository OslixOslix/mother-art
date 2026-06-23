<?php

namespace App\Services;

use App\Jobs\ProcessImportArtworkJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ArtworkImportService
{
    /** @var list<string> */
    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Ставит в очередь обработку всех изображений из папки импорта.
     *
     * @return array{queued: int, ignored: int, batch_id: ?string}
     */
    public function dispatchImport(string $path): array
    {
        File::ensureDirectoryExists($path);

        $jobs = [];
        $ignored = 0;

        foreach (File::files($path) as $file) {
            if (! $this->isSupportedImage($file->getExtension())) {
                $ignored++;

                continue;
            }

            $jobs[] = new ProcessImportArtworkJob($file->getPathname());
        }

        if ($jobs === []) {
            return [
                'queued' => 0,
                'ignored' => $ignored,
                'batch_id' => null,
            ];
        }

        $batch = Bus::batch($jobs)
            ->name('import-artworks')
            ->dispatch();

        return [
            'queued' => count($jobs),
            'ignored' => $ignored,
            'batch_id' => $batch->id,
        ];
    }

    public function titleFromFilename(string $filename): string
    {
        $title = Str::of(pathinfo($filename, PATHINFO_FILENAME))
            ->replace(['_', '-'], ' ')
            ->squish()
            ->title()
            ->toString();

        return $title ?: 'Новая работа';
    }

    public function isSupportedImage(string $extension): bool
    {
        return in_array(strtolower($extension), self::SUPPORTED_EXTENSIONS, true);
    }

    public function countImagesInPath(string $path): int
    {
        File::ensureDirectoryExists($path);

        return collect(File::files($path))
            ->filter(fn ($file) => $this->isSupportedImage($file->getExtension()))
            ->count();
    }
}
