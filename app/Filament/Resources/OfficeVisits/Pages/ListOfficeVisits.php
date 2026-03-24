<?php

namespace App\Filament\Resources\OfficeVisits\Pages;

use App\Filament\Resources\OfficeVisits\OfficeVisitsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficeVisits extends ListRecords
{
    protected static string $resource = OfficeVisitsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
