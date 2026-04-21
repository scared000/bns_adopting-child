<?php
// app/Notifications/ChildVisitNotification.php
namespace App\Notifications;

use App\Models\OfficeChildVisit;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ChildVisitNotification extends Notification
{
    public function __construct(protected OfficeChildVisit $visit) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $child  = $this->visit->child;
        $office = $this->visit->office;

        return FilamentNotification::make()
            ->title('Child Visit Recorded')
            ->body(
                'A visit was recorded for ' .
                ($child->firstname . ' ' . $child->lastname) .
                ($office ? ' by ' . $office->office : '') . '.'
            )
            ->icon('heroicon-o-calendar-days')
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
