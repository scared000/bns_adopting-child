<?php

namespace App\Observers;

use App\Models\BaranggayNutritionScholars;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if (! $user->hasRole('bns')){
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

    public function updated(User $user): void
    {
        if ($user->hasRole('bns')){
            return;
        }
        BaranggayNutritionScholars::where('user_id', $user->id)
            ->update([
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

    public function deleted(User $user): void
    {
        BaranggayNutritionScholars::where('user_id', $user->id)->delete();
    }
}
