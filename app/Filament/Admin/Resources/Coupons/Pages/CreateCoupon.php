<?php

namespace App\Filament\Admin\Resources\Coupons\Pages;

use App\Filament\Admin\Resources\Coupons\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;
}
