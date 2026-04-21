<?php

// app/Notifications/OfficeChildAssignedNotification.php
namespace App\Notifications;

use App\Models\OfficeChildAssign;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class OfficeChildAssignedNotification extends Notification
{
    public function __construct(protected OfficeChildAssign $assignment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $child = $this->assignment->child;
        $bns   = $this->assignment->bns;

        return FilamentNotification::make()
            ->title('New Child Assigned to Your Office')
            ->body(
                ($child->firstname . ' ' . $child->lastname) .
                ' has been assigned to your office' .
                ($bns ? ', under BNS ' . $bns->firstname . ' ' . $bns->lastname : '') . '.'
            )
            ->icon('heroicon-o-building-office')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('View Child')
                    ->url(route('filament.admin.resources.adopted-children.view', $child))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
