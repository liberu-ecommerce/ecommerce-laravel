<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
