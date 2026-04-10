<?php

namespace App\Filament\Resources\AdoptedChildren\Schemas;

use App\Helpers\NutritionalStatus;
use App\Models\Barangay;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AdoptedChildForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::childInformationFields());
    }

    public static function wizardSteps(): array
    {
        return [
            self::stepChildInformation(),
            self::stepGuardianInformation(),
            self::stepFamilyMembers(),
            self::stepFamilyStatus(),
            self::stepReview(),
        ];
    }

    public static function afterCreate($record, array $data): void
    {
        self::syncNutritionalStatus($record);

        $guardianType = $data['guardian_type'] ?? 'mother_and_father';
        if (in_array($guardianType, ['mother_and_father', 'mother_only'])) {
            $record->familyProfiles()->create([
                'type' => 'mother',
                'firstname' => $data['mother_firstname'],
                'lastname' => $data['mother_lastname'],
                'middlename' => $data['mother_middlename'] ?? null,
                'suffix' => $data['mother_suffix'] ?? null,
                'birthdate' => $data['mother_birthdate'] ?? null,
                'relation' => $data['mother_relation'] ?? null,
                'occupation' => $data['mother_occupation'] ?? null,
                'educational_attainment' => $data['mother_educational_attainment'] ?? null,
            ]);
        }

        if (in_array($guardianType, ['mother_and_father', 'father_only'])) {
            $record->familyProfiles()->create([
                'type' => 'father',
                'firstname' => $data['father_firstname'],
                'lastname' => $data['father_lastname'],
                'middlename' => $data['father_middlename'] ?? null,
                'suffix' => $data['father_suffix'] ?? null,
                'birthdate' => $data['father_birthdate'] ?? null,
                'relation' => $data['father_relation'] ?? null,
                'occupation' => $data['father_occupation'] ?? null,
                'educational_attainment' => $data['father_educational_attainment'] ?? null,
            ]);
        }

        if ($guardianType === 'guardian') {
            $record->familyProfiles()->create([
                'type' => 'guardian',
                'firstname' => $data['guardian_firstname'],
                'lastname' => $data['guardian_lastname'],
                'middlename' => $data['guardian_middlename'] ?? null,
                'suffix' => $data['guardian_suffix'] ?? null,
                'birthdate' => $data['guardian_birthdate'] ?? null,
                'relation' => $data['guardian_relation'] ?? null,
                'occupation' => $data['guardian_occupation'] ?? null,
                'educational_attainment' => $data['guardian_educational_attainment'] ?? null,
            ]);
        }

        // Family Members
        if (!empty($data['family_members'])) {
            foreach ($data['family_members'] as $member) {
                $record->familyProfiles()->create([
                    'type' => 'fam_member',
                    'fam_member_fullname' => $member['fam_member_fullname'],
                    'fam_member_actual_weight' => $member['fam_member_actual_weight'] ?? null,
                    'fam_member_nutrition_status' => $member['fam_member_nutrition_status'] ?? null,
                ]);
            }
        }

        // Family Status
        $record->familyStatus()->create([
            'status' => $data['civil_status'],
            'type_of_marriage' => $data['type_of_marriage'],
            'monthly_income'     => $data['monthly_income'],
            'source_income' => $data['source_income'],
            'phil_member' => $data['phil_member'],
            'family_plan_method' => $data['family_plan_method'],
            'have_electricity' => $data['have_electricity'],
            'water_source' => $data['water_source'],
            'toilet_facility' => $data['toilet_facility'],
            'roofing' => $data['roofing'],
            'walls' => $data['walls'],
            'flooring' => $data['flooring'],
        ]);
    }

    public static function fillEditForm($record): array
    {
        $mother = $record->motherProfile;
        $father = $record->fatherProfile;
        $guardian = $record->familyProfiles()->where('type', 'guardian')->first();
        $status = $record->familyStatus->first();

        // Detect guardian type from existing records
        $guardianType = 'mother_and_father';
        if ($guardian) {
            $guardianType = 'guardian';
        } elseif ($mother && $father) {
            $guardianType = 'mother_and_father';
        } elseif ($mother && !$father) {
            $guardianType = 'mother_only';
        } elseif (!$mother && $father) {
            $guardianType = 'father_only';
        }

        $ageDisplay = null;
        $ageMonths  = null;
        if ($record->birthdate) {
            $age = \Carbon\Carbon::parse($record->birthdate)->diff(now());
            $ageMonths  = ($age->y * 12) + $age->m;
            $ageDisplay = $age->y . 'y ' . $age->m . 'm (' . $ageMonths . ' months)';
        }

        return [
            'firstname' => $record->firstname,
            'middlename' => $record->middlename,
            'lastname' => $record->lastname,
            'suffix' => $record->suffix,
            'birthdate' => $record->birthdate,
            'birthplace' => $record->birthplace,
            'purok' => $record->purok,
            'municipality_id' => $record->municipality_id,
            'barangay_id' => $record->barangay_id,
            'sex' => $record->sex,
            'height_cm' => $record->height_cm,
            'weight_kg' => $record->weight_kg,
            'profile_path' => $record->profile_path,
            'age_display' => $ageDisplay,
            'age_months' => $ageMonths,

            // Guardian type selector
            'guardian_type' => $guardianType,

            // Mother fields
            'mother_firstname' => $mother?->firstname,
            'mother_middlename' => $mother?->middlename,
            'mother_lastname' => $mother?->lastname,
            'mother_suffix' => $mother?->suffix,
            'mother_birthdate' => $mother?->birthdate,
            'mother_relation' => $mother?->relation,
            'mother_occupation' => $mother?->occupation,
            'mother_educational_attainment' => $mother?->educational_attainment,

            // Father fields
            'father_firstname' => $father?->firstname,
            'father_middlename' => $father?->middlename,
            'father_lastname' => $father?->lastname,
            'father_suffix' => $father?->suffix,
            'father_birthdate' => $father?->birthdate,
            'father_relation' => $father?->relation,
            'father_occupation' => $father?->occupation,
            'father_educational_attainment' => $father?->educational_attainment,

            // Guardian fields
            'guardian_firstname' => $guardian?->firstname,
            'guardian_middlename' => $guardian?->middlename,
            'guardian_lastname' => $guardian?->lastname,
            'guardian_suffix' => $guardian?->suffix,
            'guardian_birthdate' => $guardian?->birthdate,
            'guardian_relation' => $guardian?->relation,
            'guardian_occupation' => $guardian?->occupation,
            'guardian_educational_attainment' => $guardian?->educational_attainment,

            'family_members' => $record->familyMembers->map(fn ($m) => [
                'fam_member_fullname' => $m->fam_member_fullname,
                'fam_member_actual_weight' => $m->fam_member_actual_weight,
                'fam_member_nutrition_status' => $m->fam_member_nutrition_status,
            ])->toArray(),

            'civil_status' => $status?->status,
            'type_of_marriage' => $status?->type_of_marriage,
            'monthly_income' => $status?->monthly_income,
            'source_income' => $status?->source_income,
            'phil_member' => $status?->phil_member,
            'family_plan_method' => $status?->family_plan_method,
            'have_electricity' => $status?->have_electricity,
            'water_source' => $status?->water_source,
            'toilet_facility' => $status?->toilet_facility,
            'roofing' => $status?->roofing,
            'walls' => $status?->walls,
            'flooring' => $status?->flooring,
        ];
    }

    public static function afterEdit($record, array $data): void
    {
        self::syncNutritionalStatus($record);

        $guardianType = $data['guardian_type'] ?? 'mother_and_father';
        $record->familyProfiles()->whereIn('type', ['mother', 'father', 'guardian'])->delete();

        if (in_array($guardianType, ['mother_and_father', 'mother_only'])) {
            $record->familyProfiles()->create([
                'type' => 'mother',
                'firstname' => $data['mother_firstname'],
                'lastname' => $data['mother_lastname'],
                'middlename' => $data['mother_middlename'] ?? null,
                'suffix' => $data['mother_suffix'] ?? null,
                'birthdate' => $data['mother_birthdate'] ?? null,
                'relation' => $data['mother_relation'] ?? null,
                'occupation' => $data['mother_occupation'] ?? null,
                'educational_attainment' => $data['mother_educational_attainment'] ?? null,
            ]);
        }

        if (in_array($guardianType, ['mother_and_father', 'father_only'])) {
            $record->familyProfiles()->create([
                'type' => 'father',
                'firstname' => $data['father_firstname'],
                'lastname' => $data['father_lastname'],
                'middlename' => $data['father_middlename'] ?? null,
                'suffix' => $data['father_suffix'] ?? null,
                'birthdate' => $data['father_birthdate'] ?? null,
                'relation' => $data['father_relation'] ?? null,
                'occupation' => $data['father_occupation'] ?? null,
                'educational_attainment' => $data['father_educational_attainment'] ?? null,
            ]);
        }

        if ($guardianType === 'guardian') {
            $record->familyProfiles()->create([
                'type' => 'guardian',
                'firstname' => $data['guardian_firstname'],
                'lastname' => $data['guardian_lastname'],
                'middlename' => $data['guardian_middlename'] ?? null,
                'suffix' => $data['guardian_suffix'] ?? null,
                'birthdate' => $data['guardian_birthdate'] ?? null,
                'relation' => $data['guardian_relation'] ?? null,
                'occupation' => $data['guardian_occupation'] ?? null,
                'educational_attainment' => $data['guardian_educational_attainment'] ?? null,
            ]);
        }

        $record->familyProfiles()->where('type', 'fam_member')->delete();
        foreach ($data['family_members'] ?? [] as $member) {
            $record->familyProfiles()->create([
                'type' => 'fam_member',
                'fam_member_fullname' => $member['fam_member_fullname'],
                'fam_member_actual_weight' => $member['fam_member_actual_weight'] ?? null,
                'fam_member_nutrition_status' => $member['fam_member_nutrition_status'] ?? null,
            ]);
        }

        // Update Family Status
        $record->familyStatus()->updateOrCreate(
            [],
            [
                'status' => $data['civil_status'],
                'type_of_marriage' => $data['type_of_marriage'],
                'monthly_income' => $data['monthly_income'],
                'source_income' => $data['source_income'],
                'phil_member' => $data['phil_member'],
                'family_plan_method' => $data['family_plan_method'],
                'have_electricity' => $data['have_electricity'],
                'water_source' => $data['water_source'],
                'toilet_facility' => $data['toilet_facility'],
                'roofing' => $data['roofing'],
                'walls' => $data['walls'],
                'flooring' => $data['flooring'],
            ]
        );
    }

    public static function syncNutritionalStatus($record): void
    {
        if (!$record->birthdate || !$record->weight_kg || !$record->height_cm) {
            return;
        }

        $age = Carbon::parse($record->birthdate)->diff(now());
        $ageMonths = ($age->y * 12) + $age->m;

        $record->update([
            'nutritional_status' => NutritionalStatus::classify(
                $ageMonths,
                (float) $record->weight_kg,
                (float) $record->height_cm,
                $record->sex
            ),
        ]);
    }

    private static function stepChildInformation(): Step
    {
        return Step::make('Child Information')
            ->icon('heroicon-o-user')
            ->schema(self::childInformationFields());
    }

    private static function childInformationFields(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    TextInput::make('firstname')
                        ->label('First Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('middlename')
                        ->label('Middle Name'),
                    TextInput::make('lastname')
                        ->label('Last Name')
                        ->required(),
                    TextInput::make('suffix')
                        ->label('Suffix'),
                    Select::make('sex')
                        ->label('Sex')
                        ->required()
                        ->live()
                        ->options([
                            'male' => 'Male',
                            'female' => 'Female',
                        ]),
                    DatePicker::make('birthdate')
                        ->label('Date of Birth')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                $age = Carbon::parse($state)->diff(now());
                                $totalMonths = ($age->y * 12) + $age->m;
                                $set('age_months', $totalMonths);
                                $set('age_display', $age->y . 'y ' . $age->m . 'm (' . $totalMonths . ' months)');
                            }
                        }),
                    TextInput::make('birthplace')
                        ->label('Place of Birth')
                        ->required(),

                    TextInput::make('purok')
                        ->label('Purok')
                        ->required(),

                    Select::make('municipality_id')
                        ->label('Municipality')
                        ->relationship(
                            name: 'municipality',
                            titleAttribute: 'citymunDesc'
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->citymunDesc} ({$record->province->provDesc})")
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    Select::make('barangay_id')
                        ->label('Barangay')
                        ->options(function (Get $get) {
                            $municipalityCode = $get('municipality_id');
                            if (!$municipalityCode) {
                                return [];
                            }
                            return Barangay::where('citymunCode', $municipalityCode)
                                ->pluck('brgyDesc', 'brgyCode');
                        })
                        ->live()
                        ->searchable()
                        ->required(),
                ]),

            Grid::make(4)
                ->schema([
                    TextInput::make('age_display')
                        ->label('Age')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-computed from birthdate'),

                    TextInput::make('age_months')
                        ->label('Age in Months')
                        ->disabled()
                        ->numeric()
                        ->suffix('months')
                        ->placeholder('Auto-computed'),

                    TextInput::make('height_cm')
                        ->label('Height')
                        ->suffix('cm')
                        ->numeric()
                        ->required()
                        ->live()
                        ->minValue(40)
                        ->maxValue(250)
                        ->afterStateHydrated(function (TextInput $component, $record) {
                            if ($record) {
                                $latest = $record->officeVisits()->latest('visit_date')->first();
                                if ($latest && $latest->height) {
                                    $component->state($latest->height);
                                }
                            }
                        }),

                    TextInput::make('weight_kg')
                        ->label('Weight')
                        ->suffix('kg')
                        ->numeric()
                        ->required()
                        ->live()
                        ->minValue(1)
                        ->maxValue(300)
                        ->afterStateHydrated(function (TextInput $component, $record) {
                            if ($record) {
                                $latest = $record->officeVisits()->latest('visit_date')->first();
                                if ($latest && $latest->weight) {
                                    $component->state($latest->weight);
                                }
                            }
                        }),
                ]),

            Placeholder::make('nutritional_status_preview')
                ->label('')
                ->hiddenLabel()
                ->content(function (Get $get): HtmlString {
                    $months = $get('age_months');
                    $weight = (float) $get('weight_kg');
                    $height = (float) $get('height_cm');
                    $sex = $get('sex');

                    if ($months === null || $weight <= 0 || $height <= 0 || !in_array($sex, ['male', 'female'])) {
                        return new HtmlString('<div style="display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 10px; border: 2px dashed #d1d5db; background: #f9fafb;">
                                                        <svg style="width:24px;height:24px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                        </svg>
                                                        <div>
                                                            <p style="margin:0;font-size:13px;font-weight:600;color:#6b7280;">Nutritional Status</p>
                                                            <p style="margin:0;font-size:12px;color:#9ca3af;">Fill in birthdate, height, and weight to compute status</p>
                                                        </div>
                                                    </div>');
                    }

                    $status = NutritionalStatus::classify((int)$months, $weight, $height, $sex);
                    $config = match (true) {
                        str_contains($status, 'Obese'),
                        str_contains($status, 'Severely Underweight'),
                        str_contains($status, 'SUW'),
                        str_contains($status, 'SST'),
                        str_contains($status, 'Wasted (W)') => [
                            'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#dc2626',
                            'icon_bg' => '#fee2e2', 'icon_color' => '#dc2626',
                            'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                        ],

                        str_contains($status, 'Overweight'),
                        str_contains($status, 'At Risk'),
                        str_contains($status, 'OW') => [
                            'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#2563eb',
                            'icon_bg' => '#dbeafe', 'icon_color' => '#2563eb',
                            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                        ],

                        str_contains($status, 'Underweight'),
                        str_contains($status, 'Stunted'),
                        str_contains($status, 'UW'),
                        str_contains($status, 'ST'),
                        str_contains($status, 'MW') => [
                            'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#d97706',
                            'icon_bg' => '#fef3c7', 'icon_color' => '#d97706',
                            'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                        ],

                        default => [
                            'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#16a34a',
                            'icon_bg' => '#dcfce7', 'icon_color' => '#16a34a',
                            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                        ],
                    };

                    $bmi = round($weight / (($height / 100) ** 2), 1);

                    return new HtmlString("
                        <div style=\"padding: 20px; border-radius: 12px; border: 2px solid {$config['border']}; background: {$config['bg']};\">
                            <div style=\"display:flex; align-items:center; gap:14px;\">
                                <div style=\"width: 48px; height: 48px; border-radius: 50%; background: {$config['icon_bg']}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;\">
                                    <svg style=\"width:26px; height:26px;\" fill=\"none\" stroke=\"{$config['icon_color']}\" viewBox=\"0 0 24 24\">
                                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{$config['icon']}\"/>
                                    </svg>
                                </div>
                                <div>
                                    <p style=\"margin:0; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:{$config['text']}; opacity:0.8;\">Nutritional Status</p>
                                    <p style=\"margin:4px 0 0; font-size:20px; font-weight:800; color:{$config['text']};\">{$status}</p>
                                </div>
                            </div>
                            <div style=\"display: flex; gap: 20px; margin-top: 12px; padding-top: 12px; border-top: 1px solid {$config['border']}; \">
                                <div style=\"text-align:center;\">
                                    <p style=\"margin:0; font-size:11px; color:{$config['text']}; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;\">Height</p>
                                    <p style=\"margin:4px 0 0; font-size:16px; font-weight:700; color:{$config['text']};\">{$height} cm</p>
                                </div>
                                <div style=\"text-align:center;\">
                                    <p style=\"margin:0; font-size:11px; color:{$config['text']}; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;\">Weight</p>
                                    <p style=\"margin:4px 0 0; font-size:16px; font-weight:700; color:{$config['text']};\">{$weight} kg</p>
                                </div>
                                <div style=\"text-align:center;\">
                                    <p style=\"margin:0; font-size:11px; color:{$config['text']}; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;\">BMI</p>
                                    <p style=\"margin:4px 0 0; font-size:16px; font-weight:700; color:{$config['text']};\">{$bmi}</p>
                                </div>
                                <div style=\"text-align:center;\">
                                    <p style=\"margin:0; font-size:11px; color:{$config['text']}; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;\">Age</p>
                                    <p style=\"margin:4px 0 0; font-size:16px; font-weight:700; color:{$config['text']};\">{$months} mos</p>
                                </div>
                            </div>
                        </div>
                    ");
                }),

            TextInput::make('underlying_cause')
                ->label('Underlying Cause')
                ->type('textarea')
                ->required(),

            FileUpload::make('profile_path')
                ->label('Profile Picture')
                ->disk('public')
                ->directory('child/adopted-children')
                ->visibility('public'),
        ];
    }

    private static function stepGuardianInformation(): Step
    {
        return Step::make('Guardian Information')
            ->icon('heroicon-o-users')
            ->schema([
                //Guardian type selector
                Section::make('Who is the child\'s guardian?')
                    ->description('Select the type of guardian setup for this child. Only the relevant fields will appear.')
                    ->schema([
                        Radio::make('guardian_type')
                            ->label('Guardian Type')
                            ->options([
                                'mother_and_father' => '👨‍👩‍👦 Mother & Father',
                                'mother_only' => '👩 Mother Only',
                                'father_only' => '👨 Father Only',
                                'guardian' => '🧑‍🤝‍🧑 Guardian',
                            ])
                            ->default('mother_and_father')
                            ->inline()
                            ->inlineLabel(false)
                            ->live()
                            ->required(),
                    ]),

                // Mother Section
                Section::make('Mother Profile')
                    ->icon('heroicon-o-user')
                    ->hidden(fn (Get $get): bool => !in_array($get('guardian_type'), ['mother_and_father', 'mother_only']))
                    ->schema([
                        TextInput::make('mother_firstname')
                            ->label('First Name')
                            ->required(fn (Get $get): bool => in_array($get('guardian_type'), ['mother_and_father', 'mother_only'])),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mother_middlename')
                                    ->label('Middle Name'),
                                TextInput::make('mother_lastname')
                                    ->label('Last Name')
                                    ->required(fn (Get $get): bool => in_array($get('guardian_type'), ['mother_and_father', 'mother_only'])),
                                TextInput::make('mother_suffix')
                                    ->label('Suffix'),
                                DatePicker::make('mother_birthdate')
                                    ->label('Birth Date'),
                                Select::make('mother_relation')
                                    ->label('Relationship to Child')
                                    ->options(self::relationOptions('mother'))
                                    ->searchable(),
                                TextInput::make('mother_occupation')
                                    ->label('Occupation & Skills'),
                                Select::make('mother_educational_attainment')
                                    ->label('Highest Educational Attainment')
                                    ->options(self::educationalOptions())
                                    ->native(false),
                            ]),
                    ]),

                // Father Section
                Section::make('Father Profile')
                    ->icon('heroicon-o-user')
                    ->hidden(fn (Get $get): bool => !in_array($get('guardian_type'), ['mother_and_father', 'father_only']))
                    ->schema([
                        TextInput::make('father_firstname')
                            ->label('First Name')
                            ->required(fn (Get $get): bool => in_array($get('guardian_type'), ['mother_and_father', 'father_only'])),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('father_middlename')
                                    ->label('Middle Name'),
                                TextInput::make('father_lastname')
                                    ->label('Last Name')
                                    ->required(fn (Get $get): bool => in_array($get('guardian_type'), ['mother_and_father', 'father_only'])),
                                TextInput::make('father_suffix')
                                    ->label('Suffix'),
                                DatePicker::make('father_birthdate')
                                    ->label('Birth Date'),
                                Select::make('father_relation')
                                    ->label('Relationship to Child')
                                    ->options(self::relationOptions('father'))
                                    ->searchable(),
                                TextInput::make('father_occupation')
                                    ->label('Occupation & Skills'),
                                Select::make('father_educational_attainment')
                                    ->label('Highest Educational Attainment')
                                    ->options(self::educationalOptions())
                                    ->native(false),
                            ]),
                    ]),

                // Guardian Section
                Section::make('Guardian Profile')
                    ->icon('heroicon-o-identification')
                    ->hidden(fn (Get $get): bool => $get('guardian_type') !== 'guardian')
                    ->schema([
                        TextInput::make('guardian_firstname')
                            ->label('First Name')
                            ->required(fn (Get $get): bool => $get('guardian_type') === 'guardian'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('guardian_middlename')
                                    ->label('Middle Name'),
                                TextInput::make('guardian_lastname')
                                    ->label('Last Name')
                                    ->required(fn (Get $get): bool => $get('guardian_type') === 'guardian'),
                                TextInput::make('guardian_suffix')
                                    ->label('Suffix'),
                                DatePicker::make('guardian_birthdate')
                                    ->label('Birth Date'),
                                Select::make('guardian_relation')
                                    ->label('Relationship to Child')
                                    ->options(self::guardianRelationOptions())
                                    ->searchable(),
                                TextInput::make('guardian_occupation')
                                    ->label('Occupation & Skills'),
                                Select::make('guardian_educational_attainment')
                                    ->label('Highest Educational Attainment')
                                    ->options(self::educationalOptions())
                                    ->native(false),
                            ]),
                    ]),
            ]);
    }

    private static function stepFamilyMembers(): Step
    {
        return Step::make('Family Members')
            ->icon('heroicon-o-user-group')
            ->schema([
                Repeater::make('family_members')
                    ->label('')
                    ->schema([
                        TextInput::make('fam_member_fullname')
                            ->label('Full Name')
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('fam_member_actual_weight')
                                    ->label('Actual Weight')
                                    ->numeric()
                                    ->suffix('kg')
                                    ->minValue(0.1)
                                    ->maxValue(500)
                                    ->step(0.1)
                                    ->placeholder('70.5'),
                                Select::make('fam_member_nutrition_status')
                                    ->label('Nutritional Status')
                                    ->options([
                                        'normal' => 'Normal',
                                        'underweight' => 'Underweight',
                                        'overweight' => 'Overweight',
                                        'server_uw' => 'Severely UW',
                                    ])
                                    ->native(false),
                            ]),
                    ])
                    ->addActionLabel('Add Family Member')
                    ->collapsible()
                    ->defaultItems(0),
            ]);
    }

    private static function stepFamilyStatus(): Step
    {
        return Step::make('Family Status')
            ->icon('heroicon-o-home')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('civil_status')
                            ->label('Civil Status')
                            ->options([
                                'single' => 'Single',
                                'married' => 'Married',
                                'widowed' => 'Widowed',
                                'separated' => 'Separated',
                                'cohabiting' => 'Live-in / Cohabiting',
                            ])
                            ->native(false)
                            ->required(),
                        Select::make('type_of_marriage')
                            ->label('Type of Marriage')
                            ->options([
                                'civil' => 'Civil',
                                'church' => 'Church / Religious',
                                'common_law' => 'Common Law',
                                'none' => 'N/A',
                            ])
                            ->native(false),
                        Select::make('monthly_income')
                            ->label('Monthly Income')
                            ->options([
                                'below_5000' => 'Below ₱5,000',
                                '5000-9999' => '₱5,000 - ₱9,999',
                                '10000-14999' => '₱10,000 - ₱14,999',
                                '15000-19999' => '₱15,000 - ₱19,999',
                                '20000-above' => '₱20,000 and above',
                            ])
                            ->native(false),
                        TextInput::make('source_income')
                            ->label('Source of Income'),
                        Select::make('family_plan_method')
                            ->label('Family Planning Method')
                            ->options([
                                'natural' => 'Natural',
                                'pills' => 'Pills',
                                'condom' => 'Condom',
                                'iud' => 'IUD',
                                'ligation' => 'Ligation',
                                'vasectomy' => 'Vasectomy',
                                'none' => 'None',
                            ])
                            ->native(false),
                        Radio::make('phil_member')
                            ->label('PhilHealth Member?')
                            ->required()
                            ->inline()
                            ->options([
                                'yes' => 'Yes',
                                'no'  => 'No',
                            ]),
                    ]),

                Section::make('Housing Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Radio::make('have_electricity')
                                    ->label('Has Electricity?')
                                    ->inline()
                                    ->options([
                                        'yes' => 'Yes',
                                        'no' => 'No'
                                    ]),
                                Select::make('water_source')
                                    ->label('Source of Water')
                                    ->options([
                                        'tap' => 'Tap / Piped Water',
                                        'well' => 'Deep Well',
                                        'spring' => 'Spring',
                                        'river' => 'River / Stream',
                                        'rain' => 'Rainwater',
                                        'delivered' => 'Delivered Water',
                                    ])
                                    ->native(false),
                                Select::make('toilet_facility')
                                    ->label('Toilet Facility')
                                    ->options([
                                        'flush' => 'Water-sealed / Flush',
                                        'pit' => 'Pit Latrine',
                                        'open' => 'Open Defecation',
                                        'shared' => 'Shared Toilet',
                                    ])
                                    ->native(false),
                                Select::make('roofing')
                                    ->label('Roofing Material')
                                    ->options([
                                        'galvanized' => 'Galvanized Iron',
                                        'concrete' => 'Concrete',
                                        'nipa' => 'Nipa / Cogon',
                                        'wood' => 'Wood',
                                    ])
                                    ->native(false),
                                Select::make('walls')
                                    ->label('Wall Material')
                                    ->options([
                                        'concrete' => 'Concrete / Hollow Blocks',
                                        'wood' => 'Wood',
                                        'bamboo' => 'Bamboo',
                                        'mixed' => 'Mixed Materials',
                                    ])
                                    ->native(false),
                                Select::make('flooring')
                                    ->label('Flooring Material')
                                    ->options([
                                        'concrete' => 'Concrete',
                                        'wood' => 'Wood',
                                        'earth' => 'Earth / Soil',
                                        'tile' => 'Tile',
                                    ])
                                    ->native(false),
                            ]),
                    ]),
            ]);
    }

    private static function stepReview(): Step
    {
        return Step::make('Review')
            ->icon('heroicon-o-clipboard-document-check')
            ->schema([
                Section::make('👤 Child Information')
                    ->schema([
                        Grid::make(3)->schema([
                            Placeholder::make('r_name')
                                ->label('Full Name')
                                ->content(fn (Get $get): string => trim(
                                    ($get('firstname') ?? '') . ' ' .
                                    ($get('middlename') ?? '') . ' ' .
                                    ($get('lastname') ?? '') . ' ' .
                                    ($get('suffix') ?? '')
                                ) ?: '—'),

                            Placeholder::make('r_birthdate')
                                ->label('Date of Birth')
                                ->content(fn (Get $get): string =>
                                $get('birthdate') ? Carbon::parse($get('birthdate'))->format('F d, Y') : '—'
                                ),

                            Placeholder::make('r_age')
                                ->label('Age')
                                ->content(fn (Get $get): string => $get('age_display') ?? '—'),

                            Placeholder::make('r_sex')
                                ->label('Sex')
                                ->content(fn (Get $get): string => ucfirst($get('sex') ?? '—')),

                            Placeholder::make('r_birthplace')
                                ->label('Place of Birth')
                                ->content(fn (Get $get): string => $get('birthplace') ?? '—'),

                            Placeholder::make('r_height')
                                ->label('Height')
                                ->content(fn (Get $get): string =>
                                $get('height_cm') ? $get('height_cm') . ' cm' : '—'
                                ),

                            Placeholder::make('r_weight')
                                ->label('Weight')
                                ->content(fn (Get $get): string =>
                                $get('weight_kg') ? $get('weight_kg') . ' kg' : '—'
                                ),

                            Placeholder::make('r_nutritional_status')
                                ->label('Nutritional Status')
                                ->content(function (Get $get): HtmlString {
                                    $months = (int) $get('age_months');
                                    $weight = (float) $get('weight_kg');
                                    $height = (float) $get('height_cm');
                                    $sex    = $get('sex');
                                    if (!$months || !$weight || !$height || !in_array($sex, ['male', 'female'])) {
                                        return new HtmlString('<span class="text-gray-400">—</span>');
                                    }

                                    $status = NutritionalStatus::classify($months, $weight, $height, $sex);
                                    $class  = self::statusBadgeClass($status);

                                    return new HtmlString(
                                        "<span class=\"inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {$class}\">{$status}</span>"
                                    );
                                }),
                        ]),
                    ]),

                Section::make('👨‍👩‍👦 Guardian Information')
                    ->schema([
                        // Guardian type label
                        Placeholder::make('r_guardian_type')
                            ->label('Guardian Setup')
                            ->content(fn (Get $get): string => match ($get('guardian_type')) {
                                'mother_and_father' => '👨‍👩‍👦 Mother & Father',
                                'mother_only'       => '👩 Mother Only',
                                'father_only'       => '👨 Father Only',
                                'guardian'          => '🧑‍🤝‍🧑 Guardian',
                                default             => '—',
                            }),

                        Grid::make(2)->schema([
                            // Mother review card
                            Section::make('Mother')
                                ->hidden(fn (Get $get): bool => !in_array($get('guardian_type'), ['mother_and_father', 'mother_only']))
                                ->schema([
                                    Grid::make(2)->schema([
                                        Placeholder::make('r_mother_name')
                                            ->label('Full Name')
                                            ->content(fn (Get $get): string => trim(
                                                ($get('mother_firstname') ?? '') . ' ' .
                                                ($get('mother_middlename') ?? '') . ' ' .
                                                ($get('mother_lastname') ?? '')
                                            ) ?: '—'),
                                        Placeholder::make('r_mother_birthdate')
                                            ->label('Birth Date')
                                            ->content(fn (Get $get): string =>
                                            $get('mother_birthdate') ? Carbon::parse($get('mother_birthdate'))->format('F d, Y') : '—'
                                            ),
                                        Placeholder::make('r_mother_relation')
                                            ->label('Relationship')
                                            ->content(fn (Get $get): string =>
                                                self::relationOptions('mother')[$get('mother_relation')] ?? '—'
                                            ),
                                        Placeholder::make('r_mother_occupation')
                                            ->label('Occupation')
                                            ->content(fn (Get $get): string => $get('mother_occupation') ?? '—'),
                                        Placeholder::make('r_mother_education')
                                            ->label('Education')
                                            ->content(fn (Get $get): string =>
                                                self::educationalOptions()[$get('mother_educational_attainment')] ?? '—'
                                            ),
                                    ]),
                                ]),

                            // Father review card
                            Section::make('Father')
                                ->hidden(fn (Get $get): bool => !in_array($get('guardian_type'), ['mother_and_father', 'father_only']))
                                ->schema([
                                    Grid::make(2)->schema([
                                        Placeholder::make('r_father_name')
                                            ->label('Full Name')
                                            ->content(fn (Get $get): string => trim(
                                                ($get('father_firstname') ?? '') . ' ' .
                                                ($get('father_middlename') ?? '') . ' ' .
                                                ($get('father_lastname') ?? '')
                                            ) ?: '—'),
                                        Placeholder::make('r_father_birthdate')
                                            ->label('Birth Date')
                                            ->content(fn (Get $get): string =>
                                            $get('father_birthdate') ? Carbon::parse($get('father_birthdate'))->format('F d, Y') : '—'
                                            ),
                                        Placeholder::make('r_father_relation')
                                            ->label('Relationship')
                                            ->content(fn (Get $get): string =>
                                                self::relationOptions('father')[$get('father_relation')] ?? '—'
                                            ),
                                        Placeholder::make('r_father_occupation')
                                            ->label('Occupation')
                                            ->content(fn (Get $get): string => $get('father_occupation') ?? '—'),
                                        Placeholder::make('r_father_education')
                                            ->label('Education')
                                            ->content(fn (Get $get): string =>
                                                self::educationalOptions()[$get('father_educational_attainment')] ?? '—'
                                            ),
                                    ]),
                                ]),

                            // Guardian review card
                            Section::make('Guardian')
                                ->hidden(fn (Get $get): bool => $get('guardian_type') !== 'guardian')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Placeholder::make('r_guardian_name')
                                            ->label('Full Name')
                                            ->content(fn (Get $get): string => trim(
                                                ($get('guardian_firstname') ?? '') . ' ' .
                                                ($get('guardian_middlename') ?? '') . ' ' .
                                                ($get('guardian_lastname') ?? '')
                                            ) ?: '—'),
                                        Placeholder::make('r_guardian_birthdate')
                                            ->label('Birth Date')
                                            ->content(fn (Get $get): string =>
                                            $get('guardian_birthdate') ? Carbon::parse($get('guardian_birthdate'))->format('F d, Y') : '—'
                                            ),
                                        Placeholder::make('r_guardian_relation')
                                            ->label('Relationship')
                                            ->content(fn (Get $get): string =>
                                                self::guardianRelationOptions()[$get('guardian_relation')] ?? '—'
                                            ),
                                        Placeholder::make('r_guardian_occupation')
                                            ->label('Occupation')
                                            ->content(fn (Get $get): string => $get('guardian_occupation') ?? '—'),
                                        Placeholder::make('r_guardian_education')
                                            ->label('Education')
                                            ->content(fn (Get $get): string =>
                                                self::educationalOptions()[$get('guardian_educational_attainment')] ?? '—'
                                            ),
                                    ]),
                                ]),

                            Section::make('👨‍👩‍👧‍👦 Family Members')
                                ->columnSpanFull()
                                ->schema([
                                    Placeholder::make('r_family_members')
                                        ->label('')
                                        ->hiddenLabel()
                                        ->content(function (Get $get): HtmlString {
                                            $members = $get('family_members') ?? [];

                                            if (empty($members)) {
                                                return new HtmlString(
                                                    '<span class="text-sm text-gray-400 italic">No family members added.</span>'
                                                );
                                            }
                                            $nutritionLabels = [
                                                'normal'      => 'Normal',
                                                'underweight' => 'Underweight',
                                                'overweight'  => 'Overweight',
                                                'server_uw'   => 'Severely UW',
                                            ];

                                            $rows = '';
                                            foreach ($members as $member) {
                                                $name   = $member['fam_member_fullname'] ?? '—';
                                                $weight = isset($member['fam_member_actual_weight'])
                                                    ? $member['fam_member_actual_weight'] . ' kg'
                                                    : '—';
                                                $status = $nutritionLabels[$member['fam_member_nutrition_status'] ?? ''] ?? '—';

                                                $rows .= "<tr style=\"border-bottom: 1px solid #e5e7eb;\">
                                                            <td style=\"padding: 8px 0; font-size: 14px; width: 33%;\">{$name}</td>
                                                            <td style=\"padding: 8px 0; font-size: 14px; width: 33%;\">{$weight}</td>
                                                            <td style=\"padding: 8px 0; font-size: 14px; width: 33%;\">{$status}</td>
                                                        </tr>";
                                            }

                                            return new HtmlString("<div style=\"overflow-x: auto;\">
                                                                        <table style=\"width: 100%; table-layout: fixed; border-collapse: collapse;\">
                                                                            <thead>
                                                                                <tr style=\"border-bottom: 2px solid #d1d5db;\">
                                                                                    <th style=\"padding-bottom: 8px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; width: 33%;\">Full Name</th>
                                                                                    <th style=\"padding-bottom: 8px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; width: 33%;\">Weight</th>
                                                                                    <th style=\"padding-bottom: 8px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; width: 33%;\">Nutritional Status</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                {$rows}
                                                                            </tbody>
                                                                        </table>
                                                                    </div>");
                                        }),
                                ]),
                        ]),
                    ]),

                Section::make('🏠 Family Status')
                    ->schema([
                        Grid::make(3)->schema([
                            Placeholder::make('r_civil_status')
                                ->label('Civil Status')
                                ->content(fn (Get $get): string => match ($get('civil_status')) {
                                    'single' => 'Single',
                                    'married' => 'Married',
                                    'widowed' => 'Widowed',
                                    'separated' => 'Separated',
                                    'cohabiting' => 'Live-in / Cohabiting',
                                    default => '—',
                                }),
                            Placeholder::make('r_monthly_income')
                                ->label('Monthly Income')
                                ->content(fn (Get $get): string => match ($get('monthly_income')) {
                                    'below_5000' => 'Below ₱5,000',
                                    '5000-9999' => '₱5,000 - ₱9,999',
                                    '10000-14999' => '₱10,000 - ₱14,999',
                                    '15000-19999' => '₱15,000 - ₱19,999',
                                    '20000-above' => '₱20,000 and above',
                                    default => '—',
                                }),
                            Placeholder::make('r_source_income')
                                ->label('Source of Income')
                                ->content(fn (Get $get): string => $get('source_income') ?? '—'),
                            Placeholder::make('r_phil_member')
                                ->label('PhilHealth Member?')
                                ->content(fn (Get $get): string => match ($get('phil_member')) {
                                    'yes' => 'Yes', 'no' => 'No', default => '—',
                                }),
                            Placeholder::make('r_electricity')
                                ->label('Has Electricity?')
                                ->content(fn (Get $get): string => match ($get('have_electricity')) {
                                    'yes' => 'Yes', 'no' => 'No', default => '—',
                                }),
                            Placeholder::make('r_water_source')
                                ->label('Water Source')
                                ->content(fn (Get $get): string => match ($get('water_source')) {
                                    'tap' => 'Tap / Piped Water',
                                    'well' => 'Deep Well',
                                    'spring' => 'Spring',
                                    'river' => 'River / Stream',
                                    'rain' => 'Rainwater',
                                    'delivered' => 'Delivered Water',
                                    default => '—',
                                }),
                            Placeholder::make('r_toilet')
                                ->label('Toilet Facility')
                                ->content(fn (Get $get): string => match ($get('toilet_facility')) {
                                    'flush' => 'Water-sealed / Flush',
                                    'pit' => 'Pit Latrine',
                                    'open' => 'Open Defecation',
                                    'shared' => 'Shared Toilet',
                                    default => '—',
                                }),
                            Placeholder::make('r_roofing')
                                ->label('Roofing')
                                ->content(fn (Get $get): string => match ($get('roofing')) {
                                    'galvanized' => 'Galvanized Iron',
                                    'concrete' => 'Concrete',
                                    'nipa' => 'Nipa / Cogon',
                                    'wood' => 'Wood',
                                    default => '—',
                                }),
                            Placeholder::make('r_walls')
                                ->label('Wall Material')
                                ->content(fn (Get $get): string => match ($get('walls')) {
                                    'concrete' => 'Concrete / Hollow Blocks',
                                    'wood' => 'Wood',
                                    'bamboo' => 'Bamboo',
                                    'mixed' => 'Mixed Materials',
                                    default => '—',
                                }),
                            Placeholder::make('r_flooring')
                                ->label('Flooring')
                                ->content(fn (Get $get): string => match ($get('flooring')) {
                                    'concrete' => 'Concrete',
                                    'wood' => 'Wood',
                                    'earth' => 'Earth / Soil',
                                    'tile' => 'Tile',
                                    default => '—',
                                }),
                        ]),
                    ]),
            ]);
    }

    private static function statusBadgeClass(string $status): string
    {
        $colorMap = [
            'SUW' => 'bg-red-100 text-red-700 border border-red-300',
            'SST' => 'bg-red-100 text-red-700 border border-red-300',
            'Wasted' => 'bg-red-100 text-red-700 border border-red-300',
            'At Risk' => 'bg-red-100 text-red-700 border border-red-300',
            'OB' => 'bg-red-100 text-red-700 border border-red-300',
            'OW' => 'bg-blue-100 text-blue-700 border border-blue-300',
            'UW' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
            'ST' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
            'MW' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
        ];

        foreach ($colorMap as $key => $css) {
            if (str_contains($status, $key)) {
                return $css;
            }
        }
        return 'bg-green-100 text-green-700 border border-green-300';
    }

    private static function relationOptions(string $type = 'mother'): array
    {
        if ($type === 'father') {
            return [
                'biological_father' => 'Biological Father',
                'adoptive_father' => 'Adoptive Father',
                'grandfather' => 'Grandfather',
                'uncle' => 'Uncle',
                'older_sibling' => 'Older Sibling',
                'legal_guardian' => 'Legal Guardian',
                'foster_parent' => 'Foster Parent',
                'court_appointed' => 'Court-Appointed Guardian',
                'family_friend' => 'Family Friend',
                'other' => 'Other',
            ];
        }

        return [
            'biological_mother' => 'Biological Mother',
            'adoptive_mother' => 'Adoptive Mother',
            'grandmother' => 'Grandmother',
            'aunt' => 'Aunt',
            'older_sibling' => 'Older Sibling',
            'legal_guardian' => 'Legal Guardian',
            'foster_parent' => 'Foster Parent',
            'court_appointed' => 'Court-Appointed Guardian',
            'family_friend' => 'Family Friend',
            'other' => 'Other',
        ];
    }

    private static function guardianRelationOptions(): array
    {
        return [
            'legal_guardian' => 'Legal Guardian',
            'foster_parent' => 'Foster Parent',
            'court_appointed' => 'Court-Appointed Guardian',
            'grandfather' => 'Grandfather',
            'grandmother' => 'Grandmother',
            'uncle' => 'Uncle',
            'aunt' => 'Aunt',
            'older_sibling' => 'Older Sibling',
            'family_friend' => 'Family Friend',
            'adoptive_parent' => 'Adoptive Parent',
            'other' => 'Other',
        ];
    }

    private static function educationalOptions(): array
    {
        return [
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
        ];
    }
}
