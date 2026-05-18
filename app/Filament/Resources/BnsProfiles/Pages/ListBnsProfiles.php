<?php

namespace App\Filament\Resources\BnsProfiles\Pages;

use App\Filament\Resources\BnsProfiles\BnsProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBnsProfiles extends ListRecords
{
    protected static string $resource = BnsProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
