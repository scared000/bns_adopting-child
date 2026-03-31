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

    // Pagination state — 5 rows per page for each tab
    public int $childrenPage = 1;
    public int $visitsPage   = 1;

    protected const PER_PAGE = 5;

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

    // ── Tab ────────────────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Children pagination ────────────────────────────────────────────────────

    public function childrenNextPage(): void
    {
        $total = $this->record->childAssignments()->count();
        if ($this->childrenPage < ceil($total / self::PER_PAGE)) {
            $this->childrenPage++;
        }
    }

    public function childrenPrevPage(): void
    {
        if ($this->childrenPage > 1) {
            $this->childrenPage--;
        }
    }

    public function childrenGoToPage(int $page): void
    {
        $this->childrenPage = $page;
    }

    // ── Visits pagination ──────────────────────────────────────────────────────

    public function visitsNextPage(): void
    {
        $total = $this->record->officeVisits()->count();
        if ($this->visitsPage < ceil($total / self::PER_PAGE)) {
            $this->visitsPage++;
        }
    }

    public function visitsPrevPage(): void
    {
        if ($this->visitsPage > 1) {
            $this->visitsPage--;
        }
    }

    public function visitsGoToPage(int $page): void
    {
        $this->visitsPage = $page;
    }

    // ── Unassign ───────────────────────────────────────────────────────────────

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

        $remaining = $this->record->childAssignments()->count();
        $maxPage = max(1, (int) ceil($remaining / self::PER_PAGE));
        if ($this->childrenPage > $maxPage) {
            $this->childrenPage = $maxPage;
        }

        Notification::make()
            ->title('Child Unassigned')
            ->body("{$childName} has been unassigned from this BNS.")
            ->success()
            ->send();
    }
}
