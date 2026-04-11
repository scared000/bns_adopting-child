<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists;

use App\Filament\Resources\AdoptedChildren\Tables\AdoptedChildrenTable;
use App\Filament\Resources\OfficeVisits\Schemas\OfficeVisitsForm;
use App\Helpers\NutritionalStatus;
use App\Livewire\ChildImmunizationTable;
use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use App\Models\OfficeChildVisit;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AdoptedChildInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->contained(false)
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Child Details')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            self::sectionChildInformation(),
                        ]),

                    Tab::make('Assigment & Visits')
                        ->icon('heroicon-o-user-plus')
                        ->badge(fn ($record) => $record->officeVisits()->count())
                        ->schema([
                            self::sectionAssignmentSummary(),
                            self::sectionRecordVisitAction(),
                            self::sectionVisitHistory(),
                        ]),

                    Tab::make('Immunization Records')
                        ->icon('heroicon-o-shield-check')
                        ->badge(fn ($record) => $record->immunizations()->count())
                        ->schema([
                            Livewire::make(ChildImmunizationTable::class)
                                ->key(fn ($record) => 'imm-' . $record->id)
                                ->data(fn ($record) => [
                                    'childId' => $record->id,
                                ]),
                        ]),

                    Tab::make('Family Profile')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            self::sectionGuardianInformation(),
                            self::sectionFamilyMembers(),
                            self::sectionFamilyStatus(),
                        ]),
                ])
        ]);
    }

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

                Group::make([
                    TextEntry::make('birthdate')
                        ->label(new HtmlString('<span style="font-weight:750;">Date of Birth</span>'))
                        ->date('F d, Y'),

                    TextEntry::make('sex')
                        ->label(new HtmlString('<span style="font-weight:750;">Sex</span>'))
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),
                ]),

                TextEntry::make('height_cm')
                    ->label(new HtmlString('<span style="font-weight:750;">Height</span>'))
                    ->suffix(' cm')
                    ->getStateUsing(function ($record) {
                        return $record->officeVisits()
                            ->latest('visit_date')
                            ->first()
                            ?->height
                            ?? $record->height_cm;
                    })
                    ->placeholder('—'),

                TextEntry::make('weight_kg')
                    ->label(new HtmlString('<span style="font-weight:750;">Weight</span>'))
                    ->suffix(' kg')
                    ->getStateUsing(function ($record) {
                        return $record->officeVisits()
                            ->latest('visit_date')
                            ->first()
                            ?->weight
                            ?? $record->weight_kg;
                    })
                    ->placeholder('—'),

                TextEntry::make('birthplace')
                    ->label(new HtmlString('<span style="font-weight:750;">Place of Birth</span>')),

                TextEntry::make('nutritional_status')
                    ->label(new HtmlString('<span style="font-weight:750;">Nutritional Status</span>'))
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        return $record->officeVisits()
                            ->latest('visit_date')
                            ->first()
                            ?->status
                            ?? $record->nutritional_status
                            ?? '—';
                    })
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->columnSpan(1),

                TextEntry::make('underlying_cause')
                    ->label(new HtmlString('<span style="font-weight:750;">Underlying Cause</span>'))
                    ->placeholder('—')
                    ->wrap()
                    ->columnSpan(1),


                TextEntry::make('address')
                    ->label(new HtmlString('<span style="font-weight:750;">Address</span>'))
                    ->columnSpan(2)
                    ->wrap()
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

    private static function sectionGuardianInformation(): Section
    {
        return Section::make('👨‍👩‍👦 Guardian Information')
            ->columns(2)
            ->schema([
                Section::make('Mother')
                    ->visible(fn ($record) => (bool) $record->motherProfile)
                    ->columnSpan(fn ($record) => ($record->motherProfile && $record->fatherProfile) ? 1 : 2)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('motherProfile.firstname')
                            ->label(new HtmlString('<span style="font-weight:750;">Full Name</span>'))
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->motherProfile?->firstname  ?? '') . ' ' .
                                ($record->motherProfile?->middlename ?? '') . ' ' .
                                ($record->motherProfile?->lastname   ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('motherProfile.birthdate')
                            ->label(new HtmlString('<span style="font-weight:750;">Birth Date</span>'))
                            ->formatStateUsing(fn ($state) =>
                            $state ? \Carbon\Carbon::parse($state)->format('F d, Y') : '—'),
                        TextEntry::make('motherProfile.relation')
                            ->label(new HtmlString('<span style="font-weight:750;">Relationship</span>'))
                            ->formatStateUsing(fn ($state) => self::relationLabel($state ?? '')),
                        TextEntry::make('motherProfile.occupation')
                            ->label(new HtmlString('<span style="font-weight:750;">Occupation</span>'))
                            ->placeholder('—'),
                        TextEntry::make('motherProfile.educational_attainment')
                            ->label(new HtmlString('<span style="font-weight:750;">Education</span>'))
                            ->formatStateUsing(fn ($state) => self::educationLabel($state ?? '')),
                    ]),

                Section::make('Father')
                    ->visible(fn ($record) => (bool) $record->fatherProfile)
                    ->columnSpan(fn ($record) => ($record->motherProfile && $record->fatherProfile) ? 1 : 2)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('fatherProfile.firstname')
                            ->label(new HtmlString('<span style="font-weight:750;">Full Name</span>'))
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->fatherProfile?->firstname  ?? '') . ' ' .
                                ($record->fatherProfile?->middlename ?? '') . ' ' .
                                ($record->fatherProfile?->lastname   ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('fatherProfile.birthdate')
                            ->label(new HtmlString('<span style="font-weight:750;">Birth Date</span>'))
                            ->formatStateUsing(fn ($state) =>
                            $state ? \Carbon\Carbon::parse($state)->format('F d, Y') : '—'),
                        TextEntry::make('fatherProfile.relation')
                            ->label(new HtmlString('<span style="font-weight:750;">Relationship</span>'))
                            ->formatStateUsing(fn ($state) => self::relationLabel($state ?? '')),
                        TextEntry::make('fatherProfile.occupation')
                            ->label(new HtmlString('<span style="font-weight:750;">Occupation</span>'))
                            ->placeholder('—'),
                        TextEntry::make('fatherProfile.educational_attainment')
                            ->label(new HtmlString('<span style="font-weight:750;">Education</span>'))
                            ->formatStateUsing(fn ($state) => self::educationLabel($state ?? '')),
                    ]),

                Section::make('Guardian')
                    ->visible(fn ($record) => ! $record->motherProfile && ! $record->fatherProfile && (bool) $record->guardianProfile)
                    ->columnSpan(2)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('guardianProfile.firstname')
                            ->label(new HtmlString('<span style="font-weight:750;">Full Name</span>'))
                            ->formatStateUsing(fn ($record) =>
                            trim(
                                ($record->guardianProfile?->firstname  ?? '') . ' ' .
                                ($record->guardianProfile?->middlename ?? '') . ' ' .
                                ($record->guardianProfile?->lastname   ?? '')
                            ) ?: '—'
                            ),
                        TextEntry::make('guardianProfile.birthdate')
                            ->label(new HtmlString('<span style="font-weight:750;">Birth Date</span>'))
                            ->formatStateUsing(fn ($state) =>
                            $state ? \Carbon\Carbon::parse($state)->format('F d, Y') : '—'),
                        TextEntry::make('guardianProfile.relation')
                            ->label(new HtmlString('<span style="font-weight:750;">Relationship</span>'))
                            ->formatStateUsing(fn ($state) => self::relationLabel($state ?? '')),
                        TextEntry::make('guardianProfile.occupation')
                            ->label(new HtmlString('<span style="font-weight:750;">Occupation</span>'))
                            ->placeholder('—'),
                        TextEntry::make('guardianProfile.educational_attainment')
                            ->label(new HtmlString('<span style="font-weight:750;">Education</span>'))
                            ->formatStateUsing(fn ($state) => self::educationLabel($state ?? '')),
                    ]),

            ]);
    }

    private static function relationLabel(string $state): string
    {
        return match ($state) {
            'biological_mother' => 'Biological Mother',
            'biological_father' => 'Biological Father',
            'adoptive_mother'   => 'Adoptive Mother',
            'adoptive_father'   => 'Adoptive Father',
            'grandmother'       => 'Grandmother',
            'grandfather'       => 'Grandfather',
            'aunt'              => 'Aunt',
            'uncle'             => 'Uncle',
            'older_sibling'     => 'Older Sibling',
            'legal_guardian'    => 'Legal Guardian',
            'foster_parent'     => 'Foster Parent',
            'court_appointed'   => 'Court-Appointed Guardian',
            'family_friend'     => 'Family Friend',
            default             => ucwords(str_replace('_', ' ', $state)),
        };
    }

    private static function educationLabel(string $state): string
    {
        return match ($state) {
            'no_formal_education'      => 'No Formal Education',
            'elementary_undergraduate' => 'Elementary Undergraduate',
            'elementary_graduate'      => 'Elementary Graduate',
            'jhs_undergraduate'        => 'Junior High School Undergraduate',
            'jhs_graduate'             => 'Junior High School Graduate (Grade 10)',
            'shs_undergraduate'        => 'Senior High School Undergraduate',
            'shs_graduate'             => 'Senior High School Graduate (Grade 12)',
            'vocational'               => 'Vocational / Technical Course',
            'college_undergraduate'    => 'College Undergraduate',
            'college_graduate'         => 'College Graduate',
            'masters'                  => "Master's Degree",
            'doctorate'                => 'Doctorate Degree',
            default                    => ucwords(str_replace('_', ' ', $state)),
        };
    }

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
                            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;border-radius:10px;border:2px dashed #e5e7eb;background:#f9fafb;text-align:center;">
                                <p style="margin:0;font-size:14px;font-weight:600;color:#374151;">No Family Members</p>
                                <p style="margin:4px 0 0;font-size:13px;color:#9ca3af;">No family members have been recorded for this child yet.</p>
                            </div>');
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
                            </tr>";
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
                        </table>");
                    })()),
            ]);
    }

    private static function sectionAssignmentSummary(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('Assignment')
                    ->columnSpanFull()
                    ->getStateUsing(function ($record) {
                        $assignment  = $record->officeAssignments()->with('bns', 'office')->latest()->first();
                        $bns         = $assignment?->bns;
                        $office      = $assignment?->office;
                        $totalVisits = $record->officeVisits()->count();
                        $bnsName     = $bns ? trim("{$bns->firstname} {$bns->lastname}") : '—';
                        $officeName  = $office?->office ?? '—';

                        return new HtmlString("
                        <div style='display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;border-top:4px solid #f97316;border-radius:12px;background:#fff;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);'>
                            <div style='text-align:center;padding:20px 16px;'>
                                <p style='font-size:28px;font-weight:800;color:#111827;margin:0;'>{$totalVisits}</p>
                                <p style='font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:4px 0 0;'>Total Visits</p>
                            </div>
                            <div style='text-align:center;padding:20px 16px;border-left:1px solid #f3f4f6;border-right:1px solid #f3f4f6;'>
                                <p style='font-size:18px;font-weight:700;color:#111827;margin:0;'>{$bnsName}</p>
                                <p style='font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:4px 0 0;'>Assigned BNS</p>
                            </div>
                            <div style='text-align:center;padding:20px 16px;'>
                                <p style='font-size:15px;font-weight:700;color:#111827;margin:0;'>{$officeName}</p>
                                <p style='font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:4px 0 0;'>Assigned Office</p>
                            </div>
                        </div>");
                    }),
            ])
            ->extraAttributes(['style' => 'padding:0;border:none;box-shadow:none;background:transparent;']);
    }

    private static function sectionRecordVisitAction(): Actions
    {
        return Actions::make([
            Action::make('record_visit')
                ->label('Record New Visit')
                ->icon('heroicon-o-plus-circle')
                ->color('warning')
                ->modalHeading('Record Visit')
                ->modalDescription(fn ($record) => 'Recording a visit for ' . $record->firstname . ' ' . $record->lastname)
                ->modalWidth('4xl')
                ->modalSubmitActionLabel('Save Visit')
                ->fillForm(function ($record): array {
                    $assignment = $record->officeAssignments()->with('bns', 'office')->latest()->first();

                    $addressParts = [
                        $record->purok ? "{$record->purok}" : null,
                        $record->barangay?->brgyDesc,
                        $record->municipality?->citymunDesc,
                        $record->municipality?->province?->provDesc,
                    ];

                    $ageMonths = null;
                    if ($record->birthdate) {
                        $diff      = Carbon::parse($record->birthdate)->diff(now());
                        $ageMonths = ($diff->y * 12) + $diff->m;
                    }

                    return [
                        'office_assign_id' => $assignment?->id,
                        'adopted_id'       => $record->id,
                        'bns_id'           => $assignment?->bns_id,
                        'office_id'        => $assignment?->office_id,
                        'visit_address'    => collect($addressParts)->filter()->implode(', '),
                        'visit_date'       => now()->toDateString(),
                        'sex'              => $record->sex,
                        'age_months'       => $ageMonths,
                    ];
                })
                ->form([
                    Hidden::make('adopted_id'),
                    Hidden::make('sex'),
                    Hidden::make('age_months'),

                    Grid::make(2)->schema([
                        DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->default(now()),

                        TextInput::make('visit_address')
                            ->label('Visit Address')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('bns_id')
                            ->label('BNS')
                            ->options(
                                BaranggayNutritionScholars::all()
                                    ->mapWithKeys(fn ($b) => [
                                        $b->id => $b->firstname . ' ' . $b->lastname
                                    ])
                            )
                            ->searchable()
                            ->required(),

                        Select::make('office_id')
                            ->label('Office')
                            ->options(
                                Office::all()->mapWithKeys(fn ($o) => [
                                    $o->id => $o->office . ' (' . $o->short_name . ')'
                                ])
                            )
                            ->searchable()
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('height')
                            ->label('Height')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.1)
                            ->minValue(0)
                            ->live()
                            ->required(),

                        TextInput::make('weight')
                            ->label('Weight')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.1)
                            ->minValue(0)
                            ->live()
                            ->required(),
                    ]),

                    // Live nutritional status preview
                    Placeholder::make('nutritional_status_preview')
                        ->label('')
                        ->hiddenLabel()
                        ->content(function (Get $get): HtmlString {
                            $months = $get('age_months');
                            $weight = (float) $get('weight');
                            $height = (float) $get('height');
                            $sex    = $get('sex');

                            if ($months === null || $weight <= 0 || $height <= 0
                                || ! in_array($sex, ['male', 'female'])) {
                                return new HtmlString('
                                    <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;
                                                border-radius:10px;border:2px dashed #d1d5db;background:#f9fafb;">
                                        <svg style="width:24px;height:24px;color:#9ca3af;flex-shrink:0;"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0
                                                     002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2
                                                     2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2
                                                     2 0 01-2-2z"/>
                                        </svg>
                                        <div>
                                            <p style="margin:0;font-size:13px;font-weight:600;color:#6b7280;">
                                                Nutritional Status</p>
                                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                                Enter height &amp; weight to compute status</p>
                                        </div>
                                    </div>');
                            }

                            $status = NutritionalStatus::classify((int) $months, $weight, $height, $sex);

                            $config = match (true) {
                                str_contains($status, 'Obese'),
                                str_contains($status, 'Severely Underweight'),
                                str_contains($status, 'SUW'),
                                str_contains($status, 'SST'),
                                str_contains($status, 'Wasted (W)') => [
                                    'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#dc2626',
                                    'icon_bg' => '#fee2e2', 'icon_color' => '#dc2626',
                                    'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                str_contains($status, 'Overweight'),
                                str_contains($status, 'At Risk'),
                                str_contains($status, 'OW') => [
                                    'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#2563eb',
                                    'icon_bg' => '#dbeafe', 'icon_color' => '#2563eb',
                                    'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                str_contains($status, 'Underweight'),
                                str_contains($status, 'Stunted'),
                                str_contains($status, 'UW'),
                                str_contains($status, 'ST'),
                                str_contains($status, 'MW') => [
                                    'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#d97706',
                                    'icon_bg' => '#fef3c7', 'icon_color' => '#d97706',
                                    'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                default => [
                                    'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#16a34a',
                                    'icon_bg' => '#dcfce7', 'icon_color' => '#16a34a',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                            };

                            $bmi = round($weight / (($height / 100) ** 2), 1);

                            return new HtmlString("
                                <div style=\"padding:20px;border-radius:12px;border:2px solid {$config['border']};background:{$config['bg']};\">
                                    <div style=\"display:flex;align-items:center;gap:14px;\">
                                        <div style=\"width:48px;height:48px;border-radius:50%;background:{$config['icon_bg']};
                                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;\">
                                            <svg style=\"width:26px;height:26px;\" fill=\"none\"
                                                 stroke=\"{$config['icon_color']}\" viewBox=\"0 0 24 24\">
                                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\"
                                                      stroke-width=\"2\" d=\"{$config['icon']}\"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p style=\"margin:0;font-size:11px;font-weight:600;text-transform:uppercase;
                                                       letter-spacing:0.08em;color:{$config['text']};opacity:0.8;\">
                                                Nutritional Status</p>
                                            <p style=\"margin:4px 0 0;font-size:20px;font-weight:800;color:{$config['text']};\">
                                                {$status}</p>
                                        </div>
                                    </div>
                                    <div style=\"display:flex;gap:20px;margin-top:12px;padding-top:12px;
                                                border-top:1px solid {$config['border']};\">
                                        <div style=\"text-align:center;\">
                                            <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                                       text-transform:uppercase;letter-spacing:0.05em;\">Height</p>
                                            <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$height} cm</p>
                                        </div>
                                        <div style=\"text-align:center;\">
                                            <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                                       text-transform:uppercase;letter-spacing:0.05em;\">Weight</p>
                                            <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$weight} kg</p>
                                        </div>
                                        <div style=\"text-align:center;\">
                                            <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                                       text-transform:uppercase;letter-spacing:0.05em;\">BMI</p>
                                            <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$bmi}</p>
                                        </div>
                                        <div style=\"text-align:center;\">
                                            <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                                       text-transform:uppercase;letter-spacing:0.05em;\">Age</p>
                                            <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$months} mos</p>
                                        </div>
                                    </div>
                                </div>");
                        }),

                    FileUpload::make('visit_documentation')
                        ->label('Visit Photos / Documents')
                        ->multiple()
                        ->disk('public')
                        ->directory('visit_docs')
                        ->visibility('public')
                        ->image()
                        ->maxFiles(10)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                        ->helperText('Upload up to 10 images or PDFs'),

                    Repeater::make('visitItems')
                        ->label('Items Distributed')
                        ->schema([
                            TextInput::make('Item_description')
                                ->label('Item Description')
                                ->placeholder('e.g. Rice, Vitamins')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('item_quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('item_amount')
                                ->label('Amount / Value')
                                ->numeric()
                                ->prefix('₱')
                                ->placeholder('0.00')
                                ->columnSpan(1),
                        ])
                        ->columns(4)
                        ->addActionLabel('Add Item')
                        ->defaultItems(0),
                ])
                ->action(function (array $data, $record): void {
                    // Resolve nutritional status
                    $data = OfficeVisitsForm::resolveStatus($data);

                    $items = $data['visitItems'] ?? [];
                    unset($data['visitItems']);

                    $visit = OfficeChildVisit::create([
                        'office_assign_id' => $data['office_assign_id'] ?? null,
                        'adopted_id' => $record->id,
                        'bns_id' => $data['bns_id'],
                        'office_id' => $data['office_id'],
                        'visit_date' => $data['visit_date'],
                        'visit_address' => $data['visit_address'],
                        'height' => $data['height'],
                        'weight' => $data['weight'],
                        'status' => $data['status'] ?? null,
                        'visit_documentation' => $data['visit_documentation'] ?? null,
                    ]);

                    foreach ($items as $item) {
                        $visit->visitItems()->create([
                            'Item_description' => $item['Item_description'],
                            'item_quantity'    => $item['item_quantity'],
                            'item_amount'      => $item['item_amount'] ?? null,
                        ]);
                    }

                    Notification::make()
                        ->title('Visit recorded successfully')
                        ->success()
                        ->send();
                }),
        ]);
    }

    private static function sectionVisitHistory(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('Visits')
                    ->label('')
                    ->columnSpanFull()
                    ->getStateUsing(function ($record) {
                        $visits = $record->officeVisits()
                            ->with('visitItems', 'bns', 'office')
                            ->latest('visit_date')
                            ->get();

                        $uid = 'vt_' . $record->id;

                        $badgeStyle = function (string $status): string {
                            $s = strtolower($status);
                            return match (true) {
                                str_contains($s, 'severely') ||
                                str_contains($s, 'wasted') ||
                                str_contains($s, 'obese') => 'background:#fee2e2;color:#b91c1c;',
                                str_contains($s, 'underweight') ||
                                str_contains($s, 'stunted') ||
                                str_contains($s, 'overweight') ||
                                str_contains($s, 'at risk') => 'background:#fef9c3;color:#a16207;',
                                str_contains($s, 'normal') => 'background:#dcfce7;color:#15803d;',
                                default => 'background:#f3f4f6;color:#374151;',
                            };
                        };

                        //Visit History rows
                        $historyRows = '';
                        $origHeight = $record->height_cm ? $record->height_cm . ' cm' : '—';
                        $origWeight = $record->weight_kg ? $record->weight_kg . ' kg' : '—';
                        $historyRows .= "
                        <tr style='border-bottom:1px solid #f3f4f6;background:#fefce8;'>
                            <td style='padding:12px 16px;font-size:13px;white-space:nowrap;color:#111827;'>
                                <span style='display:inline-flex;align-items:center;gap:6px;'>
                                    <span style='padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fde68a;color:#92400e;text-transform:uppercase;letter-spacing:.05em;'>
                                        📋 Original
                                    </span>
                                </span>
                            </td>
                            <td style='padding:12px 16px;font-size:13px;color:#6b7280;font-style:italic;'>Initial record on enrollment</td>
                            <td style='padding:12px 16px;font-size:13px;color:#374151;font-weight:600;'>{$origHeight}</td>
                            <td style='padding:12px 16px;font-size:13px;color:#374151;font-weight:600;'>{$origWeight}</td>
                            <td style='padding:12px 16px;font-size:13px;'>
                                    " . ($record->nutritional_status
                                ? "<span style='padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;" .
                                $badgeStyle($record->nutritional_status) . "white-space:nowrap;display:inline-block;'>" .
                                htmlspecialchars($record->nutritional_status) . "</span>"
                                : "<span style='color:#6b7280;font-style:italic;'>—</span>"
                            ) . "
                            </td>
                        </tr>";

                        if ($visits->isEmpty()) {
                            $historyRows .= "<tr><td colspan='5' style='padding:40px;text-align:center;color:#9ca3af;font-size:13px;'>No visits recorded yet.</td></tr>";
                        } else {
                            foreach ($visits as $visit) {
                                $date    = $visit->visit_date ? $visit->visit_date->format('M d, Y') : '—';
                                $height  = $visit->height ? $visit->height . ' cm' : '—';
                                $weight  = $visit->weight ? $visit->weight . ' kg' : '—';
                                $status  = htmlspecialchars($visit->status ?? '—');
                                $address = htmlspecialchars($visit->visit_address ?? '—');
                                $bs      = $badgeStyle($visit->status ?? '');

                                $historyRows .= "
                                <tr class='{$uid}_hrow' style='border-bottom:1px solid #f3f4f6;'>
                                    <td style='padding:12px 16px;font-size:13px;font-weight:600;white-space:nowrap;color:#111827;'>{$date}</td>
                                    <td style='padding:12px 16px;font-size:13px;color:#6b7280;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;'>{$address}</td>
                                    <td style='padding:12px 16px;font-size:13px;color:#374151;'>{$height}</td>
                                    <td style='padding:12px 16px;font-size:13px;color:#374151;'>{$weight}</td>
                                    <td style='padding:12px 16px;font-size:13px;'>
                                        <span style='padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;{$bs}white-space:nowrap;display:inline-block;'>
                                            {$status}
                                        </span>
                                    </td>
                                </tr>";
                            }
                        }

                        // Visit Items rows
                        $itemRows    = '';
                        $hasAnyItems = false;
                        $grandTotal  = 0.0; // 👇 running total

                        foreach ($visits as $visit) {
                            foreach ($visit->visitItems as $item) {
                                $hasAnyItems = true;
                                $date    = $visit->visit_date ? $visit->visit_date->format('M d, Y') : '—';
                                $desc    = htmlspecialchars($item->Item_description ?? '—');
                                $qty     = htmlspecialchars((string) ($item->item_quantity ?? '—'));
                                $rawAmt  = $item->item_amount ?? null;
                                $amount  = $rawAmt !== null ? '₱' . number_format((float) $rawAmt, 2) : '—';
                                $grandTotal += (float) ($rawAmt ?? 0); // 👇 accumulate

                                $itemRows .= "
                                <tr class='{$uid}_irow' style='border-bottom:1px solid #f3f4f6;'>
                                    <td style='padding:12px 16px;font-size:13px;font-weight:600;white-space:nowrap;color:#111827;'>{$date}</td>
                                    <td style='padding:12px 16px;font-size:13px;color:#374151;'>{$desc}</td>
                                    <td style='padding:12px 16px;font-size:13px;color:#374151;text-align:center;'>{$qty}</td>
                                    <td style='padding:12px 16px;font-size:13px;color:#374151;text-align:right;'>{$amount}</td>
                                </tr>";
                            }
                        }

                        if (! $hasAnyItems) {
                            $itemRows = "<tr><td colspan='4' style='padding:40px;text-align:center;color:#9ca3af;font-size:13px;'>No items distributed yet.</td></tr>";
                        }
                        $totalFormatted = '₱' . number_format($grandTotal, 2);
                        $itemsFooter = $hasAnyItems ? "
                        <tfoot>
                            <tr style='background:#f9fafb;border-top:2px solid #e5e7eb;'>
                                <td colspan='3' style='padding:12px 16px;font-size:13px;font-weight:700;color:#111827;text-align:right;'>Total Amount</td>
                                <td style='padding:12px 16px;font-size:14px;font-weight:800;color:#111827;text-align:right;'>{$totalFormatted}</td>
                            </tr>
                        </tfoot>" : '';

                        return new HtmlString("
                            <div style='border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;'>

                                <!-- Tabs -->
                                <div style='display:flex;gap:0;border-bottom:1px solid #e5e7eb;background:#f9fafb;padding:0 20px;'>
                                    <button id='{$uid}_btn_history'
                                        style='padding:12px 18px;font-size:13px;font-weight:600;border:none;background:transparent;cursor:pointer;color:#f97316;border-bottom:2px solid #f97316;margin-bottom:-1px;'>
                                        🗓️ Visit History
                                    </button>
                                    <button id='{$uid}_btn_items'
                                        style='padding:12px 18px;font-size:13px;font-weight:500;border:none;background:transparent;cursor:pointer;color:#6b7280;border-bottom:2px solid transparent;margin-bottom:-1px;'>
                                        📦 Visit Items
                                    </button>
                                </div>

                                <!-- Visit History Tab -->
                                <div id='{$uid}_history' style='display:block;overflow-x:auto;'>
                                    <table style='width:100%;border-collapse:collapse;'>
                                        <thead>
                                            <tr style='background:#f9fafb;'>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;'>Visit Date</th>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Address</th>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Height</th>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Weight</th>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Nutritional Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id='{$uid}_hbody'>{$historyRows}</tbody>
                                    </table>
                                    <div id='{$uid}_hpager' style='display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid #f3f4f6;background:#f9fafb;'></div>
                                </div>

                                <!-- Visit Items Tab -->
                                <div id='{$uid}_items' style='display:none;overflow-x:auto;'>
                                    <table style='width:100%;border-collapse:collapse;'>
                                        <thead>
                                            <tr style='background:#f9fafb;'>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;'>Visit Date</th>
                                                <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Item Description</th>
                                                <th style='padding:10px 16px;text-align:center;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Quantity</th>
                                                <th style='padding:10px 16px;text-align:right;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id='{$uid}_ibody'>{$itemRows}</tbody>
                                        {$itemsFooter}
                                    </table>
                                    <div id='{$uid}_ipager' style='display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid #f3f4f6;background:#f9fafb;'></div>
                                </div>

                            </div>

                            <script>
                            (function() {
                                var PER_PAGE = 6;
                                var uid = '{$uid}';

                                // Tab switching
                                var btnHistory = document.getElementById(uid + '_btn_history');
                                var btnItems   = document.getElementById(uid + '_btn_items');
                                var tabHistory = document.getElementById(uid + '_history');
                                var tabItems   = document.getElementById(uid + '_items');

                                var activeStyle   = 'padding:12px 18px;font-size:13px;font-weight:600;border:none;background:transparent;cursor:pointer;color:#f97316;border-bottom:2px solid #f97316;margin-bottom:-1px;';
                                var inactiveStyle = 'padding:12px 18px;font-size:13px;font-weight:500;border:none;background:transparent;cursor:pointer;color:#6b7280;border-bottom:2px solid transparent;margin-bottom:-1px;';

                                btnHistory.addEventListener('click', function() {
                                    tabHistory.style.display = 'block';
                                    tabItems.style.display   = 'none';
                                    btnHistory.style.cssText = activeStyle;
                                    btnItems.style.cssText   = inactiveStyle;
                                });

                                btnItems.addEventListener('click', function() {
                                    tabHistory.style.display = 'none';
                                    tabItems.style.display   = 'block';
                                    btnItems.style.cssText   = activeStyle;
                                    btnHistory.style.cssText = inactiveStyle;
                                });

                                // Pagination
                                function paginate(rowClass, pagerId) {
                                    var rows  = Array.from(document.querySelectorAll('.' + rowClass));
                                    var pager = document.getElementById(pagerId);
                                    var page  = 1;
                                    var total = rows.length;
                                    var pages = Math.ceil(total / PER_PAGE);

                                    if (total === 0) {
                                        pager.style.display = 'none';
                                        return;
                                    }

                                    function render() {
                                        rows.forEach(function(r, i) {
                                            r.style.display = (i >= (page - 1) * PER_PAGE && i < page * PER_PAGE) ? '' : 'none';
                                        });

                                        var from = (page - 1) * PER_PAGE + 1;
                                        var to   = Math.min(page * PER_PAGE, total);

                                        var prevBtn = document.createElement('button');
                                        prevBtn.textContent = '‹ Prev';
                                        prevBtn.disabled    = (page === 1);
                                        prevBtn.style.cssText = 'padding:5px 12px;font-size:12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:' + (page === 1 ? 'default;color:#d1d5db;' : 'pointer;color:#374151;');
                                        prevBtn.addEventListener('click', function() {
                                            if (page > 1) { page--; render(); }
                                        });

                                        var nextBtn = document.createElement('button');
                                        nextBtn.textContent = 'Next ›';
                                        nextBtn.disabled    = (page === pages);
                                        nextBtn.style.cssText = 'padding:5px 12px;font-size:12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:' + (page === pages ? 'default;color:#d1d5db;' : 'pointer;color:#374151;');
                                        nextBtn.addEventListener('click', function() {
                                            if (page < pages) { page++; render(); }
                                        });

                                        var info = document.createElement('span');
                                        info.style.cssText  = 'font-size:12px;color:#6b7280;';
                                        info.textContent    = 'Showing ' + from + '\u2013' + to + ' of ' + total;

                                        var controls = document.createElement('div');
                                        controls.style.cssText = 'display:flex;gap:6px;';
                                        controls.appendChild(prevBtn);
                                        controls.appendChild(nextBtn);

                                        pager.innerHTML = '';
                                        pager.appendChild(info);
                                        pager.appendChild(controls);
                                    }

                                    render();
                                }

                                paginate(uid + '_hrow', uid + '_hpager');
                                paginate(uid + '_irow', uid + '_ipager');
                            })();
                            </script>");
                    }),
            ])
            ->extraAttributes(['style' => 'padding:0;border:none;box-shadow:none;background:transparent;']);
    }

    private static function sectionFamilyStatus(): Section
    {
        return Section::make('🏠 Family Status')
            ->columns(3)
            ->schema([
                TextEntry::make('familyStatus.status')
                    ->label(new HtmlString('<span style="font-weight:750;">Civil Status</span>'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'civil'      => 'Civil',
                        'church'     => 'Church / Religious',
                        'common_law' => 'Common Law',
                        'none'       => 'N/A',
                        default      => ucwords(str_replace('_', ' ', $state ?? '—')),
                    }),
                TextEntry::make('familyStatus.type_of_marriage')
                    ->label(new HtmlString('<span style="font-weight:750;">Type of Marriage</span>'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'single'     => 'Single',
                        'married'    => 'Married',
                        'widowed'    => 'Widowed',
                        'separated'  => 'Separated',
                        'cohabiting' => 'Live-in / Cohabiting',
                        default      => ucwords(str_replace('_', ' ', $state ?? '—')),
                    }),
                TextEntry::make('familyStatus.monthly_income')
                    ->label(new HtmlString('<span style="font-weight:750;">Monthly Income</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->monthly_income) {
                        'below_5000'   => 'Below ₱5,000',
                        '5000-9999'    => '₱5,000 - ₱9,999',
                        '10000-14999'  => '₱10,000 - ₱14,999',
                        '15000-19999'  => '₱15,000 - ₱19,999',
                        '20000-above'  => '₱20,000 and above',
                        default        => '—',
                    }),
                TextEntry::make('familyStatus.source_income')
                    ->label(new HtmlString('<span style="font-weight:750;">Source of Income</span>'))
                    ->formatStateUsing(fn ($record) => $record->familyStatus->first()?->source_income ?? '—'),
                TextEntry::make('familyStatus.phil_member')
                    ->label(new HtmlString('<span style="font-weight:750;">PhilHealth Member?</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->phil_member) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    }),
                TextEntry::make('familyStatus.family_plan_method')
                    ->label(new HtmlString('<span style="font-weight:750;">Family Planning</span>'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'natural'   => 'Natural',
                        'pills'     => 'Pills',
                        'condom'    => 'Condom',
                        'iud'       => 'IUD',
                        'ligation'  => 'Ligation',
                        'vasectomy' => 'Vasectomy',
                        'none'      => 'None',
                        default     => ucwords(str_replace('_', ' ', $state ?? '—')),
                    }),
                TextEntry::make('familyStatus.have_electricity')
                    ->label(new HtmlString('<span style="font-weight:750;">Has Electricity?</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->have_electricity) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    }),
                TextEntry::make('familyStatus.water_source')
                    ->label(new HtmlString('<span style="font-weight:750;">Water Source</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->water_source) {
                        'tap'       => 'Tap / Piped Water',
                        'well'      => 'Deep Well',
                        'spring'    => 'Spring',
                        'river'     => 'River / Stream',
                        'rain'      => 'Rainwater',
                        'delivered' => 'Delivered Water',
                        default     => '—',
                    }),
                TextEntry::make('familyStatus.toilet_facility')
                    ->label(new HtmlString('<span style="font-weight:750;">Toilet Facility</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->toilet_facility) {
                        'flush'  => 'Water-sealed / Flush',
                        'pit'    => 'Pit Latrine',
                        'open'   => 'Open Defecation',
                        'shared' => 'Shared Toilet',
                        default  => '—',
                    }),
                TextEntry::make('familyStatus.roofing')
                    ->label(new HtmlString('<span style="font-weight:750;">Roofing</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->roofing) {
                        'galvanized' => 'Galvanized Iron',
                        'concrete'   => 'Concrete',
                        'nipa'       => 'Nipa / Cogon',
                        'wood'       => 'Wood',
                        default      => '—',
                    }),
                TextEntry::make('familyStatus.walls')
                    ->label(new HtmlString('<span style="font-weight:750;">Wall Material</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->walls) {
                        'concrete' => 'Concrete / Hollow Blocks',
                        'wood'     => 'Wood',
                        'bamboo'   => 'Bamboo',
                        'mixed'    => 'Mixed Materials',
                        default    => '—',
                    }),
                TextEntry::make('familyStatus.flooring')
                    ->label(new HtmlString('<span style="font-weight:750;">Flooring</span>'))
                    ->formatStateUsing(fn ($record) => match ($record->familyStatus->first()?->flooring) {
                        'concrete' => 'Concrete',
                        'wood'     => 'Wood',
                        'earth'    => 'Earth / Soil',
                        'tile'     => 'Tile',
                        default    => '—',
                    }),
            ]);
    }

    private static function statusColor(string $state): string
    {
        return AdoptedChildrenTable::statusColor($state);
    }
}
