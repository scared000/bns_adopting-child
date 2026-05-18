<?php

namespace App\Filament\Resources\BnsProfiles\Pages;

use App\Filament\Resources\BnsProfiles\BnsProfileResource;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBnsProfile extends ViewRecord
{
    protected static string $resource = BnsProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
