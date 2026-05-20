<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Sections;

use App\Filament\Resources\AdoptedChildren\Infolists\Concerns\{HasBoldLabel, HasStatusColor};
use Filament\Infolists\Components\{ImageEntry, TextEntry};
use Filament\Schemas\Components\{Group, Section};

final class ChildInformationSection
{
    use HasBoldLabel;
    use HasStatusColor;

    public static function make(): Section
    {
        return Section::make('👤 Child Information')
            ->columns(3)
            ->schema([
                self::profileImage(),
                self::nameAgeGroup(),
                self::birthdateSexGroup(),
                self::heightEntry(),
                self::weightEntry(),
                self::birthplaceEntry(),
                self::nutritionalStatusEntry(),
                self::underlyingCauseEntry(),
                self::batchEntry(),
                self::addressEntry(),
            ]);
    }

    private static function profileImage(): ImageEntry
    {
        return ImageEntry::make('profile_path')
            ->label(self::bold('Profile'))
            ->circular()
            ->disk('public')
            ->defaultImageUrl(fn ($record) =>
                'https://ui-avatars.com/api/?' . http_build_query([
                    'name'       => "{$record->firstname} {$record->lastname}",
                    'background' => '6366f1',
                    'color'      => 'fff',
                    'size'       => '128',
                    'bold'       => 'true',
                ])
            );
    }

    private static function nameAgeGroup(): Group
    {
        return Group::make([
            TextEntry::make('name_display')
                ->label(self::bold('Full Name'))
                ->getStateUsing(fn ($record) =>
                trim("{$record->firstname} {$record->middlename} {$record->lastname} {$record->suffix}")
                ),

            TextEntry::make('age_display')
                ->label(self::bold('Age'))
                ->getStateUsing(fn ($record) => $record->birthdate
                    ? \Carbon\Carbon::parse($record->birthdate)->diff(now())->y . ' yrs old'
                    : '—'
                ),
        ]);
    }

    private static function birthdateSexGroup(): Group
    {
        return Group::make([
            TextEntry::make('birthdate')
                ->label(self::bold('Date of Birth'))
                ->date('F d, Y'),

            TextEntry::make('sex')
                ->label(self::bold('Sex'))
                ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),
        ]);
    }

    private static function heightEntry(): TextEntry
    {
        return TextEntry::make('height_cm')
            ->label(self::bold('Height'))
            ->suffix(' cm')
            ->getStateUsing(fn ($record) =>
                $record->officeVisits()->latest('visit_date')->first()?->height
                ?? $record->height_cm
            )
            ->placeholder('—');
    }

    private static function weightEntry(): TextEntry
    {
        return TextEntry::make('weight_kg')
            ->label(self::bold('Weight'))
            ->suffix(' kg')
            ->getStateUsing(fn ($record) =>
                $record->officeVisits()->latest('visit_date')->first()?->weight
                ?? $record->weight_kg
            )
            ->placeholder('—');
    }

    private static function birthplaceEntry(): TextEntry
    {
        return TextEntry::make('birthplace')
            ->label(self::bold('Place of Birth'));
    }

    private static function nutritionalStatusEntry(): TextEntry
    {
        return TextEntry::make('nutritional_status')
            ->label(self::bold('Nutritional Status'))
            ->wrap()
            ->getStateUsing(fn ($record) =>
                $record->officeVisits()->latest('visit_date')->first()?->status
                ?? $record->nutritional_status
                ?? '—'
            )
            ->color(fn (string $state): string => self::statusColor($state))
            ->columnSpan(1);
    }

    private static function underlyingCauseEntry(): TextEntry
    {
        return TextEntry::make('underlying_cause')
            ->label(self::bold('Underlying Cause'))
            ->placeholder('—')
            ->wrap()
            ->columnSpan(1);
    }

    private static function batchEntry(): TextEntry
    {
        return TextEntry::make('batch')
            ->label(self::bold('Batch'))
            ->badge()
            ->color('primary')
            ->placeholder('— Not assigned —')
            ->columnSpan(1);
    }

    private static function addressEntry(): TextEntry
    {
        return TextEntry::make('address')
            ->label(self::bold('Address'))
            ->columnSpan(2)
            ->wrap()
            ->getStateUsing(fn ($record) =>
            collect([
                $record->purok,
                $record->barangay?->brgyDesc,
                $record->municipality?->citymunDesc,
                $record->municipality?->province?->provDesc,
            ])->filter()->implode(', ')
            );
    }
}
