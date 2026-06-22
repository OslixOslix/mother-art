<?php

namespace App\Filament\Pages;

use App\Services\ArtworkImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

class ImportArtworks extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Импорт фото';

    protected static ?string $title = 'Импорт фото';

    protected string $view = 'filament.pages.import-artworks';

    public function import(): void
    {
        $path = config('gallery.import_path');
        File::ensureDirectoryExists($path);

        $hasImages = collect(File::files($path))
            ->contains(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true));

        if (! $hasImages) {
            Notification::make()
                ->title('В папке импорта нет изображений')
                ->body($path)
                ->warning()
                ->send();

            return;
        }

        $result = app(ArtworkImportService::class)->importFrom($path);

        Notification::make()
            ->title('Импорт завершен')
            ->body("Создано черновиков: {$result['created']}")
            ->success()
            ->send();
    }
}
