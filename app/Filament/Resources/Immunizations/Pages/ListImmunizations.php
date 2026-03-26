<?php

namespace App\Filament\Resources\Immunizations\Pages;

use App\Filament\Resources\Immunizations\ImmunizationsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImmunizations extends ListRecords
{
    protected static string $resource = ImmunizationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
