<?php

namespace App\Filament\Resources\OfficeVisits\Schemas;

use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use App\Models\OfficeChildAssign;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OfficeVisitsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Assignment Information')
                    ->description('Link this visit to a child assignment and BNS')
                    ->icon('heroicon-o-user-group')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([

                        Hidden::make('adopted_id')
                            ->required(),
                        Select::make('office_assign_id')
                            ->label('Child Assignment')
                            ->options(function () {
                                return OfficeChildAssign::with(['child', 'bns'])
                                    ->get()
                                    ->mapWithKeys(fn ($assign) => [
                                        $assign->id => ($assign->child?->firstname . ' ' . $assign->child?->lastname)
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) {
                                    $set('visit_address', null);
                                    return;
                                }

                                // Fetch the assignment with the child and all location relationships
                                $assignment = OfficeChildAssign::with([
                                    'child.barangay',
                                    'child.municipality.province'
                                ])->find($state);

                                if ($assignment && $assignment->child) {
                                    $child = $assignment->child;

                                    $addressParts = [
                                        $child->purok ? "Purok {$child->purok}" : null,
                                        $child->barangay?->brgyDesc,
                                        $child->municipality?->citymunDesc,
                                        $child->municipality?->province?->provDesc,
                                    ];

                                    $fullAddress = collect($addressParts)->filter()->implode(', ');

                                    $set('visit_address', $fullAddress);

                                    $set('adopted_id', $child->id);
                                    $set('office_id', $assignment->office_id);
                                    $set('bns_id', $assignment->bns_id);
                                }
                            }),

                        Select::make('bns_id')
                            ->label('Barangay Nutrition Scholar (BNS)')
                            ->options(
                                BaranggayNutritionScholars::all()
                                    ->mapWithKeys(fn ($bns) => [
                                        $bns->id => $bns->firstname . ' ' . $bns->lastname
                                            . ' — ' . ($bns->barangay?->brgyDesc ?? 'No Barangay')
                                    ])
                            )
                            ->searchable()
                            ->required(),

                        Select::make('office_id')
                            ->label('Assigned Office')
                            ->options(
                                Office::all()->mapWithKeys(fn ($office) => [
                                    $office->id => $office->office . ' (' . $office->short_name . ')'
                                ])
                            )
                            ->searchable()
                            ->required(),
                    ]),

                Section::make('Visit Details')
                    ->description('When and where the visit took place')
                    ->icon('heroicon-o-map-pin')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->default(now())
                            ->required(),

                        TextInput::make('visit_address')
                            ->label('Visit Address')
                            ->placeholder('Autofilled from child record...')
                            ->required(),
                    ]),

                Section::make('Measurements')
                    ->description('Child\'s physical measurements during this visit')
                    ->icon('heroicon-o-beaker')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        TextInput::make('height')
                            ->label('Height (cm)')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->suffix('cm')
                            ->placeholder('e.g. 105.5'),

                        TextInput::make('weight')
                            ->label('Weight (kg)')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->suffix('kg')
                            ->placeholder('e.g. 18.2'),

                        Select::make('status')
                            ->label('Nutritional Status')
                            ->options([
                                'normal'               => 'Normal',
                                'underweight'          => 'Underweight',
                                'severely underweight' => 'Severely Underweight',
                                'overweight'           => 'Overweight',
                            ])
                            ->required(),
                    ]),

                Section::make('Documentation')
                    ->description('Upload photos or documents from this visit')
                    ->icon('heroicon-o-camera')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        FileUpload::make('visit_documentation')
                            ->label('Visit Photos / Documents')
                            ->multiple()
                            ->disk('public')
                            ->directory('visit_docs')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->helperText('Upload up to 10 images or PDFs (JPEG, PNG, WEBP, PDF)'),
                    ]),

                Section::make('Items Distributed')
                    ->description('List any items or assistance provided during this visit')
                    ->icon('heroicon-o-gift')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('visitItems')
                        ->relationship()
                            ->schema([
                                TextInput::make('Item_description')
                                    ->label('Item Description')
                                    ->placeholder('e.g. Rice, Canned Goods, Vitamins')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('item_quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('item_amount')
                                    ->label('Amount/Value')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->placeholder('0.00')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Item')
                            ->collapsible()
                            ->defaultItems(0)
                    ]),

            ])
            ->columns(2);
    }
}
