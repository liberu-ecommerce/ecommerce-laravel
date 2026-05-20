<?php

namespace App\Filament\Admin\Resources\Coupons\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Coupons\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
