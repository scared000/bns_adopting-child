<?php
// app/Notifications/ImmunizationTakenNotification.php
namespace App\Notifications;

use App\Models\Immunizations;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ImmunizationTakenNotification extends Notification
{
    public function __construct(protected Immunizations $immunization) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $child   = $this->immunization->child;
        $vaccine = $this->immunization->vaccine_description;

        return FilamentNotification::make()
            ->title('New Immunization Recorded')
            ->body(
                "Vaccine \"{$vaccine}\" was recorded for " .
                ($child->firstname . ' ' . $child->lastname) . '.'
            )
            ->icon('heroicon-o-shield-check')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View Record')
                    ->url(route('filament.admin.resources.immunizations.index'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
