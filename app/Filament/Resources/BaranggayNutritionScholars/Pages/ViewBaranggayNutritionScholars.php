<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Pages;

use App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBaranggayNutritionScholars extends ViewRecord
{
    protected static string $resource = BaranggayNutritionScholarsResource::class;
    protected string $view = 'filament.pages.BaranggayNutritionScholarsProfiles.view-bns-profile';

    public string $activeTab = 'children';

    public function getHeading(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
}
