<?php

namespace App\Filament\Resources\OfficeDistributions\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OfficeDistributionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('child.firstname')
                    ->label('CHILD')
                    ->formatStateUsing(fn ($record) =>
                        $record->child?->firstname . ' ' . $record->child?->lastname
                    )
                    ->searchable(['child.firstname', 'child.lastname']),

                TextColumn::make('visit_date')
                    ->label('DISTRIBUTION DATE')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('total_quantity')
                    ->label('QUANTITY')
                    ->getStateUsing(fn ($record) =>
                    $record->visitItems->sum('item_quantity')
                    )
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('TOTAL AMOUNT')
                    ->getStateUsing(fn ($record) =>
                        '₱' . number_format(
                            $record->visitItems()->sum('item_amount'), 2
                        )
                    ),
            ])
            ->defaultSort('visit_date', 'desc')
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
                    ]);

    }
}
