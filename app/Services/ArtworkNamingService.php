<?php

namespace App\Services;

use App\Jobs\GenerateArtworkMetadataJob;
use App\Models\Artwork;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;

class ArtworkNamingService
{
    /**
     * Ставит в очередь генерацию названия и описания для выбранных работ.
     *
     * @param  Collection<int, Artwork>  $artworks
     * @return array{queued: int, skipped: int, batch_id: ?string}
     */
    public function dispatchForArtworks(Collection $artworks): array
    {
        $jobs = [];
        $skipped = 0;

        foreach ($artworks as $artwork) {
            if (blank($artwork->image_path)) {
                $skipped++;

                continue;
            }

            $jobs[] = new GenerateArtworkMetadataJob($artwork->id);
        }

        if ($jobs === []) {
            return [
                'queued' => 0,
                'skipped' => $skipped,
                'batch_id' => null,
            ];
        }

        $batch = Bus::batch($jobs)
            ->name('generate-artwork-metadata')
            ->dispatch();

        return [
            'queued' => count($jobs),
            'skipped' => $skipped,
            'batch_id' => $batch->id,
        ];
    }
}
