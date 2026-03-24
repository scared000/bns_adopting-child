<?php

namespace App\Filament\Resources\OfficeChildAssigns\Pages;

use App\Filament\Resources\OfficeChildAssigns\OfficeChildAssignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficeChildAssign extends EditRecord
{
    protected static string $resource = OfficeChildAssignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
