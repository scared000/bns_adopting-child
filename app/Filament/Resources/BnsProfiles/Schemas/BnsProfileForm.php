<?php

namespace App\Filament\Resources\BnsProfiles\Schemas;

use App\Models\Barangay;
use App\Models\BnsProfile;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BnsProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('BNS Profile')
                ->tabs([
                    // Tab 1: Personal Information
                    Tab::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('User Account')
                                ->description('Link this profile to a registered BNS user account.')
                                ->schema([
                                    Select::make('user_id')
                                        ->label('User Account')
                                        ->relationship(
                                            name: 'user',
                                            titleAttribute: 'firstname',
                                            modifyQueryUsing: function (Builder $query, $record) {
                                                $query->whereHas('roles', fn ($q) => $q->where('name', 'bns'));

                                                // On edit, allow the currently assigned user through
                                                // On create, exclude anyone who already has a profile
                                                if ($record?->user_id) {
                                                    $query->where(function ($q) use ($record) {
                                                        $q->whereDoesntHave('bnsProfile')
                                                            ->orWhere('id', $record->user_id);  // ← include current user
                                                    });
                                                } else {
                                                    $query->whereDoesntHave('bnsProfile');
                                                }
                                            }
                                        )
                                        ->getOptionLabelFromRecordUsing(
                                            fn (User $record) => trim("{$record->lastname}, {$record->firstname} {$record->middlename}")
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            if (! $state) {
                                                return;
                                            }

                                            $user = User::find($state);

                                            if (! $user) {
                                                return;
                                            }

                                            // Auto-fill from user's existing data
                                            $set('municipality_id', $user->municipality_id);
                                            $set('barangay_id', $user->barangay_id);

                                            // Auto-fill Full Name section too
                                            $set('last_name', $user->lastname);
                                            $set('first_name', $user->firstname);
                                            $set('middle_name', $user->middlename);
                                            $set('suffix', $user->suffix);
                                        })
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Full Name')
                                ->columns(4)
                                ->schema([
                                    TextInput::make('last_name')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('first_name')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('middle_name')
                                        ->maxLength(100),
                                    TextInput::make('suffix')
                                        ->label('Suffix (Jr./Sr./III)')
                                        ->maxLength(10),
                                ]),

                            Section::make('Assignment')
                                ->columns(3)
                                ->description('Auto-filled from user account. You may update if needed.')  // ← helpful hint
                                ->schema([
                                    Select::make('municipality_id')
                                        ->label('Municipality')
                                        ->relationship(
                                            name: 'municipality',
                                            titleAttribute: 'citymunDesc'
                                        )
                                        ->getOptionLabelFromRecordUsing(
                                            fn ($record) => "{$record->citymunDesc} ({$record->province->provDesc})"
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(fn ($set) => $set('barangay_id', null)),

                                    Select::make('barangay_id')
                                        ->label('Barangay')
                                        ->options(function (Get $get) {
                                            $municipalityCode = $get('municipality_id');
                                            if (! $municipalityCode) {
                                                return [];
                                            }
                                            return Barangay::where('citymunCode', $municipalityCode)
                                                ->pluck('brgyDesc', 'brgyCode');
                                        })
                                        ->searchable()
                                        ->live()
                                        ->required()
                                        ->disabled(fn (Get $get) => ! $get('municipality_id'))
                                        ->helperText(fn (Get $get) => ! $get('municipality_id') ? 'Select a municipality first.' : null),

                                    Placeholder::make('province')
                                        ->label('Province')
                                        ->content(function (Get $get): string {
                                            $municipalityCode = $get('municipality_id');
                                            if (! $municipalityCode) {
                                                return '—';
                                            }
                                            $municipality = \App\Models\Municipality::with('province')
                                                ->where('citymunCode', $municipalityCode)
                                                ->first();
                                            return $municipality?->province?->provDesc ?? '—';
                                        }),
                                ]),

                            Section::make('Personal Details')
                                ->columns(3)
                                ->schema([
                                    DatePicker::make('date_of_birth')
                                        ->required()
                                        ->native(false)
                                        ->closeOnDateSelection()
                                        ->minDate(now()->subYears(80)->format('Y-m-d'))
                                        ->maxDate(now()->subYears(18)->format('Y-m-d'))
                                        ->displayFormat('M d, Y')
                                        ->weekStartsOnMonday()
                                        ->timezone('Asia/Manila')
                                        ->firstDayOfWeek(1)
                                        ->live(),

                                    TextInput::make('place_of_birth')
                                        ->required()
                                        ->maxLength(150),

                                    Placeholder::make('computed_age')
                                        ->label('Age')
                                        ->content(function (Get $get): string {
                                            $dob = $get('date_of_birth');
                                            if (! $dob) return '—';
                                            return \Carbon\Carbon::parse($dob)->age . ' years old';
                                        }),

                                    Radio::make('sex')
                                        ->options([
                                            'male'   => 'Male',
                                            'female' => 'Female',
                                        ])
                                        ->inline()
                                        ->inlineLabel(false)
                                        ->required(),

                                    Select::make('civil_status')
                                        ->options([
                                            'single'    => 'Single',
                                            'married'   => 'Married',
                                            'widowed'   => 'Widowed',
                                            'separated' => 'Separated',
                                        ])
                                        ->required()
                                        ->native(false),

                                    Select::make('educational_attainment')
                                        ->options(BnsProfile::educationalAttainmentOptions())
                                        ->required()
                                        ->native(false),
                                ]),

                            Section::make('Contact Information')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('contact_number')
                                        ->tel()
                                        ->maxLength(20)
                                        ->rules(['nullable', 'regex:/^(09|\+639)\d{9}$/'])
                                        ->validationMessages(['regex' => 'Enter a valid PH mobile number (09XXXXXXXXX).']),

                                    TextInput::make('email_or_facebook')
                                        ->label('Email / Facebook')
                                        ->maxLength(200),

                                    Textarea::make('home_address')
                                        ->required()
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    //Tab 2: Service Records
                    Tab::make('Service Records')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Section::make('Service Information')
                                ->columns(2)
                                ->schema([
                                    DatePicker::make('date_started')
                                        ->label('Date Started as BNS')
                                        ->native(false)
                                        ->displayFormat('M d, Y')
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state) {
                                                $years = \Carbon\Carbon::parse($state)->diffInYears(now());
                                                $set('years_of_service', $years);
                                            } else {
                                                $set('years_of_service', null);
                                            }
                                        }),

                                    TextInput::make('years_of_service')
                                        ->label('Total Years of Service')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(60)
                                        ->suffix('years')
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('Auto-calculated from Date Started.'),

                                    TextInput::make('bns_id_number')
                                        ->label('BNS ID Number')
                                        ->maxLength(50),

                                    TextInput::make('monthly_honorarium')
                                        ->label('Monthly Honorarium')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->minValue(0),

                                    Select::make('status')
                                        ->options([
                                            'active'   => 'Active',
                                            'inactive' => 'Inactive',
                                            'resigned' => 'Resigned',
                                        ])
                                        ->default('active')
                                        ->required()
                                        ->native(false),
                                ]),
                        ]),

                    Tab::make('Requirements')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            Section::make('Standard Document Requirements')
                                ->description('Upload scanned copies of the required documents for submission to the Provincial Nutrition Office.')
                                ->schema([
                                    self::fileUpload('pds_document', 'Personal Data Sheet (PDS) / BNS Profile Form'),
                                    self::fileUpload('appointment_order', 'Appointment / Designation Order'),
                                    self::fileUpload('oath_of_office', 'Oath of Office'),
                                    self::fileUpload('certificate_of_training', 'Certificate of Training (10-day + 20-day practicum)'),
                                    self::fileUpload('psa_birth_certificate', 'PSA Birth Certificate'),
                                    self::fileUpload('diploma_or_tor', 'Diploma or Transcript of Records'),
                                    self::fileUpload('service_record', 'Service Record / Certification of Service'),
                                ]),
                        ]),

                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ]);
    }

    private static function fileUpload(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->disk('public')
            ->directory('bns-documents')
            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->maxSize(5120)
            ->multiple()
            ->reorderable()
            ->openable()
            ->downloadable()
            ->columnSpanFull();
    }
}
