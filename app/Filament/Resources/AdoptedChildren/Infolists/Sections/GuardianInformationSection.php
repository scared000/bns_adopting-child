<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Sections;

use App\Filament\Resources\AdoptedChildren\Infolists\Concerns\HasBoldLabel;
use App\Filament\Resources\AdoptedChildren\Infolists\Enums\{EducationLabel, RelationLabel};
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

final class GuardianInformationSection
{
    use HasBoldLabel;

    public static function make(): Section
    {
        return Section::make('👨‍👩‍👦 Guardian Information')
            ->columns(2)
            ->schema([
                self::motherSubSection(),
                self::fatherSubSection(),
                self::guardianSubSection(),
            ]);
    }

    //Sub-sections

    private static function motherSubSection(): Section
    {
        return self::buildGuardianSubSection(
            title:    'Mother',
            relation: 'motherProfile',
            visible:  fn ($record) => (bool) $record->motherProfile,
            span:     fn ($record) => ($record->motherProfile && $record->fatherProfile) ? 1 : 2,
        );
    }

    private static function fatherSubSection(): Section
    {
        return self::buildGuardianSubSection(
            title:    'Father',
            relation: 'fatherProfile',
            visible:  fn ($record) => (bool) $record->fatherProfile,
            span:     fn ($record) => ($record->motherProfile && $record->fatherProfile) ? 1 : 2,
        );
    }

    private static function guardianSubSection(): Section
    {
        return self::buildGuardianSubSection(
            title:    'Guardian',
            relation: 'guardianProfile',
            visible:  fn ($record) => ! $record->motherProfile
                && ! $record->fatherProfile
                && (bool) $record->guardianProfile,
            span: 2,
        );
    }

    // DRY builder

    private static function buildGuardianSubSection(
        string           $title,
        string           $relation,
        \Closure         $visible,
        int|\Closure     $span,
    ): Section {
        return Section::make($title)
            ->visible($visible)
            ->columnSpan($span)
            ->columns(3)
            ->schema([
                TextEntry::make("{$relation}.firstname")
                    ->label(self::bold('Full Name'))
                    ->formatStateUsing(fn ($record) =>
                    trim(
                        ($record->{$relation}?->firstname  ?? '') . ' ' .
                        ($record->{$relation}?->middlename ?? '') . ' ' .
                        ($record->{$relation}?->lastname   ?? '')
                    ) ?: '—'
                    ),

                TextEntry::make("{$relation}.birthdate")
                    ->label(self::bold('Birth Date'))
                    ->formatStateUsing(fn ($state) =>
                    $state ? \Carbon\Carbon::parse($state)->format('F d, Y') : '—'
                    ),

                TextEntry::make("{$relation}.relation")
                    ->label(self::bold('Relationship'))
                    ->formatStateUsing(fn ($state) => RelationLabel::resolve($state ?? '')),

                TextEntry::make("{$relation}.occupation")
                    ->label(self::bold('Occupation'))
                    ->placeholder('—'),

                TextEntry::make("{$relation}.educational_attainment")
                    ->label(self::bold('Education'))
                    ->formatStateUsing(fn ($state) => EducationLabel::resolve($state ?? '')),
            ]);
    }
}
