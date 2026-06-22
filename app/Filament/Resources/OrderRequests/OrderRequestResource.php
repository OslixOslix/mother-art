<?php

namespace App\Filament\Resources\OrderRequests;

use App\Filament\Resources\OrderRequests\Pages\CreateOrderRequest;
use App\Filament\Resources\OrderRequests\Pages\EditOrderRequest;
use App\Filament\Resources\OrderRequests\Pages\ListOrderRequests;
use App\Filament\Resources\OrderRequests\Schemas\OrderRequestForm;
use App\Filament\Resources\OrderRequests\Tables\OrderRequestsTable;
use App\Models\OrderRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderRequestResource extends Resource
{
    protected static ?string $model = OrderRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    protected static ?string $navigationLabel = 'Заявки';

    public static function form(Schema $schema): Schema
    {
        return OrderRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderRequests::route('/'),
            'create' => CreateOrderRequest::route('/create'),
            'edit' => EditOrderRequest::route('/{record}/edit'),
        ];
    }
}
