<?php

namespace App\Filament\Resources\OfficeVisits\Pages;

use App\Filament\Resources\OfficeVisits\OfficeVisitsResource;
use App\Filament\Resources\OfficeVisits\Schemas\OfficeVisitsForm;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return OfficeVisitsForm::resolveStatus($data);
    }
}
