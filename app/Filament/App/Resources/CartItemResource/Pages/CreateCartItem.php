<?php

namespace App\Filament\App\Resources\CartItemResource\Pages;

use App\Filament\App\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
}
