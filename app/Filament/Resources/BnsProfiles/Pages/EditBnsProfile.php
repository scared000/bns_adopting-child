<?php

namespace App\Filament\Resources\BnsProfiles\Pages;

use App\Filament\Resources\BnsProfiles\BnsProfileResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBnsProfile extends EditRecord
{
    protected static string $resource = BnsProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['date_started'])) {
            $data['years_of_service'] = (int) \Carbon\Carbon::parse($data['date_started'])
                ->diffInYears(now());
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
