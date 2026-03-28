<?php

namespace App\Filament\Resources\AdoptedChildren\Tables;

use App\Filament\Resources\AdoptedChildren\Schemas\AdoptedChildForm;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdoptedChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
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
                        'name' => $record->firstname . ' ' . $record->lastname,
                        'background' => '6366f1',
                        'color' => 'fff',
                        'size' => '128',
                        'bold' => 'true',
                        'rounded' => 'true',
                    ])
                ),

            TextColumn::make('firstname')
                ->label('NAME')
                ->searchable(['firstname', 'lastname'])
                ->weight('bold')
                ->formatStateUsing(fn ($record) => $record->firstname . ' ' . $record->lastname),

            TextColumn::make('birthdate')
                ->label('AGE BY YEAR & MONTH')
                ->sortable()
                ->formatStateUsing(fn ($record) =>
                $record->birthdate
                    ? (function () use ($record) {
                    $age = \Carbon\Carbon::parse($record->birthdate)->diff(now());
                    $months = ($age->y * 12) + $age->m;
                    return $age->y >= 5
                        ? $age->y . ' yrs old'
                        : $age->y . 'y ' . $age->m . 'm (' . $months . ' months)';
                })()
                    : 'N/A'
                ),

            TextColumn::make('height_cm')
                ->label('HEIGHT CM')
                ->suffix('cm')
                ->numeric(),
            TextColumn::make('weight_kg')
                ->label('WEIGHT KG')
                ->suffix('kg')
                ->numeric(),

            TextColumn::make('nutritional_status')
                ->label('NUTRITIONAL STATUS')
                ->wrap()
                ->extraCellAttributes(['style' => 'min-width: 200px; white-space: normal;'])
                ->getStateUsing(function ($record) {
                    $latestStatus = $record->officeVisits->first()?->status;
                    return $latestStatus ?? $record->nutritional_status ?? '—';
                })
                ->color(fn ($state): string => $state
                    ? AdoptedChildrenTable::statusColor($state)
                    : 'gray'
                )
                ->placeholder('—'),
        ];
    }

    private static function filters(): array
    {
        return [
            SelectFilter::make('nutritional_status')
                ->label('Nutritional Status')
                ->options([
                    'Normal (N)' => 'Normal',
                    'UW — Underweight' => 'Underweight (UW)',
                    'SUW — Severely Underweight' => 'Severely Underweight (SUW)',
                    'ST — Stunted' => 'Stunted (ST)',
                    'SST — Severely Stunted' => 'Severely Stunted (SST)',
                    'MW — Moderately Wasted' => 'Moderately Wasted (MW)',
                    'W — Wasted' => 'Wasted (W)',
                    'At Risk of Overweight' => 'At Risk of Overweight',
                    'OW — Overweight' => 'Overweight (OW)',
                    'OB — Obese' => 'Obese (OB)',
                ])
                ->placeholder('All Statuses')
                ->native(false),
        ];
    }

    private static function recordActions(): array
    {
        return [
            Action::make('visit_log')
                ->label('Visit Log')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->badge()
                ->url(fn ($record) => url('/admin/child-visit-detail?childId=' . $record->id)),

            ViewAction::make()
                ->label('Details')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->badge(),

            DeleteAction::make()
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->badge(),
        ];
    }

    private static function headerActions(): array
    {
        return [
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
            str_contains($state, 'wasted') ||
            str_contains($state, 'obese') => 'danger',
            str_contains($state, 'underweight') ||
            str_contains($state, 'stunted') ||
            str_contains($state, 'overweight') ||
            str_contains($state, 'at risk') => 'warning',
            str_contains($state, 'tall') => 'info',
            str_contains($state, 'incomplete') || str_contains($state, 'n/a') => 'gray',
            default => 'success',
        };
    }
}
