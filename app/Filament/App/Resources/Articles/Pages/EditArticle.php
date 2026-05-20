<?php

namespace App\Filament\App\Resources\Articles\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
