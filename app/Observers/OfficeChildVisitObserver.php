<?php
namespace App\Observers;

use App\Models\OfficeChildVisit;
use App\Models\User;
use App\Notifications\ChildVisitNotification;

class OfficeChildVisitObserver
{
    public function created(OfficeChildVisit $visit): void
    {
        if ($visit->visit_type === 'office_distribution') {
            return;
        }

        $admins = User::role(['admin', 'super_admin'])->get();

        foreach ($admins as $admin) {
            $admin->notify(new ChildVisitNotification($visit));
        }
    }
}
