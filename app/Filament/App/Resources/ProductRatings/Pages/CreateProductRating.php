<?php

namespace App\Filament\App\Resources\ProductRatings\Pages;

use App\Filament\App\Resources\ProductRatings\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductRating extends CreateRecord
{
    protected static string $resource = ProductRatingResource::class;
}
