<?php
namespace App\Notifications;

use App\Models\OfficeChildAssign;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ChildAssignedNotification extends Notification
{
    public function __construct(protected OfficeChildAssign $assignment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $child  = $this->assignment->child;
        $office = $this->assignment->office;

        return FilamentNotification::make()
            ->title('New Child Assigned to You')
            ->body(
                ($child->firstname . ' ' . $child->lastname) .
                ' has been assigned to you' .
                ($office ? ' by ' . $office->office : '') . '.'
            )
            ->icon('heroicon-o-user-plus')
            ->iconColor('info')
            ->actions([
                Action::make('view')
                    ->label('View Child')
                    ->url(route('filament.admin.resources.adopted-children.view', $child))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
