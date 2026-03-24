<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
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
                    ->label(new HtmlString('<span style="font-weight:750;">Profile</span>'))
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?' . http_build_query([
                            'name' => "{$record->firstname} {$record->lastname}",
                            'background' => '6366f1',
                            'color' => 'fff',
                            'size' => '128',
                            'bold' => 'true',
                        ])
                    ),

                // Column 2: Name and Age
                Group::make([
                    TextEntry::make('name_display')
                        ->label(new HtmlString('<span style="font-weight:750;">Full Name</span>'))
                        ->getStateUsing(fn ($record) => trim("{$record->firstname} {$record->middlename} {$record->lastname} {$record->suffix}")),

                    TextEntry::make('age_display')
                        ->label(new HtmlString('<span style="font-weight:750;">Age</span>'))
                        ->getStateUsing(fn ($record) => $record->birthdate
                            ? \Carbon\Carbon::parse($record->birthdate)->diff(now())->y . ' yrs old'
                            : '—'),
                ]),

                // Column 3: DOB and Sex
                Group::make([
                    TextEntry::make('birthdate')
                        ->label(new HtmlString('<span style="font-weight:750;">Date of Birth</span>'))
                        ->date('F d, Y'),

                    TextEntry::make('sex')
                        ->label(new HtmlString('<span style="font-weight:750;">Sex</span>'))
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),
                ]),

                // Row 2
                TextEntry::make('height_cm')
                    ->label(new HtmlString('<span style="font-weight:750;">Height</span>'))
                    ->suffix(' cm'),

                TextEntry::make('weight_kg')
                    ->label(new HtmlString('<span style="font-weight:750;">Weight</span>'))
                    ->suffix(' kg'),

                TextEntry::make('birthplace')
                    ->label(new HtmlString('<span style="font-weight:750;">Place of Birth</span>')),

                TextEntry::make('nutritional_status')
                    ->label(new HtmlString('<span style="font-weight:750;">Nutritional Status</span>'))
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->columnSpan(1),

                TextEntry::make('address')
                    ->label(new HtmlString('<span style="font-weight:750;">Address</span>'))
                    ->columnSpan(2)
                    ->getStateUsing(function ($record) {
                        return collect([
                            $record->purok,
                            $record->barangay?->brgyDesc,
                            $record->municipality?->citymunDesc,
                            $record->municipality?->province?->provDesc
                        ])->filter()->implode(', ');
                    }),
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
                            ->label(new HtmlString('<span style="font-weight:750;">Full Name</span>'))
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->motherProfile?->firstname ?? '') . ' ' .
                                ($record->motherProfile?->middlename ?? '') . ' ' .
                                ($record->motherProfile?->lastname ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('motherProfile.birthdate')
                            ->label(new HtmlString('<span style="font-weight:750;">Birth Date</span>'))
                            ->formatStateUsing(fn ($state) =>
                            $state
                                ? \Carbon\Carbon::parse($state)->format('F d, Y')
                                : '—'
                            ),
                        TextEntry::make('motherProfile.relation')
                            ->label(new HtmlString('<span style="font-weight:750;">Relationship</span>'))
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'biological_mother' => 'Biological Mother',
                                'adoptive_mother' => 'Adoptive Mother',
                                'grandmother' => 'Grandmother',
                                'aunt' => 'Aunt',
                                'older_sibling' => 'Older Sibling',
                                'legal_guardian' => 'Legal Guardian',
                                'foster_parent' => 'Foster Parent',
                                'court_appointed' => 'Court-Appointed Guardian',
                                'family_friend' => 'Family Friend',
                                default => ucwords(str_replace('_', ' ', $state ?? '—')),
                            }),
                        TextEntry::make('motherProfile.occupation')
                            ->label(new HtmlString('<span style="font-weight:750;">Occupation</span>')),
                        TextEntry::make('motherProfile.educational_attainment')
                            ->label(new HtmlString('<span style="font-weight:750;">Education</span>'))
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'no_formal_education' => 'No Formal Education',
                                'elementary_undergraduate' => 'Elementary Undergraduate',
                                'elementary_graduate' => 'Elementary Graduate',
                                'jhs_undergraduate' => 'Junior High School Undergraduate',
                                'jhs_graduate' => 'Junior High School Graduate (Grade 10)',
                                'shs_undergraduate' => 'Senior High School Undergraduate',
                                'shs_graduate' => 'Senior High School Graduate (Grade 12)',
                                'vocational' => 'Vocational / Technical Course',
                                'college_undergraduate' => 'College Undergraduate',
                                'college_graduate' => 'College Graduate',
                                'masters' => "Master's Degree",
                                'doctorate' => 'Doctorate Degree',
                                default => ucwords(str_replace('_', ' ', $state ?? '—')),
                            }),
                    ]),

                Section::make('Father')
                    ->schema([
                        TextEntry::make('fatherProfile.firstname')
                            ->label(new HtmlString('<span style="font-weight:750;">Full Name</span>'))
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->fatherProfile?->firstname ?? '') . ' ' .
                                ($record->fatherProfile?->middlename ?? '') . ' ' .
                                ($record->fatherProfile?->lastname ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('fatherProfile.birthdate')
                            ->label(new HtmlString('<span style="font-weight:750;">Birth Date</span>'))
                            ->formatStateUsing(fn ($state) =>
                            $state
                                ? \Carbon\Carbon::parse($state)->format('F d, Y')
                                : '—'
                            ),
                        TextEntry::make('fatherProfile.relation')
                            ->label(new HtmlString('<span style="font-weight:750;">Relationship</span>'))
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'biological_mother' => 'Biological Mother',
                                'adoptive_mother' => 'Adoptive Mother',
                                'grandmother' => 'Grandmother',
                                'aunt' => 'Aunt',
                                'older_sibling' => 'Older Sibling',
                                'legal_guardian' => 'Legal Guardian',
                                'foster_parent' => 'Foster Parent',
                                'court_appointed' => 'Court-Appointed Guardian',
                                'family_friend' => 'Family Friend',
                                default => ucwords(str_replace('_', ' ', $state ?? '—')),
                            }),

                        TextEntry::make('fatherProfile.occupation')
                            ->label(new HtmlString('<span style="font-weight:750;">Occupation</span>')),

                        TextEntry::make('fatherProfile.educational_attainment')
                            ->label('Education')
                            ->label(new HtmlString('<span style="font-weight:750;">Education</span>'))
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'no_formal_education' => 'No Formal Education',
                                'elementary_undergraduate' => 'Elementary Undergraduate',
                                'elementary_graduate' => 'Elementary Graduate',
                                'jhs_undergraduate' => 'Junior High School Undergraduate',
                                'jhs_graduate' => 'Junior High School Graduate (Grade 10)',
                                'shs_undergraduate' => 'Senior High School Undergraduate',
                                'shs_graduate' => 'Senior High School Graduate (Grade 12)',
                                'vocational' => 'Vocational / Technical Course',
                                'college_undergraduate' => 'College Undergraduate',
                                'college_graduate' => 'College Graduate',
                                'masters' => "Master's Degree",
                                'doctorate' => 'Doctorate Degree',
                                default => ucwords(str_replace('_', ' ', $state ?? '—')),
                            }),
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
                    ->getStateUsing(fn ($record) => $record)
                    ->formatStateUsing(fn ($state): HtmlString => (function () use ($state) {
                        $members = $state->familyMembers;

                        if ($members->isEmpty()) {
                            return new HtmlString('
                            <div style="
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                padding: 32px 16px;
                                border-radius: 10px;
                                border: 2px dashed #e5e7eb;
                                background: #f9fafb;
                                text-align: center;
                            ">
                                <div style="
                                    width: 48px; height: 48px;
                                    border-radius: 50%;
                                    background: #f3f4f6;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    margin-bottom: 12px;
                                ">
                                    <svg style="width:24px;height:24px;" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p style="margin:0;font-size:14px;font-weight:600;color:#374151;">No Family Members</p>
                                <p style="margin:4px 0 0;font-size:13px;color:#9ca3af;">No family members have been recorded for this child yet.</p>
                            </div>
                        ');
                        }

                        $rows = '';
                        foreach ($members as $member) {
                            $name   = $member->fam_member_fullname ?? '—';
                            $weight = $member->fam_member_actual_weight
                                ? $member->fam_member_actual_weight . ' kg'
                                : '—';
                            $status = match ($member->fam_member_nutrition_status) {
                                'normal'      => 'Normal',
                                'underweight' => 'Underweight',
                                'overweight'  => 'Overweight',
                                'server_uw'   => 'Severely UW',
                                default       => '—',
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
                    ->label(new HtmlString('<span style="font-weight:750;">Civil Status</span>'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'civil' => 'Civil',
                        'church' => 'Church / Religious',
                        'common_law' => 'Common Law',
                        'none' => 'N/A',
                        default => ucwords(str_replace('_', ' ', $state ?? '—')),
                    }),
                TextEntry::make('familyStatus.type_of_marriage')
                    ->label(new HtmlString('<span style="font-weight:750;">Type of Marriage</span>'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'single' => 'Single',
                        'married' => 'Married',
                        'widowed' => 'Widowed',
                        'separated' => 'Separated',
                        'cohabiting' => 'Live-in / Cohabiting',
                        default => ucwords(str_replace('_', ' ', $state ?? '—')),
                    }),
                TextEntry::make('familyStatus.monthly_income')
                    ->label(new HtmlString('<span style="font-weight:750;">Monthly Income</span>'))
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
                    ->label(new HtmlString('<span style="font-weight:750;">Source of Income</span>'))
                    ->formatStateUsing(fn ($record) =>
                        $record->familyStatus->first()?->source_income ?? '—'
                    ),
                TextEntry::make('familyStatus.phil_member')
                    ->label(new HtmlString('<span style="font-weight:750;">PhilHealth Member?</span>'))
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->phil_member) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.family_plan_method')
                    ->label(new HtmlString('<span style="font-weight:750;">Family Planning</span>'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        'natural' => 'Natural',
                        'pills' => 'Pills',
                        'condom' => 'Condom',
                        'iud' => 'IUD',
                        'ligation' => 'Ligation',
                        'vasectomy' => 'Vasectomy',
                        'none' => 'None',
                        default => ucwords(str_replace('_', ' ', $state ?? '—')),
                    }),
                TextEntry::make('familyStatus.have_electricity')
                    ->label(new HtmlString('<span style="font-weight:750;">Has Electricity?</span>'))
                    ->formatStateUsing(fn ($record) =>
                    match ($record->familyStatus->first()?->have_electricity) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    }
                    ),
                TextEntry::make('familyStatus.water_source')
                    ->label(new HtmlString('<span style="font-weight:750;">Water Source</span>'))
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
                    ->label(new HtmlString('<span style="font-weight:750;">Toilet Facility</span>'))
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
                    ->label(new HtmlString('<span style="font-weight:750;">Roofing</span>'))
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
                    ->label(new HtmlString('<span style="font-weight:750;">Wall Material</span>'))
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
                    ->label(new HtmlString('<span style="font-weight:750;">Flooring</span>'))
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
