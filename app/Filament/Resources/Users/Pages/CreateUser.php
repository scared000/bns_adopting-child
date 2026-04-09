<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\BaranggayNutritionScholars;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

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
