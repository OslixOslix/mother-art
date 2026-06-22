<?php

namespace App\Filament\Resources\OrderRequests\Schemas;

use App\Models\OrderRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('artwork_id')
                    ->label('Работа')
                    ->relationship('artwork', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->label('Статус')
                    ->options(OrderRequest::statuses())
                    ->required(),
                TextInput::make('customer_name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('customer_email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('customer_phone')
                    ->label('Телефон')
                    ->maxLength(255),
                Textarea::make('message')
                    ->label('Сообщение')
                    ->columnSpanFull(),
            ]);
    }
}
