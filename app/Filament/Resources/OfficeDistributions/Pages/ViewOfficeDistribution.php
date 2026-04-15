<?php

namespace App\Filament\Resources\OfficeDistributions\Pages;

use App\Filament\Resources\OfficeDistributions\OfficeDistributionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOfficeDistribution extends ViewRecord
{
    protected static string $resource = OfficeDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
