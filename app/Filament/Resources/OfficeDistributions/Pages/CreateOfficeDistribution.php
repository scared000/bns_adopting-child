<?php

namespace App\Filament\Resources\OfficeDistributions\Pages;

use App\Filament\Resources\OfficeDistributions\OfficeDistributionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficeDistribution extends CreateRecord
{
    protected static string $resource = OfficeDistributionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['visit_type'] = 'office_distribution';
        $data['office_id']  = auth()->user()->office_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
