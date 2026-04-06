<?php

namespace App\Filament\Pages;

use App\Models\AdoptedChild;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class ChildVisitLog extends Page
{
    use HasPageShield;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|null|\UnitEnum $navigationGroup = 'LOG';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Child Visit Log';

    protected string $view = 'filament.pages.child-history-log.child-visit-log';

    public string $search = '';
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }
    public function getChildrenProperty()
    {
        return AdoptedChild::with([
            'officeVisits.visitItems',
            'officeAssignments.bns',
        ])
            ->when($this->search, fn ($q) =>
            $q->where('firstname', 'like', "%{$this->search}%")
                ->orWhere('lastname',  'like', "%{$this->search}%")
            )
            ->get();
    }
}
