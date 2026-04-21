<?php

namespace App\Notifications;

use App\Models\OfficeChildVisit;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ItemDistributedNotification extends Notification
{
    public function __construct(protected OfficeChildVisit $distribution) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $child    = $this->distribution->child;
        $office   = $this->distribution->office;
        $items    = $this->distribution->visitItems->count();
        $itemList = $this->distribution->visitItems
            ->pluck('Item_description')
            ->implode(', ');

        return FilamentNotification::make()
            ->title('Items Distributed')
            ->body(
                ($office?->office ?? 'An office') . ' distributed ' .
                $items . ' ' . str('item')->plural($items) .
                ' (' . $itemList . ')' .
                ' to ' . ($child->firstname . ' ' . $child->lastname) . '.'
            )
            ->icon('heroicon-o-gift')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('View Distribution')
                    ->url(route('filament.admin.resources.office-distributions.index'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
