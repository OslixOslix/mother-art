<?php

namespace App\Filament\Resources\Artworks\Tables;

use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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
                    ->disk('public')
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
                    DeleteBulkAction::make()
                        ->label('Удалить'),
                ]),
            ]);
    }
}
