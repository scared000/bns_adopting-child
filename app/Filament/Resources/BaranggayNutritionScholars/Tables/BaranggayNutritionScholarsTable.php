<?php

namespace App\Filament\Resources\BaranggayNutritionScholars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BaranggayNutritionScholarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_path')
                    ->label('PROFILE')
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
                    ),
                TextColumn::make('firstname')
                    ->label('BNS NAME')
                    ->searchable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) => $record->firstname . ' ' . $record->lastname),

                TextColumn::make('barangay_name')
                    ->label('BARANGAY'),

                TextColumn::make('child_assigned')
                    ->label('CHILD ASSIGNED')
                    ->default('—'),

                TextColumn::make('last_visit')
                    ->label('LAST VISIT')
                    ->default('—'),

                TextColumn::make('status')
                    ->label('STATUS')
                    ->default('—'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->badge()
                    ->label('Edit')
                    ->color('info'),
                DeleteAction::make()
                    ->badge()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
