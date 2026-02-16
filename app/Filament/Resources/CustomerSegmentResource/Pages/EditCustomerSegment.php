<?php

namespace App\Filament\Resources\CustomerSegmentResource\Pages;

use App\Filament\Resources\CustomerSegmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerSegment extends EditRecord
{
    protected static string $resource = CustomerSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculate')
                ->label('Recalculate Members')
                ->icon('heroicon-o-calculator')
                ->action(fn () => $this->record->calculateMembers())
                ->requiresConfirmation()
                ->successNotificationTitle('Members recalculated successfully'),
            Actions\DeleteAction::make(),
        ];
    }
}
