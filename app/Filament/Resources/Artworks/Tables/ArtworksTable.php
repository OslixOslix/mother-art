<?php

namespace App\Filament\Resources\Artworks\Tables;

use App\Enums\ArtworkImagePreset;
use App\Models\Artwork;
use App\Models\Category;
use App\Services\ArtworkNamingService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ArtworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Фото')
                    ->getStateUsing(fn (Artwork $record): ?string => $record->imageUrl(ArtworkImagePreset::Admin))
                    ->square()
                    ->imageSize('7.5rem'),
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Раздел')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Опубл.')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Раздел')
                    ->relationship('category', 'name'),
                Filter::make('no_category')
                    ->label('Без раздела')
                    ->toggle()
                    ->query(fn ($query) => $query->whereNull('category_id')),
                Filter::make('no_size')
                    ->label('Без размера')
                    ->toggle()
                    ->query(fn ($query) => $query->whereNull('width_cm')->whereNull('height_cm')),
                Filter::make('no_price')
                    ->label('Без цены')
                    ->toggle()
                    ->query(fn ($query) => $query->whereNull('price')),
                TernaryFilter::make('is_published')
                    ->label('Опубликовано'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Опубликовать')
                        ->icon(Heroicon::OutlinedEye)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['is_published' => true]),
                        )),
                    BulkAction::make('unpublish')
                        ->label('Снять с публикации')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['is_published' => false]),
                        )),
                    BulkAction::make('moveToCategory')
                        ->label('Перенести в раздел')
                        ->icon(Heroicon::OutlinedFolderArrowDown)
                        ->schema([
                            Select::make('category_id')
                                ->label('Раздел')
                                ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each(
                            fn ($record) => $record->update(['category_id' => $data['category_id']]),
                        )),
                    BulkAction::make('generateTitleAndDescription')
                        ->label('Дать название и описание')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->requiresConfirmation()
                        ->modalDescription('Для каждой выбранной работы с фото будет поставлена задача в очередь: OpenRouter придумает название и четверостишие.')
                        ->action(function (Collection $records): void {
                            $result = app(ArtworkNamingService::class)->dispatchForArtworks($records);

                            if ($result['queued'] === 0) {
                                Notification::make()
                                    ->title('Нет работ для обработки')
                                    ->body('У выбранных записей нет фотографий.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $body = "В очереди: {$result['queued']} работ. Обработка идёт в фоне.";

                            if ($result['skipped'] > 0) {
                                $body .= " Пропущено без фото: {$result['skipped']}.";
                            }

                            Notification::make()
                                ->title('Генерация названий запущена')
                                ->body($body)
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->label('Удалить'),
                ]),
            ]);
    }
}
