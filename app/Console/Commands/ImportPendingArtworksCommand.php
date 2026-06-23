<?php

namespace App\Console\Commands;

use App\Jobs\ProcessImportArtworkJob;
use App\Services\ArtworkImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPendingArtworksCommand extends Command
{
    protected $signature = 'gallery:import-pending';

    protected $description = 'Ставит в очередь файлы из папки импорта, если очередь простаивает';

    public function handle(ArtworkImportService $importService): int
    {
        $path = config('gallery.import_path');
        $pendingFiles = $importService->countImagesInPath($path);

        if ($pendingFiles === 0) {
            return self::SUCCESS;
        }

        if ($this->hasActiveImportJobs()) {
            return self::SUCCESS;
        }

        $result = $importService->dispatchImport($path);

        if ($result['queued'] === 0) {
            return self::SUCCESS;
        }

        $this->info("В очередь добавлено: {$result['queued']} изображений.");

        return self::SUCCESS;
    }

    private function hasActiveImportJobs(): bool
    {
        $needle = class_basename(ProcessImportArtworkJob::class);

        return DB::table('jobs')
            ->where('payload', 'like', '%'.$needle.'%')
            ->exists();
    }
}
