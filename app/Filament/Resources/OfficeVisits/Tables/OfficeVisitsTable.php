<?php

namespace App\Filament\Resources\OfficeVisits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfficeVisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('child.firstname')
                    ->label('CHILD NAME')
                    ->formatStateUsing(fn ($record) => $record->child?->firstname . ' ' . $record->child?->lastname ?? '—')
                    ->searchable(),

                TextColumn::make('bns.firstname')
                    ->label('ASSIGNED BNS')
                    ->formatStateUsing(fn ($record) => $record->bns?->firstname . ' ' . $record->bns?->lastname ?? '—')
                    ->searchable(),

                TextColumn::make('office.office')
                    ->label('ASSIGNED OFFICE')
                    ->wrap()
                    ->badge()
                    ->color('gray')
                    ->getStateUsing(function ($record) {
                        $office = $record->office;
                        if (!$office) return '—';
                        return collect([
                            $office->office,
                            $office->short_name ? "({$office->short_name})" : null,
                        ])->filter()->implode(' ') ?: '—';
                    })
                    ->searchable(),

                TextColumn::make('visit_date')
                    ->label('VISIT DATE')
                    ->date()
                    ->placeholder('—'),

                TextColumn::make('visit_address')
                    ->label('VISIT ADDRESS')
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->color(fn ($state) => match(strtolower($state ?? '')) {
                        'normal'               => 'success',
                        'underweight'          => 'warning',
                        'severely underweight' => 'danger',
                        'overweight'           => 'info',
                        default                => 'gray',
                    })
                    ->placeholder('—'),
            ])
            ->filters([])
            ->recordActionsColumnLabel('ACTION')
            ->recordActions([
                EditAction::make()->icon('heroicon-o-pencil')->badge(),
                DeleteAction::make()->icon('heroicon-o-trash')->badge(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
