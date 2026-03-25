<?php

namespace App\Filament\Resources\OfficeVisits\Pages;

use App\Filament\Resources\OfficeVisits\OfficeVisitsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficeVisits extends CreateRecord
{
    protected static string $resource = OfficeVisitsResource::class;
    protected string $view = 'filament.pages.OfficeChildVisit.create-office-visit';

    public function getHeading(): string
    {
        return '';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
