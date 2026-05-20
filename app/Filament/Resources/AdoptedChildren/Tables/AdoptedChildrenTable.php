<?php

namespace App\Filament\Resources\AdoptedChildren\Tables;

use App\Filament\Actions\PrintSelectionAction;
use App\Filament\Resources\AdoptedChildren\Schemas\AdoptedChildForm;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class AdoptedChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->defaultSort('created_at', 'desc')
            ->filters(self::filters())
            ->recordActions(self::recordActions())
            ->recordActionsColumnLabel('ACTION')
            ->recordActionsPosition(RecordActionsPosition::AfterColumns)
            ->headerActions(self::headerActions())
            ->toolbarActions(self::toolbarActions());
    }

    private static function columns(): array
    {
        return [
            ImageColumn::make('profile_path')
                ->label('PROFILE')
                ->circular()
                ->disk('public')
                ->defaultImageUrl(fn ($record) =>
                    'https://ui-avatars.com/api/?' . http_build_query([
                        'name'       => $record->firstname . ' ' . $record->lastname,
                        'background' => '6366f1',
                        'color'      => 'fff',
                        'size'       => '128',
                        'bold'       => 'true',
                        'rounded'    => 'true',
                    ])
                )
                ->grow(false),

            TextColumn::make('firstname')
                ->label('NAME')
                ->searchable(['firstname', 'lastname'])
                ->weight('bold')
                ->formatStateUsing(fn ($record) => $record->firstname . ' ' . $record->lastname)
                ->grow(false)
                ->width('180px'),

            TextColumn::make('batch')
                ->label('BATCH')
                ->badge()
                ->color('primary')
                ->sortable()
                ->grow(false)
                ->width('110px')
                ->placeholder('—'),

            TextColumn::make('birthdate')
                ->label('AGE BY YEAR & MONTH')
                ->sortable()
                ->grow(false)
                ->width('180px')
                ->formatStateUsing(fn ($record) =>
                $record->birthdate
                    ? (function () use ($record) {
                    $age    = \Carbon\Carbon::parse($record->birthdate)->diff(now());
                    $months = ($age->y * 12) + $age->m;
                    return $age->y > 0
                        ? $age->y . 'y ' . $age->m . 'm (' . $months . ' months)'
                        : $months . ' months';
                })()
                    : 'N/A'
                ),

            TextColumn::make('height_cm')
                ->label('HEIGHT CM')
                ->suffix('cm')
                ->numeric()
                ->grow(false)
                ->width('110px')
                ->getStateUsing(function ($record) {
                    $latest_ht = $record->officeVisits->first()?->height;
                    return $latest_ht ?? $record->height_cm ?? '—';
                }),

            TextColumn::make('weight_kg')
                ->label('WEIGHT KG')
                ->suffix('kg')
                ->numeric()
                ->grow(false)
                ->width('110px')
                ->getStateUsing(function ($record) {
                    $latest_wt = $record->officeVisits->first()?->weight;
                    return $latest_wt ?? $record->weight_kg ?? '—';
                }),

            TextColumn::make('nutritional_status')
                ->label('NUTRITIONAL STATUS')
                ->badge()
                ->wrap()
                ->grow(true)
                ->extraCellAttributes([
                    'style' => 'min-width:160px; max-width:260px; white-space:normal; word-break:break-word;',
                ])
                ->getStateUsing(function ($record) {
                    $latestStatus = $record->officeVisits->first()?->status;
                    return $latestStatus ?? $record->nutritional_status ?? '—';
                })
                ->color(fn ($state): string => $state
                    ? AdoptedChildrenTable::statusColor($state)
                    : 'gray'
                )
                ->limit(40)
                ->tooltip(fn ($state): ?string => strlen((string) $state) > 40 ? $state : null)
                ->placeholder('—'),
        ];
    }

    private static function filters(): array
    {
        return [
            SelectFilter::make('batch')
                ->label('Batch')
                ->options(fn (): array =>
                \App\Models\AdoptedChild::query()
                    ->whereNotNull('batch')
                    ->distinct()
                    ->pluck('batch', 'batch')
                    ->sortBy(fn (string $batch): int =>
                    preg_match('/(\d+)/', $batch, $m) ? (int) $m[1] : 0
                    )
                    ->toArray()
                )
                ->placeholder('All Batches')
                ->native(false),

            SelectFilter::make('nutritional_status')
                ->label('Nutritional Status')
                ->options([
                    'Normal (N)'                     => 'Normal',
                    'UW — Underweight'               => 'Underweight (UW)',
                    'SUW — Severely Underweight'     => 'Severely Underweight (SUW)',
                    'ST — Stunted'                   => 'Stunted (ST)',
                    'SST — Severely Stunted'         => 'Severely Stunted (SST)',
                    'MW — Moderately Wasted'         => 'Moderately Wasted (MW)',
                    'W — Wasted'                     => 'Wasted (W)',
                    'At Risk of Overweight'          => 'At Risk of Overweight',
                    'OW — Overweight'                => 'Overweight (OW)',
                    'OB — Obese'                     => 'Obese (OB)',
                ])
                ->placeholder('All Statuses')
                ->native(false),
        ];
    }
    private static function recordActions(): array
    {
        return [
            PrintSelectionAction::make(),
            ViewAction::make()
                ->label('Details')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->badge()
                ->iconButton(),

            DeleteAction::make()
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->badge()
                ->iconButton(),
        ];
    }

    private static function headerActions(): array
    {
        return [
            Action::make('print_batch')
                ->label('Print by Batch')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->outlined()
                ->form([
                    Select::make('batch')
                        ->label('Select Batch to Print')
                        ->options(fn (): array =>
                        \App\Models\AdoptedChild::query()
                            ->whereNotNull('batch')
                            ->distinct()
                            ->pluck('batch', 'batch')
                            ->sortBy(fn (string $batch): int =>
                            preg_match('/(\d+)/', $batch, $m) ? (int) $m[1] : 0
                            )
                            ->toArray()
                        )
                        ->searchable()
                        ->required(),
                ])
                // 2. Handle the Submission
                ->action(function (array $data, Component $livewire) {
                    $url = route('print.child.batch.monthly-monitoring', [
                        'batch' => $data['batch']
                    ]);
                    $livewire->js("window.open('{$url}', '_blank');");
                })
                ->modalHeading('Monthly Monitoring Report')
                ->modalDescription('Select a specific batch to generate the print layout.')
                ->modalSubmitActionLabel('Generate Report')
                ->modalWidth('md'),

            CreateAction::make()
                ->icon('heroicon-s-plus')
                ->label('New Child')
                ->createAnother(false)
                ->modalSubmitActionLabel('Save')
                ->modalCancelActionLabel('Discard')
                ->modalWidth('6xl')
                ->steps(AdoptedChildForm::wizardSteps())
                ->after(fn ($record, array $data) => AdoptedChildForm::afterCreate($record, $data)),
        ];
    }

    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function statusColor(string $state): string
    {
        $state = strtolower($state);

        return match (true) {
            str_contains($state, 'severely') ||
            str_contains($state, 'wasted')   ||
            str_contains($state, 'obese')    => 'danger',
            str_contains($state, 'underweight') ||
            str_contains($state, 'stunted')     ||
            str_contains($state, 'overweight')  ||
            str_contains($state, 'at risk')     => 'warning',
            str_contains($state, 'tall')         => 'info',
            str_contains($state, 'incomplete')  ||
            str_contains($state, 'n/a')          => 'gray',
            default                              => 'success',
        };
    }
}
