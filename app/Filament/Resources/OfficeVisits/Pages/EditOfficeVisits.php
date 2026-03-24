<?php

namespace App\Filament\Resources\OfficeVisits\Pages;

use App\Filament\Resources\OfficeVisits\OfficeVisitsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficeVisits extends EditRecord
{
    protected static string $resource = OfficeVisitsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
