<?php

namespace App\Filament\Resources\BnsProfiles\Pages;

use App\Filament\Resources\BnsProfiles\BnsProfileResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBnsProfile extends CreateRecord
{
    protected static string $resource = BnsProfileResource::class;
    protected function fillForm(): void
    {
        parent::fillForm();

        $userId = request()->query('user_id')
            ?? session('redirect_to_bns_profile');

        if ($userId) {
            $this->form->fill(['user_id' => (int) $userId]);
        }
    }
    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        // Clear the pending redirect session key
        session()->forget('redirect_to_bns_profile');

        Notification::make()
            ->title('BNS Profile created!')
            ->body("Profile for {$this->record->full_name} has been saved.")
            ->success()
            ->send();
    }

    /**
     * Pre-fill user_id when redirected from UserResource after BNS user creation.
     */
//    protected function fillForm(): void
//    {
//        parent::fillForm();
//
//        if ($userId = session('redirect_to_bns_profile')) {
//            $this->form->fill(['user_id' => $userId]);
//        }
//    }
}
