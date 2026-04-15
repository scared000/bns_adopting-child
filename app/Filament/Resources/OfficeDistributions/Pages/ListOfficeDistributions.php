<?php

namespace App\Filament\Resources\OfficeDistributions\Pages;

use App\Filament\Resources\OfficeDistributions\OfficeDistributionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficeDistributions extends ListRecords
{
    protected static string $resource = OfficeDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
