<?php

namespace App\Filament\Resources\AdoptedChildren\Schemas;

use App\Helpers\NutritionalStatus;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

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
        ];
    }

    public static function afterCreate($record, array $data): void
    {
        self::syncNutritionalStatus($record);
        // Mother
        $record->familyProfiles()->create([
            'type' => 'mother',
            'firstname' => $data['mother_firstname'],
            'lastname' => $data['mother_lastname'],
            'middlename' => $data['mother_middlename'],
            'suffix' => $data['mother_suffix'] ?? null,
            'birthdate' => $data['mother_birthdate'],
            'relation' => $data['mother_relation'],
            'occupation' => $data['mother_occupation'],
            'educational_attainment' => $data['mother_educational_attainment'],
        ]);
        // Father
        $record->familyProfiles()->create([
            'type' => 'father',
            'firstname' => $data['father_firstname'],
            'lastname' => $data['father_lastname'],
            'middlename' => $data['father_middlename'],
            'suffix' => $data['father_suffix'] ?? null,
            'birthdate' => $data['father_birthdate'],
            'relation' => $data['father_relation'],
            'occupation' => $data['father_occupation'],
            'educational_attainment' => $data['father_educational_attainment'],
        ]);
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
        ]);
    }

    public static function afterEdit($record): void
    {
        self::syncNutritionalStatus($record);
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
                $record->sex ?? 'male'
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
            TextInput::make('firstname')
                ->label('First Name')
                ->required()
                ->maxLength(255),

            Grid::make(2)
                ->schema([
                    TextInput::make('middlename')
                        ->label('Middle Name'),
                    TextInput::make('lastname')
                        ->label('Last Name')
                        ->required(),
                    TextInput::make('suffix')
                        ->label('Suffix'),
                    DatePicker::make('birthdate')
                        ->label('Date of Birth')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                $age         = Carbon::parse($state)->diff(now());
                                $totalMonths = ($age->y * 12) + $age->m;
                                $set('age_months', $totalMonths);
                                $set('age_display', $age->y . 'y ' . $age->m . 'm (' . $totalMonths . ' months)');
                            }
                        }),
                    TextInput::make('birthplace')
                        ->label('Place of Birth')
                        ->required(),
                    Select::make('sex')
                        ->label('Sex')
                        ->required()
                        ->options([
                            'male'   => 'Male',
                            'female' => 'Female',
                        ]),
                ]),

            Grid::make(3)
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
                        ->maxValue(250),

                    TextInput::make('weight_kg')
                        ->label('Weight')
                        ->suffix('kg')
                        ->numeric()
                        ->required()
                        ->live()
                        ->minValue(1)
                        ->maxValue(300),
                ]),

            Placeholder::make('nutritional_status_preview')
                ->label('Nutritional Status Preview')
                ->content(function (Get $get): \Illuminate\Support\HtmlString {
                    $months = (int) $get('age_months');
                    $weight = (float) $get('weight_kg');
                    $height = (float) $get('height_cm');
                    $sex    = $get('sex') ?? 'male';

                    if (!$months || !$weight || !$height) {
                        return new \Illuminate\Support\HtmlString(
                            '<span class="text-sm text-gray-400 italic">Fill in birthdate, weight, and height to see status</span>'
                        );
                    }

                    $status = NutritionalStatus::classify($months, $weight, $height, $sex);
                    $colorMap = [
                        'SUW' => 'bg-red-100 text-red-700 border border-red-300',
                        'SST' => 'bg-red-100 text-red-700 border border-red-300',
                        'Wasted'  => 'bg-red-100 text-red-700 border border-red-300',
                        'At Risk' => 'bg-red-100 text-red-700 border border-red-300',
                        'OB' => 'bg-red-100 text-red-700 border border-red-300',
                        'OW' => 'bg-blue-100 text-blue-700 border border-blue-300',
                        'UW' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                        'ST' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                        'MW' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                    ];

                    $class = 'bg-green-100 text-green-700 border border-green-300';
                    foreach ($colorMap as $key => $css) {
                        if (str_contains($status, $key)) {
                            $class = $css;
                            break;
                        }
                    }

                    return new \Illuminate\Support\HtmlString(
                        "<span class=\"inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {$class}\">{$status}</span>"
                    );
                }),

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
                Grid::make(2)
                    ->schema([
                        Section::make('Mother Profile')
                            ->schema([
                                TextInput::make('mother_firstname')
                                    ->label('First Name')
                                    ->required(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('mother_middlename')
                                            ->label('Middle Name')
                                            ->required(),
                                        TextInput::make('mother_lastname')
                                            ->label('Last Name')
                                            ->required(),
                                        TextInput::make('mother_suffix')
                                            ->label('Suffix'),
                                        DatePicker::make('mother_birthdate')
                                            ->label('Birth Date')
                                            ->required(),
                                        Select::make('mother_relation')
                                            ->label('Relationship to Child')
                                            ->options(self::relationOptions())
                                            ->searchable(),
                                        TextInput::make('mother_occupation')
                                            ->label('Occupation & Skills'),
                                        Select::make('mother_educational_attainment')
                                            ->label('Highest Educational Attainment')
                                            ->options(self::educationalOptions())
                                            ->native(false),
                                    ]),
                            ]),

                        Section::make('Father Profile')
                            ->schema([
                                TextInput::make('father_firstname')
                                    ->label('First Name')
                                    ->required(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('father_middlename')
                                            ->label('Middle Name')
                                            ->required(),
                                        TextInput::make('father_lastname')
                                            ->label('Last Name')
                                            ->required(),
                                        TextInput::make('father_suffix')
                                            ->label('Suffix'),
                                        DatePicker::make('father_birthdate')
                                            ->label('Birth Date')
                                            ->required(),
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
                        Select::make('phil_member')
                            ->label('PhilHealth Member?')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->native(false),
                    ]),

                Section::make('Housing Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('have_electricity')
                                    ->label('Has Electricity?')
                                    ->options(['yes' => 'Yes', 'no' => 'No'])
                                    ->native(false),
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
