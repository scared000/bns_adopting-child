<?php

namespace App\Filament\Resources\AdoptedChildren\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;


class AdoptedChildrenTable
{
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
                    ->formatStateUsing(fn ($record) => $record->firstname. ' ' .$record->middlename. ' ' .$record->lastname )
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-s-plus')
                    ->createAnother(false)
                    ->modalSubmitActionLabel('Save')
                    ->modalCancelActionLabel('Cancel')
                    ->schema([
                        Section::make('Adopted children Profile')
                        ->schema([
                            TextInput::make('firstname')
                                ->label('First Name')
                                ->required()
                                ->maxLength(255),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('middlename')
                                        ->label('Middle Name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('lastname')
                                        ->label('Last Name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('suffix')
                                        ->label('Suffix')
                                        ->placeholder('e.g. Jr.')
                                        ->suffix('Jr./Sr.'),
                                    DatePicker::make('birthdate')
                                        ->label('Date Of Birth')
                                        ->required(),
                                    TextInput::make('birthplace')
                                        ->label('Place of Birth')
                                        ->required(),


                                ]),
                            FileUpload::make('profile_path')
                                ->label('Adopted Children Picture')
                                ->disk('public')
                                ->directory('child/adopted-children')
                                ->visibility('public'),
                        ]),

                        Section::make('Guardian Profile')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Mother Profile ')
                                            ->schema([
                                                TextInput::make('mother_firstname')
                                                    ->label('First Name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('mother_middlename')
                                                            ->label('Middle Name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('mother_lastname')
                                                            ->label('Last Name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('mother_suffix')
                                                            ->label('Suffix')
                                                            ->placeholder('e.g. Jr.')
                                                            ->suffix('Jr./Sr.'),
                                                        DatePicker::make('mother_birthdate')
                                                            ->label('Birth Date')
                                                            ->required(),
                                                        Select::make('mother_relation')
                                                            ->label('Relationship to Child')
                                                            ->required()
                                                            ->options([
                                                                // Biological
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
                                                            ->label('Occupation & Skills')
                                                            ->required(),
                                                        Select::make('mother_educational_attainment')
                                                            ->label('Educational Attainment')
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
                                                            ->searchable()
                                                            ->native(false),

                                                    ]),

                                            ]),
                                        Section::make('Father Profile ')
                                            ->schema([
                                                TextInput::make('father_firstname')
                                                    ->label('First Name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('father_lastname')
                                                            ->label('Last Name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('father_middlename')
                                                            ->label('Middle Name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('father_suffix')
                                                            ->label('Suffix')
                                                            ->placeholder('e.g. Jr.')
                                                            ->suffix('Jr./Sr.'),
                                                        DatePicker::make('father_birthdate')
                                                            ->label('Birth Date')
                                                            ->required(),
                                                        Select::make('father_relation')
                                                            ->label('Relationship to Child')
                                                            ->required()
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
                                                            ->label('Occupation & Skills')
                                                            ->required(),
                                                        Select::make('father_educational_attainment')
                                                            ->label('Educational Attainment')
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
                                                            ->searchable()
                                                            ->native(false),

                                                    ]),
                                            ]),
                                        Section::make('Family Members')
                                            ->columnSpanFull()
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
                                                                    ->label('Actual Weight'),
                                                                Select::make('fam_member_nutrition_status')
                                                                    ->label('Nutritional Status')
                                                                    ->options([
                                                                        'normal'      => 'Normal',
                                                                        'underweight' => 'Underweight',
                                                                        'overweight'  => 'Overweight',
                                                                        'server_uw'       => 'Severely UW',
                                                                    ])
                                                                    ->native(false),
                                                            ]),
                                                    ])
                                                    ->addActionLabel('Add Family Member')
                                                    ->collapsible()
                                                    ->defaultItems(0),
                                            ]),
                                    ]),
                            ])
                    ])
                    ->slideOver()
                    ->after(function ($record, array $data) {
                        $record->familyProfiles()->create([
                            'firstname'                     => $data['mother_firstname'],
                            'lastname'                      => $data['mother_lastname'],
                            'middlename'                    => $data['mother_middlename'],
                            'suffix'                        => $data['mother_suffix'],
                            'birthdate'                     => $data['mother_birthdate'],
                            'relation'                      => $data['mother_relation'],
                            'occupation'                    => $data['mother_occupation'],
                            'educational_attainment'=> $data['mother_educational_attainment'],
                        ]);

                        $record->familyMembers()->create([
                            'firstname'                     => $data['father_firstname'],
                            'lastname'                      => $data['father_lastname'],
                            'middlename'                    => $data['father_middlename'],
                            'suffix'                        => $data['father_suffix'],
                            'birthdate'                     => $data['father_birthdate'],
                            'relation'                      => $data['father_relation'],
                            'occupation'                    => $data['father_occupation'],
                            'educational_attainment'=> $data['father_educational_attainment'],
                        ]);

                        if (!empty($data['family_members'])) {
                            foreach ($data['family_members'] as $member) {
                                $record->familyProfiles()->create([
                                    'type'                       => 'fam_member',
                                    'fam_member_fullname'        => $member['fam_member_fullname'],
                                    'fam_member_actual_weight'   => $member['fam_member_actual_weight'],
                                    'fam_member_nutrition_status'=> $member['fam_member_nutrition_status'],
                                ]);
                            }
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
