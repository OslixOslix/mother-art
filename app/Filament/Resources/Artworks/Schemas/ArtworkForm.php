<?php

namespace App\Filament\Resources\Artworks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Работа')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('URL slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('category_id')
                            ->label('Раздел')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('price')
                            ->label('Цена, ₽')
                            ->numeric()
                            ->minValue(0),
                        FileUpload::make('image_path')
                            ->label('Главное фото')
                            ->disk('public')
                            ->directory('artworks')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label('Опубликовано')
                            ->default(false),
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
