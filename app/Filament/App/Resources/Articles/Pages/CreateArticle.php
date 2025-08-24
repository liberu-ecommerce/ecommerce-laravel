<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
