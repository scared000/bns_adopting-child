<?php

namespace App\Filament\Resources\AdoptedChildren\Tables;

use App\Filament\Resources\AdoptedChildren\Schemas\AdoptedChildForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
            ->headerActions(self::headerActions())
            ->toolbarActions(self::toolbarActions());
    }

    private static function columns(): array
    {
        return [
            ImageColumn::make('profile_path')
                ->label('Profile')
                ->circular()
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
                ->label('Name')
                ->searchable(['firstname', 'lastname'])
                ->weight('bold')
                ->formatStateUsing(fn ($record) => $record->firstname . ' ' . $record->lastname),

            TextColumn::make('birthdate')
                ->label('Age by year & month')
                ->sortable()
                ->formatStateUsing(fn ($record) =>
                $record->birthdate
                    ? (function () use ($record) {
                    $age    = \Carbon\Carbon::parse($record->birthdate)->diff(now());
                    $months = ($age->y * 12) + $age->m;
                    return $age->y >= 5
                        ? $age->y . ' yrs old'
                        : $age->y . 'y ' . $age->m . 'm (' . $months . ' months)';
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
                ->formatStateUsing(fn ($record) => $record->nutritional_status ?? 'Incomplete Data')
                ->color(fn (string $state): string => self::statusColor($state)),
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
            ViewAction::make()->color('info')->iconButton(),

            EditAction::make()
                ->iconButton()
                ->after(fn ($record) => AdoptedChildForm::afterEdit($record)),

            DeleteAction::make()->iconButton(),
        ];
    }

    private static function headerActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-s-plus')
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

    private static function statusColor(string $state): string
    {
        return match (true) {
            str_contains($state, 'SUW') => 'danger',
            str_contains($state, 'SST') => 'danger',
            str_contains($state, 'Wasted') => 'danger',
            str_contains($state, 'At Risk') => 'danger',
            str_contains($state, 'OB') => 'danger',
            str_contains($state, 'OW') => 'info',
            str_contains($state, 'UW') => 'warning',
            str_contains($state, 'ST') => 'warning',
            str_contains($state, 'MW') => 'warning',
            str_contains($state, 'Incomplete') => 'gray',
            str_contains($state, 'Invalid') => 'danger',
            default => 'success',
        };
    }
}
