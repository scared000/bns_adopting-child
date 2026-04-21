<?php

namespace App\Filament\Resources\OfficeDistributions\Pages;

use App\Filament\Resources\OfficeDistributions\OfficeDistributionResource;
use App\Models\User;
use App\Notifications\ItemDistributedNotification;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficeDistributions extends ListRecords
{
    protected static string $resource = OfficeDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function ($record) {
                    $distribution = $record->load('visitItems', 'child', 'office');
                    foreach (User::role(['admin', 'super_admin'])->get() as $admin) {
                        $admin->notify(new ItemDistributedNotification($distribution));
                    }
                }),
        ];
    }
}
