<?php

namespace App\Filament\Pages;

use App\Models\AdoptedChild;
use Filament\Pages\Page;

class ChildVisitLog extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-user-group';
    protected static string|null|\UnitEnum $navigationGroup = 'LOG';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Child Visit Log';

    protected string $view = 'filament.pages.child-visit-log';

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
