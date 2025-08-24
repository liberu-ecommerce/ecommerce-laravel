<?php

namespace App\Filament\Admin\Resources\Discounts\Pages;

use App\Filament\Admin\Resources\Discounts\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;
}
