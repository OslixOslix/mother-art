<?php

namespace App\Filament\Resources\OrderRequests\Tables;

use App\Models\OrderRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('artwork.title')
                    ->label('Работа')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => OrderRequest::statuses()[$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderRequest::statuses()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
