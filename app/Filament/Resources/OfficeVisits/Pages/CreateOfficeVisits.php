<?php

namespace App\Filament\Resources\OfficeVisits\Pages;

use App\Filament\Resources\OfficeVisits\OfficeVisitsResource;
use App\Filament\Resources\OfficeVisits\Schemas\OfficeVisitsForm;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficeVisits extends CreateRecord
{
    protected static string $resource = OfficeVisitsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return OfficeVisitsForm::resolveStatus($data);
    }
}
