<?php

namespace App\Filament\Pages;

use App\Services\ArtworkImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;

class ImportArtworks extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Импорт фото';

    protected static ?string $title = 'Импорт фото';

    protected string $view = 'filament.pages.import-artworks';

    public ?string $batchId = null;

    public function import(): void
    {
        $path = config('gallery.import_path');
        File::ensureDirectoryExists($path);

        $hasImages = collect(File::files($path))
            ->contains(fn ($file) => app(ArtworkImportService::class)->isSupportedImage($file->getExtension()));

        if (! $hasImages) {
            Notification::make()
                ->title('В папке импорта нет изображений')
                ->body($path)
                ->warning()
                ->send();

            return;
        }

        $result = app(ArtworkImportService::class)->dispatchImport($path);
        $this->batchId = $result['batch_id'];

        $body = "В очереди: {$result['queued']} изображений. Обработка идёт в фоне — убедитесь, что запущен воркер очереди.";

        if ($result['ignored'] > 0) {
            $body .= " Пропущено неподдерживаемых файлов: {$result['ignored']}.";
        }

        Notification::make()
            ->title('Импорт запущен')
            ->body($body)
            ->success()
            ->send();
    }

    /**
     * @return array{total: int, processed: int, pending: int, failed: int, finished: bool, cancelled: bool}|null
     */
    public function batchStatus(): ?array
    {
        if ($this->batchId === null) {
            return null;
        }

        $batch = Bus::findBatch($this->batchId);

        if ($batch === null) {
            return null;
        }

        return [
            'total' => $batch->totalJobs,
            'processed' => $batch->processedJobs(),
            'pending' => $batch->pendingJobs,
            'failed' => $batch->failedJobs,
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ];
    }
}
