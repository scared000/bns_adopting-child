<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AdoptedChildInfolist
{
    //Entry point
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::sectionChildInformation(),
            self::sectionGuardianInformation(),
            self::sectionFamilyMembers(),
            self::sectionFamilyStatus(),
        ]);
    }

    //Child Information
    private static function sectionChildInformation(): Section
    {
        return Section::make('👤 Child Information')
            ->columns(3)
            ->schema([
                ImageEntry::make('profile_path')
                    ->label('Profile')
                    ->circular()
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?' . http_build_query([
                            'name' => $record->firstname . ' ' . $record->lastname,
                            'background' => '6366f1',
                            'color' => 'fff',
                            'size' => '128',
                            'bold' => 'true',
                            'rounded' => 'true',
                        ])
                    )
                    ->columnSpanFull(),

                TextEntry::make('firstname')
                    ->label('Full Name')
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) =>
                    trim(
                        $record->firstname . ' ' .
                        ($record->middlename ?? '') . ' ' .
                        $record->lastname . ' ' .
                        ($record->suffix ?? '')
                    )
                    ),

                TextEntry::make('birthdate')
                    ->label('Date of Birth')
                    ->formatStateUsing(fn ($state) =>
                    $state
                        ? \Carbon\Carbon::parse($state)->format('F d, Y')
                        : '—'
                    ),

                TextEntry::make('birthdate')
                    ->label('Age')
                    ->formatStateUsing(fn ($record) => (function () use ($record) {
                        if (!$record->birthdate) return '—';
                        $age    = \Carbon\Carbon::parse($record->birthdate)->diff(now());
                        $months = ($age->y * 12) + $age->m;
                        return $age->y >= 5
                            ? $age->y . ' yrs old'
                            : $age->y . 'y ' . $age->m . 'm (' . $months . ' mos)';
                    })()),

                TextEntry::make('sex')
                    ->label('Sex')
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),

                TextEntry::make('birthplace')
                    ->label('Place of Birth'),

                TextEntry::make('height_cm')
                    ->label('Height')
                    ->suffix(' cm'),

                TextEntry::make('weight_kg')
                    ->label('Weight')
                    ->suffix(' kg'),

                TextEntry::make('nutritional_status')
                    ->label('Nutritional Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ?? 'Incomplete Data')
                    ->color(fn (string $state): string => self::statusColor($state)),
            ]);
    }

    //Guardian Information
    private static function sectionGuardianInformation(): Section
    {
        return Section::make('👨‍👩‍👦 Guardian Information')
            ->columns(2)
            ->schema([
                Section::make('Mother')
                    ->schema([
                        TextEntry::make('motherProfile.firstname')
                            ->label('Full Name')
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->motherProfile?->firstname ?? '') . ' ' .
                                ($record->motherProfile?->middlename ?? '') . ' ' .
                                ($record->motherProfile?->lastname ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('motherProfile.birthdate')
                            ->label('Birth Date')
                            ->formatStateUsing(fn ($state) =>
                            $state
                                ? \Carbon\Carbon::parse($state)->format('F d, Y')
                                : '—'
                            ),
                        TextEntry::make('motherProfile.relation')
                            ->label('Relationship'),
                        TextEntry::make('motherProfile.occupation')
                            ->label('Occupation'),
                        TextEntry::make('motherProfile.educational_attainment')
                            ->label('Education'),
                    ]),

                Section::make('Father')
                    ->schema([
                        TextEntry::make('fatherProfile.firstname')
                            ->label('Full Name')
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->fatherProfile?->firstname ?? '') . ' ' .
                                ($record->fatherProfile?->middlename ?? '') . ' ' .
                                ($record->fatherProfile?->lastname ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('fatherProfile.birthdate')
                            ->label('Birth Date')
                            ->formatStateUsing(fn ($state) =>
                            $state
                                ? \Carbon\Carbon::parse($state)->format('F d, Y')
                                : '—'
                            ),
                        TextEntry::make('fatherProfile.relation')
                            ->label('Relationship'),
                        TextEntry::make('fatherProfile.occupation')
                            ->label('Occupation'),
                        TextEntry::make('fatherProfile.educational_attainment')
                            ->label('Education'),
                    ]),
            ]);
    }

    //Family Members
    private static function sectionFamilyMembers(): Section
    {
        return Section::make('👨‍👩‍👧‍👦 Family Members')
            ->schema([
                TextEntry::make('familyMembers')
                    ->label('')
                    ->hiddenLabel()
                    ->formatStateUsing(fn ($record): HtmlString => (function () use ($record) {
                        $members = $record->familyMembers;

                        if ($members->isEmpty()) {
                            return new HtmlString(
                                '<span style="font-size:13px;color:#9ca3af;font-style:italic;">No family members recorded.</span>'
                            );
                        }

                        $rows = '';
                        foreach ($members as $member) {
                            $name   = $member->fam_member_fullname ?? '—';
                            $weight = $member->fam_member_actual_weight
                                ? $member->fam_member_actual_weight . ' kg'
                                : '—';
                            $status = match ($member->fam_member_nutrition_status) {
                                'normal' => 'Normal',
                                'underweight' => 'Underweight',
                                'overweight' => 'Overweight',
                                'server_uw' => 'Severely UW',
                                default => '—',
                            };

                            $rows .= "
                                <tr style=\"border-bottom:1px solid #e5e7eb;\">
                                    <td style=\"padding:8px 0;font-size:14px;width:33%;\">{$name}</td>
                                    <td style=\"padding:8px 0;font-size:14px;width:33%;\">{$weight}</td>
                                    <td style=\"padding:8px 0;font-size:14px;width:33%;\">{$status}</td>
                                </tr>
                            ";
                        }

                        return new HtmlString("
                            <table style=\"width:100%;table-layout:fixed;border-collapse:collapse;\">
                                <thead>
                                    <tr style=\"border-bottom:2px solid #d1d5db;\">
                                        <th style=\"padding-bottom:8px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;width:33%;\">Full Name</th>
                                        <th style=\"padding-bottom:8px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;width:33%;\">Weight</th>
                                        <th style=\"padding-bottom:8px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;width:33%;\">Nutritional Status</th>
                                    </tr>
                                </thead>
                                <tbody>{$rows}</tbody>
                            </table>
                        ");
                    })()),
            ]);
    }

    //Family Status
    private static function sectionFamilyStatus(): Section
    {
        return Section::make('🏠 Family Status')
            ->columns(3)
            ->schema([
                TextEntry::make('familyStatus.status')
                    ->label('Civil Status')
                    ->formatStateUsing(fn ($record) =>
                        $record->familyStatus->first()?->status ?? '—'
                    ),
                TextEntry::make('familyStatus.type_of_marriage')
                    ->label('Type of Marriage')
                    ->formatStateUsing(fn ($record) =>
                        $record->familyStatus->first()?->type_of_marriage ?? '—'
                    ),
                TextEntry::make('familyStatus.monthly_income')
                    ->label('Monthly Income')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->monthly_income) {
                        'below_5000' => 'Below ₱5,000',
                        '5000-9999' => '₱5,000 - ₱9,999',
                        '10000-14999' => '₱10,000 - ₱14,999',
                        '15000-19999' => '₱15,000 - ₱19,999',
                        '20000-above' => '₱20,000 and above',
                        default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.source_income')
                    ->label('Source of Income')
                    ->formatStateUsing(fn ($record) =>
                        $record->familyStatus->first()?->source_income ?? '—'
                    ),
                TextEntry::make('familyStatus.phil_member')
                    ->label('PhilHealth Member?')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->phil_member) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.family_plan_method')
                    ->label('Family Planning')
                    ->formatStateUsing(fn ($record) =>
                        $record->familyStatus->first()?->family_plan_method ?? '—'
                    ),
                TextEntry::make('familyStatus.have_electricity')
                    ->label('Has Electricity?')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->have_electricity) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.water_source')
                    ->label('Water Source')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->water_source) {
                        'tap' => 'Tap / Piped Water',
                        'well' => 'Deep Well',
                        'spring' => 'Spring',
                        'river' => 'River / Stream',
                        'rain' => 'Rainwater',
                        'delivered' => 'Delivered Water',
                        default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.toilet_facility')
                    ->label('Toilet Facility')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->toilet_facility) {
                        'flush' => 'Water-sealed / Flush',
                        'pit' => 'Pit Latrine',
                        'open' => 'Open Defecation',
                        'shared' => 'Shared Toilet',
                        default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.roofing')
                    ->label('Roofing')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->roofing) {
                        'galvanized' => 'Galvanized Iron',
                        'concrete' => 'Concrete',
                        'nipa' => 'Nipa / Cogon',
                        'wood' => 'Wood',
                        default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.walls')
                    ->label('Wall Material')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->walls) {
                        'concrete' => 'Concrete / Hollow Blocks',
                        'wood' => 'Wood',
                        'bamboo' => 'Bamboo',
                        'mixed' => 'Mixed Materials',
                        default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.flooring')
                    ->label('Flooring')
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->flooring) {
                        'concrete' => 'Concrete',
                        'wood' => 'Wood',
                        'earth' => 'Earth / Soil',
                        'tile' => 'Tile',
                        default => '—',
                    }
                    ),
            ]);
    }

    //badge color map
    private static function statusColor(string $state): string
    {
        return match (true) {
            str_contains($state, 'SUW') => 'danger',
            str_contains($state, 'SST') => 'danger',
            str_contains($state, 'Wasted') => 'danger',
            str_contains($state, 'At Risk') => 'danger',
            str_contains($state, 'OB') => 'danger',
            str_contains($state, 'OW') => 'info',
            str_contains($state, 'UW') => 'warning',
            str_contains($state, 'ST') => 'warning',
            str_contains($state, 'MW') => 'warning',
            str_contains($state, 'Incomplete') => 'gray',
            str_contains($state, 'Invalid') => 'danger',
            default => 'success',
        };
    }
}
