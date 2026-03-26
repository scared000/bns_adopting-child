<?php

namespace App\Filament\Pages;

use App\Models\AdoptedChild;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ChildVisitDetail extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.child-history-log.child-visit-detail';
    #[Url]
    public int $childId = 0;

    public string $activeTab = 'history';

    public function getChildProperty(): AdoptedChild
    {
        return AdoptedChild::with([
            'officeVisits.visitItems',
            'officeVisits.bns',
            'officeAssignments.bns',
            'barangay',
            'municipality.province',
        ])->findOrFail($this->childId);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
}
