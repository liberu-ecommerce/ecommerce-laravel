<?php

namespace App\Filament\Admin\Resources\Coupons\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\Coupons\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
