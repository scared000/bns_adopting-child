<?php

namespace App\Filament\Resources\OfficeVisits\Tables;

use App\Filament\Pages\ChildVisitDetail;
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
                    ->weight('bold')
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
                    ->wrap()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->placeholder('—'),
            ])
            ->recordUrl(
                fn ($record): string => ChildVisitDetail::getUrl(['child' => $record->child_id])
            )
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
    private static function statusColor(string $state): string
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
