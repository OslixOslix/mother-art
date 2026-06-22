<?php

namespace App\Filament\Resources\OrderRequests\Pages;

use App\Filament\Resources\OrderRequests\OrderRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderRequest extends CreateRecord
{
    protected static string $resource = OrderRequestResource::class;
}
