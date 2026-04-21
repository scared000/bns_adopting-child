<?php
// app/Observers/OfficeChildVisitObserver.php
namespace App\Observers;

use App\Models\OfficeChildVisit;
use App\Models\User;
use App\Notifications\ChildVisitNotification;
use App\Notifications\ItemDistributedNotification;

class OfficeChildVisitObserver
{
    public function created(OfficeChildVisit $visit): void
    {
        $admins = User::role(['admin', 'super_admin'])->get();

        if ($visit->visit_type === 'office_distribution') {
            foreach ($admins as $admin) {
                $admin->notify(new ItemDistributedNotification($visit));
            }
        } else {
            foreach ($admins as $admin) {
                $admin->notify(new ChildVisitNotification($visit));
            }
        }
    }
}
