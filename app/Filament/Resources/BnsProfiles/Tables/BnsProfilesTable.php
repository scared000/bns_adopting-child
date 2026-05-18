<?php

namespace App\Filament\Resources\BnsProfiles\Tables;

use App\Models\BnsProfile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BnsProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(BnsProfile::query()->with(['municipality', 'barangay']))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(['last_name'])
                    ->weight('bold'),

                TextColumn::make('barangay_assigned')
                    ->label('Barangay')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('municipality.citymunDesc')
                ->label('Municipality')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('contact_number')
                    ->label('Contact #')
                    ->toggleable(),

                TextColumn::make('date_started')
                    ->label('Date Started')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('years_of_service')
                    ->label('Years')
                    ->suffix(' yrs')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'resigned' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('trainings_count')
                    ->label('Trainings')
                    ->counts('trainings')
                    ->badge()
                    ->color('info'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_name')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active'   => 'Active',
                        'inactive' => 'Inactive',
                        'resigned' => 'Resigned',
                    ]),
                SelectFilter::make('municipality_id')
                ->label('Municipality')
                    ->options(
                        fn () => \App\Models\Municipality::query()
                            ->orderBy('citymunDesc')
                            ->pluck('citymunDesc', 'citymunCode')
                            ->toArray()
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
