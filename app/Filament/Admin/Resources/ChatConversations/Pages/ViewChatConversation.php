<?php

namespace App\Filament\Admin\Resources\ChatConversations\Pages;

use App\Filament\Admin\Resources\ChatConversations\ChatConversationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewChatConversation extends ViewRecord
{
    protected static string $resource = ChatConversationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Conversation Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('session_id')
                            ->label('Session ID'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'queued' => 'warning',
                                'active' => 'success',
                                'closed' => 'secondary',
                            }),
                        TextEntry::make('customer.name')
                            ->label('Customer')
                            ->default('Guest'),
                        TextEntry::make('agent.name')
                            ->label('Agent')
                            ->default('Unassigned'),
                        TextEntry::make('customer_email')
                            ->label('Customer Email'),
                        TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime(),
                        TextEntry::make('ended_at')
                            ->label('Ended At')
                            ->dateTime(),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ]),
                Section::make('Chat Messages')
                    ->schema([
                        RepeatableEntry::make('messages')
                            ->schema([
                                TextEntry::make('sender_type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'customer' => 'info',
                                        'agent' => 'success',
                                        'system' => 'warning',
                                    }),
                                TextEntry::make('message')
                                    ->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label('Sent At')
                                    ->dateTime(),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Analytics')
                    ->schema([
                        TextEntry::make('analytics.response_time_seconds')
                            ->label('Response Time')
                            ->formatStateUsing(fn ($state) => $state ? gmdate("i:s", $state) . ' min' : 'N/A'),
                        TextEntry::make('analytics.resolution_time_seconds')
                            ->label('Resolution Time')
                            ->formatStateUsing(fn ($state) => $state ? gmdate("i:s", $state) . ' min' : 'N/A'),
                        TextEntry::make('analytics.message_count')
                            ->label('Total Messages'),
                        TextEntry::make('analytics.satisfaction_rating')
                            ->label('Satisfaction Rating')
                            ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : 'Not rated'),
                        TextEntry::make('analytics.satisfaction_feedback')
                            ->label('Feedback')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
