<?php

namespace App\Filament\App\Resources\ProductReviews\Pages;

use App\Filament\App\Resources\ProductReviews\ProductReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductReview extends CreateRecord
{
    protected static string $resource = ProductReviewResource::class;
}
