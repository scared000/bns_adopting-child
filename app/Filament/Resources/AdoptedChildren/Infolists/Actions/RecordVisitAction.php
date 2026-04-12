<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Actions;

use App\Filament\Resources\AdoptedChildren\Infolists\Html\NutritionalStatusPreview;
use App\Filament\Resources\OfficeVisits\Schemas\OfficeVisitsForm;
use App\Models\BaranggayNutritionScholars;
use App\Models\Office;
use App\Models\OfficeChildVisit;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\{DatePicker, FileUpload, Hidden, Placeholder, Repeater, Select, TextInput};
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

final class RecordVisitAction
{
    public static function make(): Actions
    {
        return Actions::make([
            Action::make('record_visit')
                ->label('Record New Visit')
                ->icon('heroicon-o-plus-circle')
                ->color('warning')
                ->modalHeading('Record Visit')
                ->modalDescription(fn ($record) => "Recording a visit for {$record->firstname} {$record->lastname}")
                ->modalWidth('4xl')
                ->modalSubmitActionLabel('Save Visit')
                ->fillForm(self::fillFormData(...))     // PHP 8.1 first-class callable
                ->form(self::buildForm())
                ->action(self::handleAction(...)),      // PHP 8.1 first-class callable
        ]);
    }

    // ─── Form fill ───────────────────────────────────────────────────────────────

    private static function fillFormData(mixed $record): array
    {
        $assignment = $record->officeAssignments()->with('bns', 'office')->latest()->first();

        $ageMonths = null;
        if ($record->birthdate) {
            $diff      = Carbon::parse($record->birthdate)->diff(now());
            $ageMonths = ($diff->y * 12) + $diff->m;
        }

        $addressParts = array_filter([
            $record->purok,
            $record->barangay?->brgyDesc,
            $record->municipality?->citymunDesc,
            $record->municipality?->province?->provDesc,
        ]);

        return [
            'office_assign_id' => $assignment?->id,
            'adopted_id'       => $record->id,
            'bns_id'           => $assignment?->bns_id,
            'office_id'        => $assignment?->office_id,
            'visit_address'    => implode(', ', $addressParts),
            'visit_date'       => now()->toDateString(),
            'sex'              => $record->sex,
            'age_months'       => $ageMonths,
        ];
    }

    // ─── Form schema ─────────────────────────────────────────────────────────────

    private static function buildForm(): array
    {
        return [
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
                            ->mapWithKeys(fn ($b) => [$b->id => "{$b->firstname} {$b->lastname}"])
                    )
                    ->searchable()
                    ->required(),

                Select::make('office_id')
                    ->label('Office')
                    ->options(
                        Office::all()
                            ->mapWithKeys(fn ($o) => [$o->id => "{$o->office} ({$o->short_name})"])
                    )
                    ->searchable()
                    ->required(),
            ]),

            Grid::make(2)->schema([
                TextInput::make('height')
                    ->label('Height')->numeric()->suffix('cm')
                    ->step(0.1)->minValue(0)->live()->required(),

                TextInput::make('weight')
                    ->label('Weight')->numeric()->suffix('kg')
                    ->step(0.1)->minValue(0)->live()->required(),
            ]),

            // Live nutritional status preview — delegates to readonly value object
            Placeholder::make('nutritional_status_preview')
                ->hiddenLabel()
                ->content(fn (Get $get): HtmlString =>
                NutritionalStatusPreview::from($get)->render()
                ),

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
                ->columns(4)
                ->addActionLabel('Add Item')
                ->defaultItems(0)
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
                ]),
        ];
    }

    // ─── Action handler ──────────────────────────────────────────────────────────

    private static function handleAction(array $data, mixed $record): void
    {
        $data  = OfficeVisitsForm::resolveStatus($data);
        $items = $data['visitItems'] ?? [];
        unset($data['visitItems']);

        $visit = OfficeChildVisit::create([
            'office_assign_id'    => $data['office_assign_id'] ?? null,
            'adopted_id'          => $record->id,
            'bns_id'              => $data['bns_id'],
            'office_id'           => $data['office_id'],
            'visit_date'          => $data['visit_date'],
            'visit_address'       => $data['visit_address'],
            'height'              => $data['height'],
            'weight'              => $data['weight'],
            'status'              => $data['status'] ?? null,
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
    }
}
