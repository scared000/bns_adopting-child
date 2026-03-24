<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Pages;

use App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBaranggayNutritionScholars extends EditRecord
{
    protected static string $resource = BaranggayNutritionScholarsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
