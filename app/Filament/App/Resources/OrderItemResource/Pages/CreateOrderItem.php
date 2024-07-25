<?php

namespace App\Filament\App\Resources\OrderItemResource\Pages;

use App\Filament\App\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;
}
