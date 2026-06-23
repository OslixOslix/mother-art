<?php

namespace App\Console\Commands;

use App\Enums\ArtworkImagePreset;
use App\Models\Artwork;
use App\Services\ArtworkImageServer;
use Illuminate\Console\Command;
use League\Glide\Filesystem\FileNotFoundException;

class WarmArtworkImageCacheCommand extends Command
{
    protected $signature = 'artworks:warm-image-cache';

    protected $description = 'Сгенерировать кэш пресетов изображений для всех работ с фото';

    public function handle(ArtworkImageServer $images): int
    {
        $presetNames = config('artwork-images.warmable_presets', []);

        if ($presetNames === []) {
            $this->error('Список пресетов для прогрева пуст.');

            return self::FAILURE;
        }

        $presets = array_map(
            fn (string $name): ArtworkImagePreset => ArtworkImagePreset::from($name),
            $presetNames,
        );

        $artworks = Artwork::query()
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->get();

        if ($artworks->isEmpty()) {
            $this->info('Нет работ с фотографиями.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($artworks->count());
        $bar->start();

        $generated = 0;
        $skipped = 0;

        foreach ($artworks as $artwork) {
            $path = (string) $artwork->image_path;

            if (! $images->isAllowedSourcePath($path)) {
                $skipped++;
                $bar->advance();

                continue;
            }

            foreach ($presets as $preset) {
                try {
                    $images->makeImage($path, $preset);
                    $generated++;
                } catch (FileNotFoundException) {
                    $skipped++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Готово. Сгенерировано вариантов: {$generated}, пропущено: {$skipped}.");

        return self::SUCCESS;
    }
}
