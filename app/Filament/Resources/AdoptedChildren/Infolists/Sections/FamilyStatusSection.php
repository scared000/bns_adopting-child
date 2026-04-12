<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Sections;

use App\Filament\Resources\AdoptedChildren\Infolists\Concerns\HasBoldLabel;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

final class FamilyStatusSection
{
    use HasBoldLabel;

    public static function make(): Section
    {
        return Section::make('🏠 Family Status')
            ->columns(3)
            ->schema([
                self::civilStatusEntry(),
                self::marriageTypeEntry(),
                self::monthlyIncomeEntry(),
                self::sourceIncomeEntry(),
                self::philHealthEntry(),
                self::familyPlanEntry(),
                self::electricityEntry(),
                self::waterSourceEntry(),
                self::toiletFacilityEntry(),
                self::roofingEntry(),
                self::wallsEntry(),
                self::flooringEntry(),
            ]);
    }

    private static function civilStatusEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.status')
            ->label(self::bold('Civil Status'))
            ->formatStateUsing(fn ($state) => match ($state) {
                'civil'      => 'Civil',
                'church'     => 'Church / Religious',
                'common_law' => 'Common Law',
                'none'       => 'N/A',
                default      => ucwords(str_replace('_', ' ', $state ?? '—')),
            });
    }

    private static function marriageTypeEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.type_of_marriage')
            ->label(self::bold('Type of Marriage'))
            ->formatStateUsing(fn ($state) => match ($state) {
                'single'     => 'Single',
                'married'    => 'Married',
                'widowed'    => 'Widowed',
                'separated'  => 'Separated',
                'cohabiting' => 'Live-in / Cohabiting',
                default      => ucwords(str_replace('_', ' ', $state ?? '—')),
            });
    }

    private static function monthlyIncomeEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.monthly_income')
            ->label(self::bold('Monthly Income'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->monthly_income) {
                'below_5000'  => 'Below ₱5,000',
                '5000-9999'   => '₱5,000 - ₱9,999',
                '10000-14999' => '₱10,000 - ₱14,999',
                '15000-19999' => '₱15,000 - ₱19,999',
                '20000-above' => '₱20,000 and above',
                default       => '—',
            });
    }

    private static function sourceIncomeEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.source_income')
            ->label(self::bold('Source of Income'))
            ->formatStateUsing(fn ($record) => $record->familyStatus->first()?->source_income ?? '—');
    }

    private static function philHealthEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.phil_member')
            ->label(self::bold('PhilHealth Member?'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->phil_member) {
                'yes' => 'Yes', 'no' => 'No', default => '—',
            });
    }

    private static function familyPlanEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.family_plan_method')
            ->label(self::bold('Family Planning'))
            ->formatStateUsing(fn ($state) => match ($state) {
                'natural'   => 'Natural',
                'pills'     => 'Pills',
                'condom'    => 'Condom',
                'iud'       => 'IUD',
                'ligation'  => 'Ligation',
                'vasectomy' => 'Vasectomy',
                'none'      => 'None',
                default     => ucwords(str_replace('_', ' ', $state ?? '—')),
            });
    }

    private static function electricityEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.have_electricity')
            ->label(self::bold('Has Electricity?'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->have_electricity) {
                'yes' => 'Yes', 'no' => 'No', default => '—',
            });
    }

    private static function waterSourceEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.water_source')
            ->label(self::bold('Water Source'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->water_source) {
                'tap'       => 'Tap / Piped Water',
                'well'      => 'Deep Well',
                'spring'    => 'Spring',
                'river'     => 'River / Stream',
                'rain'      => 'Rainwater',
                'delivered' => 'Delivered Water',
                default     => '—',
            });
    }

    private static function toiletFacilityEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.toilet_facility')
            ->label(self::bold('Toilet Facility'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->toilet_facility) {
                'flush'  => 'Water-sealed / Flush',
                'pit'    => 'Pit Latrine',
                'open'   => 'Open Defecation',
                'shared' => 'Shared Toilet',
                default  => '—',
            });
    }

    private static function roofingEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.roofing')
            ->label(self::bold('Roofing'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->roofing) {
                'galvanized' => 'Galvanized Iron',
                'concrete'   => 'Concrete',
                'nipa'       => 'Nipa / Cogon',
                'wood'       => 'Wood',
                default      => '—',
            });
    }

    private static function wallsEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.walls')
            ->label(self::bold('Wall Material'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->walls) {
                'concrete' => 'Concrete / Hollow Blocks',
                'wood'     => 'Wood',
                'bamboo'   => 'Bamboo',
                'mixed'    => 'Mixed Materials',
                default    => '—',
            });
    }

    private static function flooringEntry(): TextEntry
    {
        return TextEntry::make('familyStatus.flooring')
            ->label(self::bold('Flooring'))
            ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->flooring) {
                'concrete' => 'Concrete',
                'wood'     => 'Wood',
                'earth'    => 'Earth / Soil',
                'tile'     => 'Tile',
                default    => '—',
            });
    }
}
