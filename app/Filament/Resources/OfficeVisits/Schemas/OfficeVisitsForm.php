<?php

namespace App\Filament\Resources\OfficeVisits\Schemas;

use App\Helpers\NutritionalStatus;
use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use App\Models\OfficeChildAssign;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                        // Hidden fields — auto-populated when an assignment is selected
                        Hidden::make('adopted_id'),
                        Hidden::make('sex'),
                        Hidden::make('age_months'),

                        Select::make('office_assign_id')
                            ->label('Child Assignment')
                            ->options(function () {
                                return OfficeChildAssign::with(['child', 'bns'])
                                    ->get()
                                    ->mapWithKeys(fn ($assign) => [
                                        $assign->id => ($assign->child?->firstname . ' ' . $assign->child?->lastname),
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) {
                                    $set('visit_address', null);
                                    $set('adopted_id',    null);
                                    $set('sex',           null);
                                    $set('age_months',    null);
                                    return;
                                }

                                $assignment = OfficeChildAssign::with([
                                    'child.barangay',
                                    'child.municipality.province',
                                ])->find($state);

                                if ($assignment && $assignment->child) {
                                    $child = $assignment->child;

                                    // Auto-fill address
                                    $addressParts = [
                                        $child->purok ? "Purok {$child->purok}" : null,
                                        $child->barangay?->brgyDesc,
                                        $child->municipality?->citymunDesc,
                                        $child->municipality?->province?->provDesc,
                                    ];
                                    $set('visit_address', collect($addressParts)->filter()->implode(', '));

                                    // Auto-fill related IDs
                                    $set('adopted_id', $child->id);
                                    $set('office_id',  $assignment->office_id);
                                    $set('bns_id',     $assignment->bns_id);
                                    $set('sex', $child->sex);

                                    if ($child->birthdate) {
                                        $diff        = Carbon::parse($child->birthdate)->diff(now());
                                        $set('age_months', ($diff->y * 12) + $diff->m);
                                    }
                                }
                            }),

                        Select::make('bns_id')
                            ->label('Barangay Nutrition Scholar (BNS)')
                            ->options(
                                BaranggayNutritionScholars::all()
                                    ->mapWithKeys(fn ($bns) => [
                                        $bns->id => $bns->firstname . ' ' . $bns->lastname
                                            . ' — ' . ($bns->barangay?->brgyDesc ?? 'No Barangay'),
                                    ])
                            )
                            ->searchable()
                            ->required(),

                        Select::make('office_id')
                            ->label('Assigned Office')
                            ->options(
                                Office::all()->mapWithKeys(fn ($office) => [
                                    $office->id => $office->office . ' (' . $office->short_name . ')',
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
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $assignId = $get('office_assign_id');
                                if (!$assignId || !$state) return;

                                $child = OfficeChildAssign::with('child')->find($assignId)?->child;

                                if ($child?->birthdate) {
                                    $diff = Carbon::parse($child->birthdate)->diff(Carbon::parse($state));
                                    $set('age_months', ($diff->y * 12) + $diff->m);
                                }
                            }),

                        TextInput::make('visit_address')
                            ->label('Visit Address')
                            ->placeholder('Autofilled from child record...')
                            ->required(),
                    ]),

                Section::make('Measurements & Nutritional Status')
                    ->description("Child's measurements — nutritional status is computed automatically")
                    ->icon('heroicon-o-beaker')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([

                        Grid::make(2)->schema([
                            TextInput::make('height')
                                ->label('Height')
                                ->numeric()
                                ->step(0.1)
                                ->minValue(0)
                                ->suffix('cm')
                                ->placeholder('e.g. 105.5')
                                ->live()
                                ->required(),

                            TextInput::make('weight')
                                ->label('Weight')
                                ->numeric()
                                ->step(0.1)
                                ->minValue(0)
                                ->suffix('kg')
                                ->placeholder('e.g. 18.2')
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
                                    || !in_array($sex, ['male', 'female'])) {
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
                                                    Select a child assignment and enter height &amp; weight to compute</p>
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

                        Hidden::make('status'),
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
                                    ->label('Amount / Value')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->placeholder('0.00')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Item')
                            ->collapsible()
                            ->defaultItems(0),
                    ]),

            ])
            ->columns(2);
    }

    public static function resolveStatus(array $data): array
    {
        $months = (int)  ($data['age_months'] ?? 0);
        $weight = (float)($data['weight'] ?? 0);
        $height = (float)($data['height'] ?? 0);
        $sex = $data['sex'] ?? '';

        if ($months > 0 && $weight > 0 && $height > 0 && in_array($sex, ['male', 'female'])) {
            $data['status'] = NutritionalStatus::classify($months, $weight, $height, $sex);
        }

        // Remove transient UI-only fields — not columns in office_child_visits
        unset($data['age_months'], $data['sex']);

        return $data;
    }
}
