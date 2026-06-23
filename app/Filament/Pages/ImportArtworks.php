<?php

namespace App\Filament\Pages;

use App\Services\ArtworkImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;

/**
 * @property-read Schema $form
 */
class ImportArtworks extends Page
{
    use RestrictsFileUploadsToSchemaComponents;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Импорт фото';

    protected static ?string $title = 'Импорт фото';

    protected string $view = 'filament.pages.import-artworks';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public ?string $batchId = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photos')
                    ->label('Изображения')
                    ->multiple()
                    ->disk('import')
                    ->directory('')
                    ->image()
                    ->maxFiles(100)
                    ->maxSize(20480)
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                    ])
                    ->helperText('До 100 файлов за раз. Форматы: jpg, jpeg, png, webp, gif.'),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        $pending = $this->pendingImportCount();

        return $schema
            ->components([
                Section::make('Массовая загрузка')
                    ->description(
                        'Выберите фото и нажмите «Загрузить и импортировать». Каждое изображение отправляется в OpenRouter: '
                        .'объект на белом фоне 1:1, готово для карточки товара. После обработки создаются неопубликованные черновики в разделе «Работы».'
                        .($pending > 0 ? " В папке импорта ожидает обработки: {$pending} шт." : '')
                    )
                    ->schema([
                        $this->getFormContentComponent(),
                    ]),
            ]);
    }

    public function uploadAndImport(): void
    {
        $photos = $this->form->getState()['photos'] ?? [];

        if ($photos === [] || $photos === null) {
            Notification::make()
                ->title('Выберите изображения')
                ->body('Добавьте хотя бы один файл для загрузки.')
                ->warning()
                ->send();

            return;
        }

        $this->form->fill();

        $this->startImport(count($photos));
    }

    public function import(): void
    {
        $path = config('gallery.import_path');
        File::ensureDirectoryExists($path);

        $importService = app(ArtworkImportService::class);

        $hasImages = collect(File::files($path))
            ->contains(fn ($file) => $importService->isSupportedImage($file->getExtension()));

        if (! $hasImages) {
            Notification::make()
                ->title('В папке импорта нет изображений')
                ->body('Загрузите фото через форму выше или скопируйте файлы в '.$path)
                ->warning()
                ->send();

            return;
        }

        $this->startImport();
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

    public function pendingImportCount(): int
    {
        $path = config('gallery.import_path');
        File::ensureDirectoryExists($path);

        return app(ArtworkImportService::class)->countImagesInPath($path);
    }

    protected function startImport(?int $uploadedCount = null): void
    {
        $result = app(ArtworkImportService::class)->dispatchImport(config('gallery.import_path'));
        $this->batchId = $result['batch_id'];

        if ($result['queued'] === 0) {
            Notification::make()
                ->title('Нет изображений для импорта')
                ->warning()
                ->send();

            return;
        }

        $body = "В очереди: {$result['queued']} изображений. Обработка идёт в фоне.";

        if ($uploadedCount !== null) {
            $body = "Загружено: {$uploadedCount}. ".$body;
        }

        if ($result['ignored'] > 0) {
            $body .= " Пропущено неподдерживаемых файлов: {$result['ignored']}.";
        }

        Notification::make()
            ->title('Импорт запущен')
            ->body($body)
            ->success()
            ->send();
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('import-form')
            ->livewireSubmitHandler('uploadAndImport');
    }
}
