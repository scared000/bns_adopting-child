<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists;

use App\Filament\Resources\AdoptedChildren\Infolists\Actions\RecordVisitAction;
use App\Filament\Resources\AdoptedChildren\Infolists\Sections\{
    AssignmentSummarySection,
    ChildInformationSection,
    FamilyMembersSection,
    FamilyStatusSection,
    GuardianInformationSection,
    VisitHistorySection,
};
use App\Livewire\ChildImmunizationTable;
use Filament\Schemas\Components\{Livewire, Tabs};
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

final class AdoptedChildInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->contained(false)
                ->columnSpanFull()
                ->tabs([
                    self::childDetailsTab(),
                    self::assignmentVisitsTab(),
                    self::immunizationTab(),
                    self::familyProfileTab(),
                ]),
        ]);
    }

    private static function childDetailsTab(): Tab
    {
        return Tab::make('Child Details')
            ->icon('heroicon-o-user-circle')
            ->schema([ChildInformationSection::make()]);
    }

    private static function assignmentVisitsTab(): Tab
    {
        return Tab::make('Assignment & Visits')
            ->icon('heroicon-o-user-plus')
            ->badge(fn ($record) => $record->officeVisits()->count())
            ->schema([
                AssignmentSummarySection::make(),
                RecordVisitAction::make(),
                VisitHistorySection::make(),
            ]);
    }

    private static function immunizationTab(): Tab
    {
        return Tab::make('Immunization Records')
            ->icon('heroicon-o-shield-check')
            ->badge(fn ($record) => $record->immunizations()->count())
            ->schema([
                Livewire::make(ChildImmunizationTable::class)
                    ->key(fn ($record) => 'imm-' . $record->id)
                    ->data(fn ($record) => ['childId' => $record->id]),
            ]);
    }

    private static function familyProfileTab(): Tab
    {
        return Tab::make('Family Profile')
            ->icon('heroicon-o-user-group')
            ->schema([
                GuardianInformationSection::make(),
                FamilyMembersSection::make(),
                FamilyStatusSection::make(),
            ]);
    }
}
