<?php
// app/Observers/ImmunizationsObserver.php
namespace App\Observers;

use App\Models\Immunizations;
use App\Models\User;
use App\Notifications\ImmunizationTakenNotification;

class ImmunizationsObserver
{
    public function created(Immunizations $immunization): void
    {
        $admins = User::role(['admin', 'super_admin'])->get();

        foreach ($admins as $admin) {
            $admin->notify(new ImmunizationTakenNotification($immunization));
        }
    }
}
