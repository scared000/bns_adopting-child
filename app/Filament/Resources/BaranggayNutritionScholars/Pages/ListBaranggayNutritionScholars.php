<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Pages;

use App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBaranggayNutritionScholars extends ListRecords
{
    protected static string $resource = BaranggayNutritionScholarsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
