<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\BaranggayNutritionScholars;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function getRedirectUrl(): string
    {
        $user = $this->record;
        // If the newly created user is a BNS and has no profile yet,
        // redirect directly to the BNS Profile create form.
        if ($user->isBns() && ! $user->bnsProfile()->exists()) {
            return route('filament.admin.resources.bns-profiles.create') .
                '?user_id=' . $user->id;
        }

        return $this->getResource()::getUrl('index');
    }
    protected function afterCreate(): void
    {
        $user = $this->record;
        if (! $user->hasRole('bns')) {
            return;
        }
        BaranggayNutritionScholars::create([
            'user_id'         => $user->id,
            'firstname'       => $user->firstname,
            'lastname'        => $user->lastname,
            'middlename'      => $user->middlename,
            'suffix'          => $user->suffix,
            'profile_path'    => $user->profile_path,
            'barangay_id'     => $user->barangay_id,
            'barangay_code'   => $user->barangay_code,
            'barangay_name'   => $user->barangay_name,
            'municipality_id' => $user->municipality_id,
            'purok'           => $user->purok,
        ]);
    }
}
