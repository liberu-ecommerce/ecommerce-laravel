<?php

namespace App\Filament\Admin\Resources\ChatConversations\Pages;

use App\Filament\Admin\Resources\ChatConversations\ChatConversationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListChatConversations extends ListRecords
{
    protected static string $resource = ChatConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('agent_dashboard')
                ->label('Agent Dashboard')
                ->url(route('filament.admin.pages.chat-agent-dashboard'))
                ->color('primary'),
        ];
    }
}
