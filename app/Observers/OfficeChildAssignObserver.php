<?php
// app/Observers/OfficeChildAssignObserver.php
namespace App\Observers;

use App\Models\OfficeChildAssign;
use App\Models\User;
use App\Notifications\ChildAssignedNotification;
use App\Notifications\OfficeChildAssignedNotification;

class OfficeChildAssignObserver
{
    public function created(OfficeChildAssign $assignment): void
    {
        // 1. Notify the BNS user
        $bnsUser = $assignment->bns?->user;

        if ($bnsUser) {
            $bnsUser->notify(new ChildAssignedNotification($assignment));
        }

        // 2. Notify the officeDistributor user belonging to the assigned office
        $officeUsers = User::role('office')
            ->where('office_id', $assignment->office_id)
            ->get();

        foreach ($officeUsers as $officeUser) {
            $officeUser->notify(new OfficeChildAssignedNotification($assignment));
        }
    }
}
