<?php

namespace App\Filament\Resources\AdoptedChildren\Tables;

use App\Helpers\NutritionalStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;


class AdoptedChildrenTable
{
    private static function getNutritionalStatus($record): string
    {
        if (!$record->birthdate || !$record->weight_kg || !$record->height_cm) {
            return 'Incomplete Data';
        }

        $age = \Carbon\Carbon::parse($record->birthdate)->diff(now());
        $ageMonths = ($age->y * 12) + $age->m;

        return \App\Helpers\NutritionalStatus::classify(
            $ageMonths,
            (float) $record->weight_kg,
            (float) $record->height_cm,
            $record->sex ?? 'male'
        );
    }

    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                ImageColumn::make('profile_path')
                    ->label('Profile')
                    ->circular()
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?' . http_build_query([
                            'name'       => $record->firstname . ' ' . $record->lastname,
                            'background' => '6366f1',
                            'color'      => 'fff',
                            'size'       => '128',
                            'bold'       => 'true',
                            'rounded'    => 'true',
                        ])
                    ),
                TextColumn::make('firstname')
                    ->label('Name')
                    ->searchable(['firstname', 'lastname'])
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) => $record->firstname. ' ' .$record->lastname ),

                TextColumn::make('birthdate')
                    ->label('Age by year & month')
                    ->sortable()
                    ->formatStateUsing(fn ($record) =>
                    $record->birthdate
                        ? (function () use ($record) {
                        $age = \Carbon\Carbon::parse($record->birthdate)->diff(now());
                        $months = ($age->y * 12) + $age->m;
                        return $age->y . 'y ' . $age->m . 'm (' . $months . ' months)';
                    })()
                        : 'N/A'
                    ),
                TextColumn::make('height_cm')
                    ->label('Height')
                    ->suffix('cm')
                    ->numeric(),

                TextColumn::make('weight_kg')
                    ->label('Weight')
                    ->suffix('kg')
                    ->numeric(),

                TextColumn::make('nutritional_status')
                ->sortable()
                    ->label('Nutritional Status')
                    ->badge()
                    ->formatStateUsing(fn ($record) => self::getNutritionalStatus($record))
                    ->color(function (string $state): string {
                        return match (true) {
                            str_contains($state, 'SUW')        => 'danger',
                            str_contains($state, 'SST')        => 'danger',
                            str_contains($state, 'MW')         => 'warning',
                            str_contains($state, 'OB')         => 'danger',
                            str_contains($state, 'OW')         => 'info',
                            str_contains($state, 'Wasted')     => 'danger',
                            str_contains($state, 'UW')         => 'warning',
                            str_contains($state, 'ST')         => 'warning',
                            str_contains($state, 'At Risk')    => 'danger',
                            str_contains($state, 'Invalid')    => 'danger',
                            str_contains($state, 'Incomplete') => 'gray',
                            default                            => 'success',
                        };
                    }),
            ])
            ->filters([
                SelectFilter::make('nutritional_status')
                    ->label('Nutritional Status')
                    ->options([
                        'Normal (N)'                  => 'Normal',

                        'UW — Underweight'            => 'Underweight (UW)',
                        'SUW — Severely Underweight'  => 'Severely Underweight (SUW)',

                        'ST — Stunted'                => 'Stunted (ST)',
                        'SST — Severely Stunted'      => 'Severely Stunted (SST)',

                        'MW — Moderately Wasted'      => 'Moderately Wasted (MW)',
                        'W — Wasted'                  => 'Wasted (W)',

                        'At Risk of Overweight'       => 'At Risk of Overweight',
                        'OW — Overweight'             => 'Overweight (OW)',
                        'OB — Obese'                  => 'Obese (OB)',
                    ])
                    ->placeholder('All Statuses')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->color('info')->iconButton(),
                EditAction::make()
                    ->iconButton()
                    ->after(function ($record) {
                        if ($record->birthdate && $record->weight_kg && $record->height_cm) {
                            $age = \Carbon\Carbon::parse($record->birthdate)->diff(now());
                            $ageMonths = ($age->y * 12) + $age->m;

                            $record->update([
                                'nutritional_status' => \App\Helpers\NutritionalStatus::classify(
                                    $ageMonths,
                                    (float) $record->weight_kg,
                                    (float) $record->height_cm,
                                    $record->sex ?? 'male'
                                ),
                            ]);
                        }
                    }),
                DeleteAction::make()->iconButton(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-s-plus')
                    ->createAnother(false)
                    ->modalSubmitActionLabel('Save')
                    ->modalCancelActionLabel('Discard')
                    ->steps([
                        Step::make('Child Information')
                            ->icon('heroicon-o-user')
                            ->schema([
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
                                                    $age = \Carbon\Carbon::parse($state)->diff(now());
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
                                                'male' => 'Male',
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
                                        $sex    = $get('sex') ?? 'combined';

                                        if (!$months || !$weight || !$height) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<span class="text-sm text-gray-400 italic">Fill in birthdate, weight, and height to see status</span>'
                                            );
                                        }

                                        $status = \App\Helpers\NutritionalStatus::classify($months, $weight, $height, $sex);

                                        $colorMap = [
                                            'SUW' => 'bg-red-100 text-red-700 border border-red-300',
                                            'UW'  => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                                            'SST' => 'bg-red-100 text-red-700 border border-red-300',
                                            'ST'  => 'bg-orange-100 text-orange-700 border border-orange-300',
                                            'W —' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
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
                            ]),

                        Step::make('Guardian Information')
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
                                                            ->options([
                                                                'biological_mother'     => 'Biological Mother',

                                                                // Adoptive
                                                                'adoptive_mother'       => 'Adoptive Mother',

                                                                // Extended Family
                                                                'grandmother'           => 'Grandmother',
                                                                'aunt'                  => 'Aunt',
                                                                'older_sibling'         => 'Older Sibling',

                                                                // Legal
                                                                'legal_guardian'        => 'Legal Guardian',
                                                                'foster_parent'         => 'Foster Parent',
                                                                'court_appointed'       => 'Court-Appointed Guardian',

                                                                'family_friend'         => 'Family Friend',
                                                                'other'                 => 'Other',
                                                            ])
                                                            ->searchable(),
                                                        TextInput::make('mother_occupation')
                                                            ->label('Occupation & Skills'),
                                                        Select::make('mother_educational_attainment')
                                                            ->label('Highest Educational Attainment')
                                                            ->options([
                                                                // No Formal Education
                                                                'no_formal_education'       => 'No Formal Education',

                                                                // Elementary
                                                                'elementary_undergraduate'  => 'Elementary Undergraduate',
                                                                'elementary_graduate'       => 'Elementary Graduate',

                                                                // Junior High School
                                                                'jhs_undergraduate'         => 'Junior High School Undergraduate',
                                                                'jhs_graduate'              => 'Junior High School Graduate (Grade 10)',

                                                                // Senior High School
                                                                'shs_undergraduate'         => 'Senior High School Undergraduate',
                                                                'shs_graduate'              => 'Senior High School Graduate (Grade 12)',

                                                                // Vocational / Technical
                                                                'vocational'                => 'Vocational / Technical Course',

                                                                // College
                                                                'college_undergraduate'     => 'College Undergraduate',
                                                                'college_graduate'          => 'College Graduate',

                                                                // Post Graduate
                                                                'masters'                   => "Master's Degree",
                                                                'doctorate'                 => 'Doctorate Degree',
                                                            ])
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
                                                            ->options([
                                                                // Biological
                                                                'biological_father'     => 'Biological Father',

                                                                // Adoptive
                                                                'adoptive_father'       => 'Adoptive Father',

                                                                // Extended Family
                                                                'grandfather'           => 'Grandfather',
                                                                'uncle'                  => 'Uncle',
                                                                'older_sibling'         => 'Older Sibling',

                                                                // Legal
                                                                'legal_guardian'        => 'Legal Guardian',
                                                                'foster_parent'         => 'Foster Parent',
                                                                'court_appointed'       => 'Court-Appointed Guardian',

                                                                'family_friend'         => 'Family Friend',
                                                                'other'                 => 'Other',
                                                            ])
                                                            ->searchable(),
                                                        TextInput::make('father_occupation')
                                                            ->label('Occupation & Skills'),
                                                        Select::make('father_educational_attainment')
                                                            ->label('Highest Educational Attainment')
                                                            ->options([
                                                                // No Formal Education
                                                                'no_formal_education'       => 'No Formal Education',

                                                                // Elementary
                                                                'elementary_undergraduate'  => 'Elementary Undergraduate',
                                                                'elementary_graduate'       => 'Elementary Graduate',

                                                                // Junior High School
                                                                'jhs_undergraduate'         => 'Junior High School Undergraduate',
                                                                'jhs_graduate'              => 'Junior High School Graduate (Grade 10)',

                                                                // Senior High School
                                                                'shs_undergraduate'         => 'Senior High School Undergraduate',
                                                                'shs_graduate'              => 'Senior High School Graduate (Grade 12)',

                                                                // Vocational / Technical
                                                                'vocational'                => 'Vocational / Technical Course',

                                                                // College
                                                                'college_undergraduate'     => 'College Undergraduate',
                                                                'college_graduate'          => 'College Graduate',

                                                                // Post Graduate
                                                                'masters'                   => "Master's Degree",
                                                                'doctorate'                 => 'Doctorate Degree',
                                                            ])
                                                            ->native(false),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        Step::make('Family Members')
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
                                                        'normal'      => 'Normal',
                                                        'underweight' => 'Underweight',
                                                        'overweight'  => 'Overweight',
                                                        'server_uw'   => 'Severely UW',
                                                    ])
                                                    ->native(false),
                                            ]),
                                    ])
                                    ->addActionLabel('Add Family Member')
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),
                        Step::make('Family Status')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('civil_status')
                                            ->label('Civil Status')
                                            ->options([
                                                'single'   => 'Single',
                                                'married'  => 'Married',
                                                'widowed'  => 'Widowed',
                                                'separated'=> 'Separated',
                                                'cohabiting'=> 'Live-in / Cohabiting',
                                            ])
                                            ->native(false)
                                            ->required(),
                                        Select::make('type_of_marriage')
                                            ->label('Type of Marriage')
                                            ->options([
                                                'civil'     => 'Civil',
                                                'church'    => 'Church / Religious',
                                                'common_law'=> 'Common Law',
                                                'none'      => 'N/A',
                                            ])
                                            ->native(false),
                                        Select::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->options([
                                                'below_5000'  => 'Below ₱5,000',
                                                '5000-9999'   => '₱5,000 - ₱9,999',
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
                                                'natural'    => 'Natural',
                                                'pills'      => 'Pills',
                                                'condom'     => 'Condom',
                                                'iud'        => 'IUD',
                                                'ligation'   => 'Ligation',
                                                'vasectomy'  => 'Vasectomy',
                                                'none'       => 'None',
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
                                                    ->options([
                                                        'yes' => 'Yes',
                                                        'no' => 'No'])
                                                    ->native(false),
                                                Select::make('water_source')
                                                    ->label('Source of Water')
                                                    ->options([
                                                        'tap'       => 'Tap / Piped Water',
                                                        'well'      => 'Deep Well',
                                                        'spring'    => 'Spring',
                                                        'river'     => 'River / Stream',
                                                        'rain'      => 'Rainwater',
                                                        'delivered' => 'Delivered Water',
                                                    ])
                                                    ->native(false),
                                                Select::make('toilet_facility')
                                                    ->label('Toilet Facility')
                                                    ->options([
                                                        'flush'       => 'Water-sealed / Flush',
                                                        'pit'         => 'Pit Latrine',
                                                        'open'        => 'Open Defecation',
                                                        'shared'      => 'Shared Toilet',
                                                    ])
                                                    ->native(false),
                                                Select::make('roofing')
                                                    ->label('Roofing Material')
                                                    ->options([
                                                        'galvanized' => 'Galvanized Iron',
                                                        'concrete'   => 'Concrete',
                                                        'nipa'       => 'Nipa / Cogon',
                                                        'wood'       => 'Wood',
                                                    ])
                                                    ->native(false),
                                                Select::make('walls')
                                                    ->label('Wall Material')
                                                    ->options([
                                                        'concrete' => 'Concrete / Hollow Blocks',
                                                        'wood'     => 'Wood',
                                                        'bamboo'   => 'Bamboo',
                                                        'mixed'    => 'Mixed Materials',
                                                    ])
                                                    ->native(false),
                                                Select::make('flooring')
                                                    ->label('Flooring Material')
                                                    ->options([
                                                        'concrete' => 'Concrete',
                                                        'wood'     => 'Wood',
                                                        'earth'    => 'Earth / Soil',
                                                        'tile'     => 'Tile',
                                                    ])
                                                    ->native(false),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->after(function ($record, array $data) {
                        if ($record->birthdate && $record->weight_kg && $record->height_cm) {
                            $age = \Carbon\Carbon::parse($record->birthdate)->diff(now());
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

                        // Mother
                        $record->familyProfiles()->create([
                            'type'                   => 'mother',
                            'firstname'              => $data['mother_firstname'],
                            'lastname'               => $data['mother_lastname'],
                            'middlename'             => $data['mother_middlename'],
                            'suffix'                 => $data['mother_suffix'] ?? null,
                            'birthdate'              => $data['mother_birthdate'],
                            'relation'               => $data['mother_relation'],
                            'occupation'             => $data['mother_occupation'],
                            'educational_attainment' => $data['mother_educational_attainment'],
                        ]);

                        // Father
                        $record->familyProfiles()->create([
                            'type'                   => 'father',
                            'firstname'              => $data['father_firstname'],
                            'lastname'               => $data['father_lastname'],
                            'middlename'             => $data['father_middlename'],
                            'suffix'                 => $data['father_suffix'] ?? null,
                            'birthdate'              => $data['father_birthdate'],
                            'relation'               => $data['father_relation'],
                            'occupation'             => $data['father_occupation'],
                            'educational_attainment' => $data['father_educational_attainment'],
                        ]);

                        // Family Members
                        if (!empty($data['family_members'])) {
                            foreach ($data['family_members'] as $member) {
                                $record->familyProfiles()->create([
                                    'type'                        => 'fam_member',
                                    'fam_member_fullname'         => $member['fam_member_fullname'],
                                    'fam_member_actual_weight'    => $member['fam_member_actual_weight'] ?? null,
                                    'fam_member_nutrition_status' => $member['fam_member_nutrition_status'] ?? null,
                                ]);
                            }
                        }
                        $record->familyStatus()->create([
                            'status'              => $data['civil_status'],
                            'type_of_marriage'    => $data['type_of_marriage'],
                            'monthly_income'      => $data['monthly_income'],
                            'source_income'       => $data['source_income'],
                            'phil_member'         => $data['phil_member'],
                            'family_plan_method'  => $data['family_plan_method'],
                            'have_electricity'    => $data['have_electricity'],
                            'water_source'        => $data['water_source'],
                            'toilet_facility'     => $data['toilet_facility'],
                            'roofing'             => $data['roofing'],
                            'walls'               => $data['walls'],
                            'flooring'            => $data['flooring'],
                        ]);
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
