<?php

namespace App\Filament\Resources\OfficeChildAssigns\Pages;

use App\Filament\Resources\OfficeChildAssigns\OfficeChildAssignResource;
use App\Models\OfficeChildAssign;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficeChildAssign extends CreateRecord
{
    protected static string $resource = OfficeChildAssignResource::class;
    protected string $view = 'filament.pages.OfficeChildAssign.create-office-child-assign';

    public function getHeading(): string
    {
        return '';
    }
    protected function handleRecordCreation(array $data): OfficeChildAssign
    {
        $childIds = $data['adopted_id'] ?? [];
        if (empty($childIds)) {
            Notification::make()
                ->title('Please select at least one child.')
                ->danger()
                ->send();

            $this->halt();
        }

        $firstId  = array_shift($childIds);
        $record = OfficeChildAssign::create([
            'bns_id'        => $data['bns_id'],
            'adopted_id'    => $firstId,
            'office_id'     => $data['office_id'],
            'assigned_date' => $data['assigned_date'],
        ]);

        foreach ($childIds as $childId) {
            OfficeChildAssign::create([
                'bns_id'        => $data['bns_id'],
                'adopted_id'    => $childId,
                'office_id'     => $data['office_id'],
                'assigned_date' => $data['assigned_date'],
            ]);
        }

        $total = count($childIds) + 1;

        Notification::make()
            ->title("{$total} " . str('child')->plural($total) . ' assigned successfully')
            ->success()
            ->send();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
