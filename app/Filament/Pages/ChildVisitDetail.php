<?php

namespace App\Filament\Pages;

use App\Models\AdoptedChild;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Spatie\Activitylog\Models\Activity;

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
    public function getActivitiesProperty(): Collection
    {
        $childLogs = Activity::query()
            ->where('subject_type', AdoptedChild::class)
            ->where('subject_id', $this->childId)
            ->latest()
            ->get();

        $visitIds = $this->child->officeVisits->pluck('id');

        $visitLogs = Activity::query()
            ->where('subject_type', \App\Models\OfficeChildVisit::class)
            ->whereIn('subject_id', $visitIds)
            ->latest()
            ->get();

        return $childLogs
            ->merge($visitLogs)
            ->sortByDesc('created_at')
            ->values();
    }
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
}
