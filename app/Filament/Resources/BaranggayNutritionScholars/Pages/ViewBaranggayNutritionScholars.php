<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Pages;

use App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource;
use App\Models\OfficeChildAssign;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBaranggayNutritionScholars extends ViewRecord
{
    protected static string $resource = BaranggayNutritionScholarsResource::class;
    public string $activeTab = 'children';

    public ?int $confirmingUnassignId = null;
    public function getView(): string
    {
        return 'filament.pages.BaranggayNutritionScholarsProfiles.view-bns-profile';
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function confirmUnassign(int $assignmentId): void
    {
        $this->confirmingUnassignId = $assignmentId;
    }

    public function cancelUnassign(): void
    {
        $this->confirmingUnassignId = null;
    }


    public function unassignChild(): void
    {
        $assignment = OfficeChildAssign::find($this->confirmingUnassignId);
        if (! $assignment) {
            Notification::make()
                ->title('Assignment not found')
                ->danger()
                ->send();
            $this->confirmingUnassignId = null;
            return;
        }

        $childName = $assignment->child?->firstname . ' ' . $assignment->child?->lastname;
        $assignment->delete();
        $this->confirmingUnassignId = null;
        Notification::make()
            ->title('Child Unassigned')
            ->body("{$childName} has been unassigned from this BNS.")
            ->success()
            ->send();
    }
}
