<?php

namespace App\Filament\Resources\CustomerSegmentResource\Pages;

use App\Filament\Resources\CustomerSegmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerSegment extends CreateRecord
{
    protected static string $resource = CustomerSegmentResource::class;

    protected function afterCreate(): void
    {
        // Calculate members after creating segment
        $this->record->calculateMembers();
    }
}
