<?php

namespace App\Filament\Resources\OfficeDistributions\Pages;

use App\Filament\Resources\OfficeDistributions\OfficeDistributionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficeDistribution extends EditRecord
{
    protected static string $resource = OfficeDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
