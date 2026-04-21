<?php

namespace App\Filament\Resources\OfficeDistributions\Pages;

use App\Filament\Resources\OfficeDistributions\OfficeDistributionResource;
use App\Models\User;
use App\Notifications\ItemDistributedNotification;
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

    protected function afterCreate(): void
    {
        try {
            $distribution = $this->record->load('visitItems', 'child', 'office');
            $admins = User::role(['admin', 'super_admin'])->get();

            foreach ($admins as $admin) {
                $admin->notify(new ItemDistributedNotification($distribution));
            }
        } catch (\Throwable $e) {
            \Log::error('ItemDistributedNotification failed: ' . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
